<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetRegister
{
    /**
     * @abstract Nombre de la tabla a consultar
     */
    private static ?string $table = null;

    /**
     * @abstract Definir o actualizar el nombre de la tabla
     * 
     * @param string $name Nombre de la tabla
     * 
     * @return void
     */
    public static function setTable(string $name): void
    {
        self::$table = $name;
    }

    /**
     * @abstract Ejecuta una consulta dentro de un bloque seguro con manejo de excepciones.
     *
     * @param callable $query Función que contiene la consulta a ejecutar.
     * 
     * @return mixed Resultado de la consulta.
     *
     * @throws \Exception Si no se ha definido la tabla o si ocurre un error en la consulta.
     */
    private static function run(callable $query): mixed
    {
        if (empty(self::$table)) {
            throw new \Exception("La propiedad 'table' no está definida");
        }

        try {
            return $query();
        } catch (\Exception $e) {
            throw new \Exception("Error ejecutando la consulta: {$e->getMessage()}");
        }
    }

    /**
     * @abstract Obtener todos los registros de la tabla
     * 
     * @return Collection
     * 
     * @throws \Exception Si la tabla no está definida
     */
    public static function getAll(array $columns = ['*']): Collection 
    {
        return self::run(function() use ($columns) {
            return DB::table(self::$table)->select($columns)->get();
        });
    }

    /**
     * @abstract Obtiene un registro individual según su ID.
     *
     * @param int $id ID del registro a consultar.
     * @param array $columns Columnas requeridas del registro. Por defecto, todas (*).
     * @return object|null Objeto con el registro o null si no existe
     * 
     * @throws \Exception Si la tabla no está definida
     */
    public static function findById(int $id, array $columns = ['*']): ?object 
    {
        return self::run(function() use ($id, $columns) {
            return DB::table(self::$table)
                     ->select($columns)
                     ->where('id', '=', $id)
                     ->first();
        });
    }
}