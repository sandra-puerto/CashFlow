<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Seeder para importar/actualizar el Plan Único de Cuentas (PUC) colombiano.
 *
 * - Descarga automática del CSV si no existe localmente.
 * - Carga previa en memoria para ejecutar en O(1) y reducir consultas a la BD.
 * - Usa updateOrCreate para evitar duplicados (actualiza si el código ya existe).
 * - Mantiene la integridad jerárquica de padres e hijos.
*/
class PUCSeeder extends Seeder
{
    /**
     * URL del archivo CSV con el catálogo PUC.
    */
    private const CSV_URL = 'https://raw.githubusercontent.com/heilernova/colombia-data/main/puc/puc.csv';

    /**
     * Ruta local donde se almacena el archivo CSV.
    */
    private const CSV_PATH = 'seeders/data/puc.csv';

    /**
     * Mapa de naturaleza contable según el primer dígito del código PUC (1 al 9 completo).
     *
     * @var array<string, string>
    */
    private const NATURE_MAP = [
        '1' => 'debit',   // Activo
        '2' => 'credit',  // Pasivo
        '3' => 'credit',  // Patrimonio
        '4' => 'credit',  // Ingresos
        '5' => 'debit',   // Gastos
        '6' => 'debit',   // Costos de venta
        '7' => 'debit',   // Costos de producción / operación
        '8' => 'debit',   // Cuentas de orden deudoras
        '9' => 'credit',  // Cuentas de orden acreedoras
    ];

    /**
     * Tamaño del lote para mostrar progreso en consola.
    */
    private const PROGRESS_STEP = 200;

    /**
     * Run the database seeds.
    */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando importación/actualización del PUC colombiano...');

        // 1. Obtener el archivo CSV (descarga automática si no existe)
        $csvPath = $this->getCsvFile();

        // 2. Parsear el CSV
        $data = $this->parseCsv($csvPath);
        if (empty($data)) {
            $this->command->error('❌ No se encontraron datos válidos en el archivo CSV.');
            return;
        }

        $this->command->info("📊 Se encontraron " . count($data) . " cuentas en el archivo.");

        // 3. Importar/actualizar las cuentas
        $result = $this->importOrUpdateAccounts($data);

        // 4. Mostrar resumen
        $this->showSummary($result);

