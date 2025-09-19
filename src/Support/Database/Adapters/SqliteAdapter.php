<?php

namespace Tir\Crud\Support\Database\Adapters;

use Illuminate\Database\Connection;
use Tir\Crud\Support\Database\DatabaseAdapterInterface;

/**
 * SQLite Database Adapter
 *
 * Handles SQLite specific operations
 * Most operations are similar to MySQL but with SQLite-specific optimizations
 */
class SqliteAdapter implements DatabaseAdapterInterface
{
    // ========================================
    // ADAPTER IDENTIFICATION & CONFIGURATION
    // ========================================

    public function getDriverName(): string
    {
        return 'sqlite';
    }

    public function supports(Connection $connection): bool
    {
        return $connection->getDriverName() === 'sqlite';
    }

    public function setRequestFieldName(mixed $field): mixed
    {
        return $field->name;
    }

    // ========================================
    // REQUEST DATA PROCESSING
    // ========================================

    public function processRequestData(array $requestData, array $scaffolderFields = []): array
    {
        // SQLite: Handle field filtering for request processing
        if (empty($scaffolderFields)) {
            return $requestData;
        }

        // Extract allowed field paths from scaffolder fields, but only include fillable fields
        $allowedFieldNames = collect($scaffolderFields)
            ->filter(function ($field) {
                // Only include fields that are fillable (default true, or explicitly set to true)
                return !isset($field->fillable) || $field->fillable !== false;
            })
            ->pluck('request')
            ->flatten()
            ->unique()
            ->toArray();

        // Filter request data to only include allowed and fillable fields
        $clearedRequest = [];
        foreach ($requestData as $key => $value) {
            if (in_array($key, $allowedFieldNames)) {
                $clearedRequest[$key] = $value;
            }
        }

        // SQLite doesn't need format conversion - return as-is
        return $clearedRequest;
    }

    // ========================================
    // QUERY & RELATION CONFIGURATION
    // ========================================

    public function configureRelations($query, $field, $model): mixed
    {
        // Standard SQL relation handling (same as MySQL)
        if (isset($field->relation)) {
            if ($field->multiple) {
                $query->with($field->relation->name);
            } else {
                $query->with($field->relation->name);
            }
        }

        return $query;
    }

    public function handleManyToManyFilter($query, $field, $value, $model): mixed
    {
        // SQLite specific many-to-many filtering (similar to MySQL but optimized for SQLite)
        $table = $model->{$field->relation->name}()->getRelated()->getTable();
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
        // SQLite date filtering (similar to MySQL)
        $startDate = \Carbon\Carbon::make($dateRange[0])->startOfDay();
        $endDate = \Carbon\Carbon::make($dateRange[1])->endOfDay();

        $query->whereDate($column, '>=', $startDate);
        $query->whereDate($column, '<=', $endDate);

        return $query;
    }

    public function getSelectColumns($model, array $indexFields): array
    {
        // SQLite: Same as MySQL (SQL standard)
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
        // SQLite specific: Get the raw query string
        return [$query->toSql()];
    }

    // ========================================
    // MODEL FILLING & DATA PERSISTENCE
    // ========================================

    /**
     * Process fillable data for SQLite - uses standard Laravel fillable mechanism
     */
    public function processFillableData(array $requestData, array $scaffolderFields, $model): array
    {
        // SQLite: Request is already processed and filtered by processRequestData
        // Just return the data as-is for filling
        return $requestData;
    }

    /**
     * Apply filtered data to SQLite model using standard Laravel fill() method
     */
    public function fillModel($model, array $filteredData, array $scaffolderFields = []): mixed
    {
        // Step 1: Determine base fillable fields (priority-based)
        $modelFillable = $model->getFillable();

        if (!empty($modelFillable)) {
            // Priority 1: Use model's explicit fillable array
            $allowedFields = $modelFillable;
        } else {
            // Priority 2: Extract fillable fields from scaffolder
            $allowedFields = collect($scaffolderFields)
                ->filter(function ($field) {
                    // Only include fields that are fillable (default true, or explicitly set to true)
                    return !isset($field->fillable) || $field->fillable !== false;
                })
                ->pluck('request')
                ->flatten()
                ->unique()
                ->toArray();
        }

        // Step 2: Always remove guarded fields from allowed fields
        $guardedFields = $model->getGuarded();
        if (!empty($guardedFields) && $guardedFields !== ['*']) {
            // Filter out guarded fields and their nested patterns
            $allowedFields = $this->filterOutGuardedFields($allowedFields, $guardedFields);
        } elseif ($guardedFields === ['*']) {
            // If guarded is ['*'], check if model has explicit fillable
            $modelFillableForGuarded = $model->getFillable();
            if (!empty($modelFillableForGuarded)) {
                // Use model's explicit fillable if available
                $allowedFields = $modelFillableForGuarded;
            } else {
                // If no model fillable, use scaffolder fillable
                $allowedFields = collect($scaffolderFields)
                    ->filter(function ($field) {
                        return !isset($field->fillable) || $field->fillable !== false;
                    })
                    ->pluck('request')
                    ->flatten()
                    ->unique()
                    ->toArray();
            }
        }

        // Step 3: Filter data to only include allowed fields
        $secureData = array_intersect_key($filteredData, array_flip($allowedFields));

        // Step 4: Use Laravel's standard fill method for SQL databases
        return $model->fill($secureData);
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * Filter out guarded fields and their nested patterns from allowed fields
     * Handles cases like guarded=['profile'] should block 'profile.eyes_color', 'profile.height', etc.
     */
    private function filterOutGuardedFields(array $allowedFields, array $guardedFields): array
    {
        $filteredFields = [];
        
        foreach ($allowedFields as $field) {
            $isGuarded = false;
            
            foreach ($guardedFields as $guardedField) {
                // Check if field matches guarded field exactly
                if ($field === $guardedField) {
                    $isGuarded = true;
                    break;
                }
                
                // Check if field is a nested field of a guarded field (like profile.eyes_color when profile is guarded)
                if (strpos($field, $guardedField . '.') === 0) {
                    $isGuarded = true;
                    break;
                }
            }
            
            if (!$isGuarded) {
                $filteredFields[] = $field;
            }
        }
        
        return $filteredFields;
    }
}
