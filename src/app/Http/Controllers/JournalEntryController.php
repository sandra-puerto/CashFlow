<?php

namespace App\Http\Controllers;

use App\Exceptions\JournalEntryException;
use App\Helpers\ResponseHelper;
use App\Http\Requests\JournalEntryRequest;
use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class JournalEntryController extends Controller
{
    /**
     * Crea una nueva instancia del controlador inyectando el servicio de asientos contables.
     *
     * @param JournalEntryService $journalEntryService
     */
    public function __construct(
        private readonly JournalEntryService $journalEntryService
    ) {}

    /**
     * Muestra la lista paginada de asientos contables con filtros opcionales por fecha.
     *
     * @param JournalEntryRequest $request
     * @return JsonResponse
     */
    public function index(JournalEntryRequest $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $entries = JournalEntry::query()
            ->with('lines.account')
            // Filtrado corregido por la columna real 'datetime' de la migración
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('datetime', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('datetime', '<=', $request->input('date_to')))
            ->latest('datetime')
            ->latest('id')
            ->paginate($perPage);

        return ResponseHelper::raw(['entries' => $entries]);
    }

    /**
     * Almacena un nuevo asiento contable aplicando las reglas del PUC y la partida doble.
     *
     * @param JournalEntryRequest $request
     * @return JsonResponse
     */
    public function store(JournalEntryRequest $request): JsonResponse
    {
        try {
            $entry = $this->journalEntryService->createJournalEntry(
                $request->validated()
            );

            return ResponseHelper::created(['entry' => $entry], 'Asiento contabilizado exitosamente.');
        } catch (JournalEntryException $e) {
            return ResponseHelper::badRequest($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error en JournalEntryController@store: ' . $e->getMessage());
            return ResponseHelper::criticalError('Error al contabilizar el asiento.');
        }
    }

    /**
     * Muestra los detalles de un asiento contable específico y sus líneas asociadas.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $entry = JournalEntry::with('lines.account')->find($id);

        if (! $entry) {
            return ResponseHelper::notFound('Asiento no encontrado.');
        }

        return ResponseHelper::success(['entry' => $entry]);
    }
}