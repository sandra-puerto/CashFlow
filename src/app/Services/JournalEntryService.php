<?php

namespace App\Services;

use App\Exceptions\JournalEntryException;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly class JournalEntryService
{
    /**
     * Crea un asiento contable completo con sus líneas, aplicando la partida doble
     * y actualizando de forma segura los saldos de las cuentas contables.
     *
     * @param array{
     *    datetime: string,
     *    concept: string,
     *    type?: string,
     *    lines: array<int, array{
     *        account_id: int,
     *        description?: string|null,
     *        debit: float|int,
     *        credit: float|int
     *    }>
     * } $data
     * @return JournalEntry
     * @throws JournalEntryException
     */
    public function createJournalEntry(array $data): JournalEntry
    {
        // Normaliza las líneas: redondea los montos a 2 decimales y reindexa,
        // de modo que la validación y la persistencia usen exactamente los mismos valores.
        // Esto evita que un asiento pase la validación con valores de 3+ decimales
        // que luego se redondean y dejan el asiento desbalanceado.
        $lines = collect($data['lines'] ?? [])
            ->map(fn (array $line) => [
                'account_id'  => $line['account_id'] ?? null,
                'description' => $line['description'] ?? null,
                'debit'       => round((float) ($line['debit'] ?? 0), 2),
                'credit'      => round((float) ($line['credit'] ?? 0), 2),
            ])
            ->values();

        // Validaciones previas de integridad y partida doble
        $this->validateLinesNotEmpty($lines);
        $this->validateBalance($lines);
        $this->validateLines($lines);

        return DB::transaction(function () use ($data, $lines) {
            // 1. Crear la cabecera del asiento contable
            $entry = JournalEntry::create([
                'datetime' => $data['datetime'],
                'concept'  => $data['concept'],
                'type'     => $data['type'] ?? 'diario',
                'status'   => 'contabilizado',
            ]);

            // 2. Procesar las líneas de manera transaccional e imperativa
            foreach ($lines as $index => $lineData) {
                // lockForUpdate() previene condiciones de carrera concurrentes sobre el saldo
                // de la cuenta en motores que lo soportan (MySQL/PostgreSQL). En SQLite el
                // lock es ignorado, pero la transacción aún garantiza atomicidad.
                $account = Account::lockForUpdate()->find($lineData['account_id']);

                if (! $account) {
                    throw new JournalEntryException('La cuenta contable especificada no existe.');
                }

                // Regla de negocio: no permitir contabilizar en cuentas inactivas.
                if (! $account->is_active) {
                    throw new JournalEntryException(
                        "La cuenta '{$account->code}' no se encuentra activa y no puede recibir movimientos."
                    );
                }

                $previousBalance = (float) $account->current_balance;
                $newBalance      = (float) $account->applyMovement($lineData['debit'], $lineData['credit']);

                // line_number es obligatorio: la tabla tiene un índice único
                // (journal_entry_id, line_number), por lo que cada línea debe tener
                // un consecutivo distinto dentro del mismo asiento.
                $entry->lines()->create([
                    'line_number'      => $index + 1,
                    'account_id'       => $account->id,
                    'description'      => $lineData['description'],
                    'debit'            => $lineData['debit'],
                    'credit'           => $lineData['credit'],
                    'previous_balance' => $previousBalance,
                    'new_balance'      => $newBalance,
                ]);
            }

            return $entry->load('lines.account');
        });
    }

    /**
     * Valida que el comprobante contenga al menos una línea de movimiento.
     *
     * @param Collection<int, array> $lines
     * @return void
     * @throws JournalEntryException
     */
    private function validateLinesNotEmpty(Collection $lines): void
    {
        if ($lines->isEmpty()) {
            throw new JournalEntryException('El asiento contable debe contener al menos una línea de movimiento.');
        }
    }

    /**
     * Valida que el total del débito equivalga exactamente al total del crédito (Partida Doble).
     *
     * @param Collection<int, array> $lines
     * @return void
     * @throws JournalEntryException
     */
    private function validateBalance(Collection $lines): void
    {
        $totalDebit  = round($lines->sum(fn (array $line) => (float) ($line['debit'] ?? 0)), 2);
        $totalCredit = round($lines->sum(fn (array $line) => (float) ($line['credit'] ?? 0)), 2);

        // Tolerancia estricta ante errores de representación de coma flotante
        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new JournalEntryException(
                "El total del débito ({$totalDebit}) no coincide con el total del crédito ({$totalCredit}). El asiento no está balanceado."
            );
        }
    }

    /**
     * Valida las reglas individuales para cada línea de movimiento contable.
     *
     * @param Collection<int, array> $lines
     * @return void
     * @throws JournalEntryException
     */
    private function validateLines(Collection $lines): void
    {
        foreach ($lines as $index => $line) {
            $debit   = (float) ($line['debit'] ?? 0);
            $credit  = (float) ($line['credit'] ?? 0);
            $lineNum = $index + 1;

            if ($debit < 0 || $credit < 0) {
                throw new JournalEntryException(
                    "Línea {$lineNum}: los valores monetarios no pueden ser negativos."
                );
            }

            if ($debit > 0 && $credit > 0) {
                throw new JournalEntryException(
                    "Línea {$lineNum}: una línea contable no puede poseer débito y crédito simultáneamente."
                );
            }

            if ($debit === 0.0 && $credit === 0.0) {
                throw new JournalEntryException(
                    "Línea {$lineNum}: debe especificar un valor de débito o crédito mayor a cero."
                );
            }
        }
    }
}