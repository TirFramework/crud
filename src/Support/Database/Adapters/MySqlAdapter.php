<?php

namespace Tir\Crud\Support\Database\Adapters;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use Tir\Crud\Support\Database\DatabaseAdapterInterface;

/**
 * MySQL Database Adapter
 *
 * Handles MySQL/MariaDB specific operations
 * This is the default/standard SQL adapter
 */
class MySqlAdapter implements DatabaseAdapterInterface
{
    public function getDriverName(): string
    {
        return 'mysql';
    }

    public function supports(Connection $connection): bool
    {
        return in_array($connection->getDriverName(), ['mysql', 'mariadb']);
    }

    public function setRequestFieldName(mixed $field): mixed
    {
        return $field->name;
    }

    public function processRequestData(array $requestData): array
    {
        // MySQL doesn't need special request processing
        return $requestData;
    }

    // public function configureRelations($query, $field, $model): mixed
    // {
    //     // Standard SQL relation handling
    //     $relationTable = $model->{$field->relation->name}()->getRelated()->getTable();
    //     $relationKey = $relationTable . '.' . $field->relation->key;
    //     $query = $query->with($field->relation->name, function ($q) use ($field, $relationKey) {
    //         $q->select($relationKey . ' as value', $field->relation->field . ' as label');
    //     });

    //     return $query;
    // }

    public function configureRelations($query, $field, $model): mixed
    {
        $relName   = $field->relation->name;       // ex: users
        $relation  = $model->{$relName}();

        $pivotTable   = $relation->getTable();                 // ex: minimal_example_user
        $parentTable  = $relation->getParent()->getTable();    // ex: minimal_examples
        $relationTable = $relation->getRelated()->getTable();   // ex: users
        $parentKey = $field->relation->key ?? $relation->getParentKeyName(); // ex: id


        if($field->type === 'Select'){

            $pivotForeignCol = Str::after($relation->getForeignPivotKeyName(), '.'); // ex: 'minimal_example_id'
            $pivotRelatedCol = Str::after($relation->getRelatedPivotKeyName(), '.'); // ex: 'user_id'

            $aggExpr = "CAST(CONCAT('[', IFNULL(GROUP_CONCAT(DISTINCT {$pivotTable}.{$pivotRelatedCol} ORDER BY {$pivotTable}.{$pivotRelatedCol} SEPARATOR ','), ''), ']') AS JSON)";


            $sub = DB::table($pivotTable)
                ->whereColumn("{$pivotTable}.{$pivotForeignCol}", "{$parentTable}.{$parentKey}")
                ->selectRaw($aggExpr);


            return $query->selectSub($sub, $relName)->withCasts([$relName => 'array']);
        }


        // Standard SQL relation handling
        $relationKey = $relationTable . '.' . $parentKey;
        $query = $query->with($relName, function ($q) use ($field, $relationKey) {
            $q->select($relationKey . ' as value', $field->relation->field . ' as label');
        });

        return $query;

    }

    public function handleManyToManyFilter($query, $field, $value, $model): mixed
    {
        // Get table name from relation
        $table = $model->{$field->relation->name}()->getRelated()->getTable();

        // Get primary key from relation
        $primaryKey = $model->{$field->relation->name}()->getRelated()->getKeyName();
        $primaryKey = $table . '.' . $primaryKey;

        if (is_array($value)) {
            $query->whereHas($field->relation->name, function ($q) use ($primaryKey, $value) {
                $q->whereIn($primaryKey, $value);
            });
        } else {
            $query->whereHas($field->relation->name, function ($q) use ($primaryKey, $value) {
                $q->where($primaryKey, $value);
            });
        }

        return $query;
    }

    public function getRelationPrimaryKey($model, $field): string
    {
        $table = $model->{$field->relation->name}()->getRelated()->getTable();
        $primaryKey = $model->{$field->relation->name}()->getRelated()->getKeyName();

        return $table . '.' . $primaryKey;
    }

    public function applyDateFilter($query, string $column, array $dateRange): mixed
    {
        // Standard SQL date filtering
        $startDate = \Carbon\Carbon::make($dateRange[0])->startOfDay();
        $endDate = \Carbon\Carbon::make($dateRange[1])->endOfDay();

        $query->whereDate($column, '>=', $startDate);
        $query->whereDate($column, '<=', $endDate);

        return $query;
    }

    public function getSelectColumns($model, array $indexFields): array
    {
        // MySQL/SQL: Use table-prefixed column names
        $selectFields = [];
        $selectFields[] = $model->getTable() . '.' . $model->getKeyName();

        foreach ($indexFields as $field) {
            // Check if field is many to many relation or not
            if (!$field->virtual) {
                if (!isset($field->relation) || !$field->multiple) {
                    $selectFields[] = $model->getTable() . '.' . $field->name;
                }
            }
        }

        return $selectFields;
    }


    public function getSql($query): array
    {
        // MySQL specific: Get the raw query string
        return [$query->toSql()];
    }
}