        // 5. Verificar integridad
        $this->verifyIntegrity();
    }

    /**
     * Obtiene la ruta del archivo CSV, descargándolo si no existe localmente.
     *
     * @return string Ruta completa al archivo CSV.
    */
    private function getCsvFile(): string
    {
        $fullPath = database_path(self::CSV_PATH);

        if (file_exists($fullPath)) {
            $this->command->info("✅ Usando archivo local: " . self::CSV_PATH);
            return $fullPath;
        }

        // Descargar el archivo
        $this->command->info("📥 Descargando PUC desde GitHub...");
        $response = Http::withoutVerifying()->get(self::CSV_URL);

        if (!$response->successful()) {
            $this->command->error("❌ No se pudo descargar el archivo PUC.");
            $this->command->error("   URL: " . self::CSV_URL);
            $this->command->error("   Código de respuesta: " . $response->status());
            throw new \RuntimeException('No se pudo descargar el archivo PUC.');
        }

        // Crear el directorio si no existe
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Guardar el archivo
        file_put_contents($fullPath, $response->body());
        $this->command->info("✅ Archivo guardado en: " . self::CSV_PATH);

        return $fullPath;
    }

    /**
     * Parsea el archivo CSV y devuelve un array con los datos.
     *
     * @param string $csvPath Ruta completa al archivo CSV.
     * @return array<int, array{code: string, name: string}> Datos parseados.
    */
    private function parseCsv(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->command->error("❌ No se pudo abrir el archivo CSV: $csvPath");
            return [];
        }

        $data = [];
        $lineNumber = 0;

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $lineNumber++;

            if (count($row) < 2 || empty(trim($row[0]))) {
                continue;
            }

            $code = trim($row[0]);
            $name = trim($row[1]);

            if (!ctype_digit($code) || strlen($code) > 10) {
                Log::warning("PUC Seeder: Línea $lineNumber ignorada (código inválido: '$code')");
                continue;
            }

            $data[] = [
                'code' => $code,
                'name' => $name,
            ];
        }

        fclose($handle);
        return $data;
    }

    /**
     * Importa o actualiza las cuentas en la base de datos manteniendo la jerarquía.
     *
     * @param array<int, array{code: string, name: string}> $data Datos a procesar.
     * @return array{processed: int, created: int, updated: int, errors: int}
    */
    private function importOrUpdateAccounts(array $data): array
    {
        $this->command->info("🔄 Procesando cuentas (insertar/actualizar)...");

        // Ordenar por longitud de código para garantizar que los padres se creen antes que los hijos
        usort($data, function ($a, $b) {
            return strlen($a['code']) - strlen($b['code']);
        });

        // Carga previa en memoria de las cuentas existentes para evitar miles de consultas SQL
        $accountMap = Account::pluck('id', 'code')->toArray();

        $processed = 0;
        $created = 0;
        $updated = 0;
        $errors = 0;

        DB::transaction(function () use ($data, &$accountMap, &$processed, &$created, &$updated, &$errors) {
            foreach ($data as $item) {
                try {
                    $code = $item['code'];
                    $name = $item['name'];
                    
                    // Cálculo del nivel jerárquico contable colombiano (1: Clase, 2: Grupo, 4: Cuenta, 6: Subcuenta, 8+: Auxiliar)
                    $level = $this->calculatePucLevel($code);

                    $firstDigit = substr($code, 0, 1);
                    $nature = self::NATURE_MAP[$firstDigit] ?? 'debit';

                    // Búsqueda del ID del padre en el mapa en memoria
                    $parentId = null;
                    $codeLength = strlen($code);

                    if ($codeLength > 1) {
                        for ($i = $codeLength - 1; $i >= 1; $i--) {
                            $parentCode = substr($code, 0, $i);
                            if (isset($accountMap[$parentCode])) {
                                $parentId = $accountMap[$parentCode];
                                break;
                            }
                        }
                    }

                    // Determinar si existe en el mapa de memoria
                    $exists = isset($accountMap[$code]);

                    $account = Account::updateOrCreate(
                        ['code' => $code],
                        [
                            'name'            => $name,
                            'nature'          => $nature,
                            'level'           => $level,
                            'parent_id'       => $parentId,
                            'is_active'       => true,
                            // Las cuentas de nivel 1 (Clase) son la base
                            // inalterable del PUC y quedan marcadas como primarias.
                            'is_primary'      => $level === 1,
                            'current_balance' => 0,
                        ]
                    );

                    // Actualizar mapa en memoria con el ID asignado
                    $accountMap[$code] = $account->id;

                    if ($exists) {
                        $updated++;
                    } else {
                        $created++;
                    }

                    $processed++;

                    if ($processed % self::PROGRESS_STEP === 0) {
                        $this->command->info("   Procesadas $processed cuentas...");
                    }

                } catch (\Exception $e) {
                    $errors++;
                    $this->command->error("   Error en cuenta {$item['code']}: " . $e->getMessage());
                    Log::error("PUC Seeder Error en {$item['code']}: " . $e->getMessage());
                }
            }
        });

        return [
            'processed' => $processed,
            'created'   => $created,
            'updated'   => $updated,
            'errors'    => $errors,
        ];
    }

    /**
     * Calcula el nivel jerárquico oficial del PUC colombiano según la longitud del código.
     * 1 dígito  => Nivel 1 (Clase)
     * 2 dígitos => Nivel 2 (Grupo)
     * 4 dígitos => Nivel 3 (Cuenta)
     * 6 dígitos => Nivel 4 (Subcuenta)
     * 8+ dígitos=> Nivel 5+ (Auxiliares)
    */
    private function calculatePucLevel(string $code): int
    {
        $len = strlen($code);

        return match ($len) {
            1       => 1, // Clase
            2       => 2, // Grupo
            3, 4    => 3, // Cuenta
            5, 6    => 4, // Subcuenta
            7, 8    => 5, // Auxiliar Nivel 1
            default => 5 + (int) ceil(($len - 8) / 2),
        };
    }

    /**
     * Muestra un resumen detallado de la operación.
     *
     * @param array{processed: int, created: int, updated: int, errors: int} $result
    */
    private function showSummary(array $result): void
    {
        $this->command->newLine();
        $this->command->info("📋 RESUMEN DE OPERACIÓN");
        $this->command->line("─────────────────────────────────");
        $this->command->line("📊 Cuentas procesadas: {$result['processed']}");
        $this->command->line("✅ Nuevas creadas: {$result['created']}");
        $this->command->line("🔄 Actualizadas: {$result['updated']}");
        $this->command->line("❌ Errores: {$result['errors']}");
        $this->command->line("📊 Total en BD: " . Account::count());
        $this->command->line("─────────────────────────────────");

        if ($result['errors'] === 0) {
            $this->command->info("✅ ¡PUC importado/actualizado exitosamente!");
        } else {
            $this->command->warn("⚠️  La operación completó con errores. Revisa el log para más detalles.");
        }
    }

    /**
     * Verifica la integridad de los datos.
    */
    private function verifyIntegrity(): void
    {
        $this->command->newLine();
        $this->command->info("🔍 Verificando integridad de los datos...");

        // Búsqueda eficiente de cuentas con parent_id apuntando a registros inexistentes
        $orphans = Account::whereNotNull('parent_id')
            ->whereDoesntHave('parent')
            ->get();

        if ($orphans->isEmpty()) {
            $this->command->info("✅ No hay cuentas huérfanas.");
        } else {
            $this->command->warn("⚠️  Se encontraron " . $orphans->count() . " cuentas huérfanas.");
            foreach ($orphans as $orphan) {
                $this->command->line("   - {$orphan->code} ({$orphan->name}) -> parent_id: {$orphan->parent_id}");
            }
        }

        $levels = Account::selectRaw('level, COUNT(*) as total')
            ->groupBy('level')
            ->orderBy('level')
            ->pluck('total', 'level');

        $this->command->info("📊 Distribución por nivel jerárquico:");
        foreach ($levels as $level => $total) {
            $this->command->line("   Nivel $level: $total cuentas");
        }
    }
}