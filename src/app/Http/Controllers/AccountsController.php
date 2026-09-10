<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para la consulta y gestión del Plan Único de Cuentas (PUC).
 */
class AccountsController extends Controller
{
    /**
     * Lista paginada de cuentas contables con filtros opcionales.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 30), 1), 100);

        $accounts = Account::query()
            ->when($request->filled('code'), fn ($q) => $q->where('code', 'LIKE', $request->input('code').'%'))
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'LIKE', '%'.$request->input('name').'%'))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->integer('level')))
            ->when($request->has('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('parent_code'), function ($q) use ($request) {
                $q->whereHas('parent', fn ($p) => $p->where('code', $request->input('parent_code')));
            })
            ->orderBy('code')
            ->paginate($perPage);

        return ResponseHelper::success(['accounts' => $accounts]);
    }

    /**
     * Muestra el detalle de una cuenta contable específica.
     *
     * @param Account $account
     * @return JsonResponse
     */
    public function show(Account $account): JsonResponse
    {
        return ResponseHelper::success(['account' => $account]);
    }

    /**
     * Almacenar una nueva cuenta contable en el PUC.
     */
    public function store(AccountRequest $request): JsonResponse
    {
        try {
            $account = Account::create($request->validated());

            return ResponseHelper::created(['account' => $account], 'Cuenta creada exitosamente.');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::badRequest($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error en AccountsController@store: '.$e->getMessage());

            return ResponseHelper::criticalError('Error al crear la cuenta contable.');
        }
    }

    /**
     * Actualizar una cuenta contable existente (siempre y cuando no sea primaria).
     */
    public function update(AccountRequest $request, Account $account): JsonResponse
    {
        if ($account->isPrimaryAccount()) {
            return ResponseHelper::badRequest('No se pueden modificar cuentas primarias del PUC.');
        }

        try {
            $account->update($request->validated());

            return ResponseHelper::success(['account' => $account], 'Cuenta actualizada exitosamente.');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::badRequest($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error en AccountsController@update: '.$e->getMessage());

            return ResponseHelper::criticalError('Error al actualizar la cuenta contable.');
        }
    }

    /**
     * Eliminar una cuenta contable (siempre y cuando no sea primaria ni tenga asientos asociados).
     */
    public function destroy(Account $account): JsonResponse
    {
        // Verificar si es una cuenta primaria
        if ($account->isPrimaryAccount()) {
            return ResponseHelper::badRequest('No se pueden eliminar cuentas primarias del PUC.');
        }

        // Verificar si tiene líneas de asientos contables asociadas
        if ($account->journalEntryLines()->exists()) {
            return ResponseHelper::badRequest('No se puede eliminar la cuenta porque tiene asientos contables asociados.');
        }

        // Verificar si tiene cuentas hijas asociadas, para no dejarlas huérfanas
        if ($account->children()->exists()) {
            return ResponseHelper::badRequest('No se puede eliminar la cuenta porque tiene cuentas hijas asociadas.');
        }

        $account->delete();

        return ResponseHelper::success([], 'Cuenta eliminada exitosamente.');
    }

    /**
     * Buscar cuentas activas por código o nombre (autocompletado).
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->string('q')->trim()->toString();
        $limit = min(max($request->integer('limit', 20), 1), 100);

        if (mb_strlen($term) < 2) {
            return ResponseHelper::success(['items' => []]);
        }

        $items = Account::query()
            ->select(['code', 'name'])
            ->where('is_active', true)
            ->where(fn ($query) =>
                $query->where('code', 'LIKE', "{$term}%")
                      ->orWhere('name', 'LIKE', "%{$term}%")
            )
            ->orderBy('code')
            ->limit($limit)
            ->get()
            ->map(fn (Account $account) => [
                'code' => $account->code,
                'text' => "{$account->code} - {$account->name}",
            ]);

        return ResponseHelper::success(['items' => $items]);
    }

    /**
     * Obtener cuentas hijas activas de una cuenta padre específica.
     */
    public function children(Account $account): JsonResponse
    {
        $children = $account->children()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'current_balance', 'parent_id'])
            ->map(fn (Account $child) => [
                'code'            => $child->code,
                'name'            => $child->name,
                'current_balance' => (float) $child->current_balance,
            ]);

        return ResponseHelper::success(['accounts' => $children]);
    }

    /**
     * Obtener el saldo actual en tiempo real de una cuenta.
     */
    public function getBalance(Account $account): JsonResponse
    {
        return ResponseHelper::success([
            'code'            => $account->code,
            'name'            => $account->name,
            'current_balance' => (float) $account->current_balance,
        ]);
    }
}