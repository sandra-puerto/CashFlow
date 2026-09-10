<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Crea un nuevo usuario de forma interactiva o con opciones.
*/
class CreateUsers extends Command
{
    /**
     * Firma del comando.
     *
     * @var string
    */
    protected $signature = 'make:user
                            {--name= : Nombre del usuario}
                            {--email= : Correo electrónico}
                            {--password= : Contraseña (se pedirá si no se proporciona)}
                            {--document-type= : Tipo de documento (código o nombre, ej. CC, NIT)}
                            {--document-number= : Número de identificación}
                            {--force : Saltar confirmación}';

    /**
     * Descripción del comando.
     *
     * @var string
    */
    protected $description = 'Crea un nuevo usuario de forma interactiva o con opciones.';

    /**
     * Reglas de validación para los campos.
     *
     * @var array<string, string>
    */
    private array $rules = [
        'name'             => 'required|string|max:255|unique:users,name',
        'email'            => 'required|string|email|max:255|unique:users,email',
        'password'         => 'required|string|min:8',
        'document_number'  => 'required|string|max:20|unique:users,document_number',
        'document_type_id' => 'required|exists:document_types,id',
    ];

    /**
     * Mensajes personalizados para las reglas de validación.
     *
     * @var array<string, string>
    */
    private array $messages = [
        'name.required'             => 'El nombre es obligatorio.',
        'name.string'               => 'El nombre debe ser texto.',
        'name.max'                  => 'El nombre no debe exceder 255 caracteres.',
        'name.unique'               => 'Ese nombre ya está en uso.',
        'email.required'            => 'El correo es obligatorio.',
        'email.string'              => 'El correo debe ser texto.',
        'email.email'               => 'El correo debe tener un formato válido.',
        'email.max'                 => 'El correo no debe exceder 255 caracteres.',
        'email.unique'              => 'Ese correo ya está registrado.',
        'password.required'         => 'La contraseña es obligatoria.',
        'password.string'           => 'La contraseña debe ser texto.',
        'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
        'document_number.required'  => 'El número de identificación es obligatorio.',
        'document_number.string'    => 'El número de identificación debe ser texto.',
        'document_number.max'       => 'El número de identificación no debe exceder 20 caracteres.',
        'document_number.unique'    => 'Ese número de identificación ya está registrado.',
        'document_type_id.required' => 'El tipo de documento es obligatorio.',
        'document_type_id.exists'   => 'El tipo de documento seleccionado no existe.',
    ];

    /**
     * Ejecuta el comando.
     *
     * @return int Código de salida (0 éxito, 1 error)
    */
    public function handle(): int
    {
        $this->components->info('🔄 Creación de nuevo usuario');

        // Resolver tipo de documento (vía opción de consola o interactiva)
        $documentTypeInput = $this->option('document-type');
        $documentType = $documentTypeInput 
            ? $this->resolveDocumentType($documentTypeInput) 
            : $this->askDocumentType();

        // Obtener o preguntar el resto de los campos
        $documentNumber = $this->option('document-number') ?? $this->askValidated('Número de identificación', 'document_number');
        $name           = $this->option('name') ?? $this->askValidated('Nombre', 'name');
        $email          = $this->option('email') ?? $this->askValidated('Correo electrónico', 'email');
        $password       = $this->option('password') ?? $this->askValidated('Contraseña', 'password', true);

        $this->showSummary($name, $email, $password, $documentType, $documentNumber);

        if (! $this->option('force') && ! $this->confirm('¿Crear este usuario?', true)) {
            $this->components->warn('Operación cancelada.');
            return 0;
        }

        try {
            $user = User::create([
                'name'             => $name,
                'email'            => $email,
                'password'         => Hash::make($password),
                'document_type_id' => $documentType->id,
                'document_number'  => $documentNumber,
            ]);

            $this->components->info('✅ Usuario creado con éxito');
            $this->showUser($user);

            return 0;
        } catch (UniqueConstraintViolationException $e) {
            $this->components->error('El nombre, correo o número de identificación ya existe. Intenta de nuevo.');
            return 1;
        } catch (\Throwable $e) {
            $this->components->error('Error inesperado: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Busca y resuelve un tipo de documento a partir de un string (código o nombre).
     *
     * @param string $input
     * @return DocumentType
    */
    private function resolveDocumentType(string $input): DocumentType
    {
        $documentType = DocumentType::where('abbreviation', $input)
            ->orWhere('name', 'LIKE', "%{$input}%")
            ->first();

        if (! $documentType) {
            $this->components->error("No se encontró un tipo de documento con el valor '{$input}'.");
            exit(1);
        }

        return $documentType;
    }

    /**
     * Pregunta interactivamente por el tipo de documento (código o nombre).
     *
     * @return DocumentType
    */
    private function askDocumentType(): DocumentType
    {
        while (true) {
            $input = $this->ask('Tipo de documento (ej. CC, NIT, CE). Introduce el código o nombre');

            $documentType = DocumentType::where('abbreviation', $input)
                ->orWhere('name', 'LIKE', "%{$input}%")
                ->first();

            if ($documentType) {
                return $documentType;
            }

            $this->components->error('No se encontró un tipo de documento con ese código o nombre. Intenta de nuevo.');
        }
    }

    /**
     * Solicita un valor al usuario y lo valida en tiempo real.
     *
     * @param string $question Texto de la pregunta
     * @param string $field    Nombre del campo a validar (debe existir en $rules)
     * @param bool   $secret   Si es verdadero, oculta la entrada (para contraseñas)
     * @return string Valor validado
    */
    private function askValidated(string $question, string $field, bool $secret = false): string
    {
        while (true) {
            $value = $secret ? $this->secret($question) : $this->ask($question);

            $validator = Validator::make(
                [$field => $value],
                [$field => $this->rules[$field]],
                $this->messages
            );

            if ($validator->fails()) {
                $this->components->error($validator->errors()->first($field));
                continue;
            }

            return $value;
        }
    }

    /**
     * Muestra un resumen de los datos ingresados.
     *
     * @param string       $name
     * @param string       $email
     * @param string       $password
     * @param DocumentType $documentType
     * @param string       $documentNumber
    */
    private function showSummary(string $name, string $email, string $password, DocumentType $documentType, string $documentNumber): void
    {
        $this->components->twoColumnDetail('Nombre', $name);
        $this->components->twoColumnDetail('Correo', $email);
        $this->components->twoColumnDetail('Contraseña', str_repeat('•', strlen($password)));
        $this->components->twoColumnDetail('Tipo Documento', $documentType->name . ' (' . $documentType->abbreviation . ')');
        $this->components->twoColumnDetail('Número Documento', $documentNumber);
    }

    /**
     * Muestra la información del usuario creado.
     *
     * @param User $user
    */
    private function showUser(User $user): void
    {
        $this->components->twoColumnDetail('ID', (string) $user->id);
        $this->components->twoColumnDetail('Nombre', $user->name);
        $this->components->twoColumnDetail('Correo', $user->email);
        $this->components->twoColumnDetail('Documento', $user->document_number . ' (' . optional($user->documentType)->abbreviation . ')');
    }
}