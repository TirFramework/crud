<?php

namespace Tir\Crud\Support\Database\Adapters;

use Illuminate\Support\Arr;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
use Tir\Crud\Support\Database\DatabaseAdapterInterface;

/**
 * MongoDB Database Adapter
 *
 * Handles MongoDB specific operations including:
 * - Request data processing (array grouping by numeric indexes)
 * - Relation handling for MongoDB documents
 * - Many-to-many filtering for MongoDB collections
 * - Date filtering with MongoDB BSON types
 */
class MongoDbAdapter implements DatabaseAdapterInterface
{
    // ========================================
    // ADAPTER IDENTIFICATION & CONFIGURATION
    // ========================================
    
    public function getDriverName(): string
    {
        return 'mongodb';
    }

    public function supports(Connection $connection): bool
    {
        return $connection->getDriverName() === 'mongodb';
    }

    public function setRequestFieldName(mixed $field): mixed
    {
        return $field->originalName;
    }

    // ========================================
    // REQUEST DATA PROCESSING
    // ========================================

    public function processRequestData(array $requestData, array $scaffolderFields = []): array
    {
        // MongoDB: Handle field filtering and format conversion for request processing
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
            $isAllowed = false;

            // Check exact match first
            if (in_array($key, $allowedFieldNames)) {
                $isAllowed = true;
            } else {
                // Check if key starts with any allowed field pattern (for family.0.dob etc.)
                foreach ($allowedFieldNames as $allowedField) {
                    if (strpos($key, $allowedField . '.') === 0) {
                        $isAllowed = true;
                        break;
                    }
                }
            }

            if ($isAllowed) {
                $clearedRequest[$key] = $value;
            }
        }

        // Convert dot notation to nested arrays for MongoDB
        return Arr::undot($clearedRequest);
    }

    // ========================================
    // QUERY & RELATION CONFIGURATION
    // ========================================

    public function configureRelations($query, $field, $model): mixed
    {
        // MongoDB specific relation handling
        // if (isset($field->relation)) {
        //     if ($field->multiple) {
        //         // MongoDB needs foreign key in many-to-many relation
        //         $foreignKey = $query->getModel()->{$field->relation->name}()->getForeignKey();
        //         $otherKey = $query->getModel()->{$field->relation->name}()->getRelated()->getKeyName();

        //         $query->with([$field->relation->name => function ($q) use ($foreignKey, $otherKey) {
        //             // MongoDB specific relation configuration if needed
        //         }]);
        //     } else {
        //         // Standard relation for MongoDB
        //         $query->with($field->relation->name);
        //     }
        // }

        return $query;

    }

    public function handleManyToManyFilter($query, $field, $value, $model): mixed
    {
        // MongoDB specific many-to-many filtering
        $primaryKey = $model->{$field->relation->name}()->getRelated()->getKeyName();

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
        // MongoDB doesn't need table.column format
        return $model->{$field->relation->name}()->getRelated()->getKeyName();
    }

    /**
     * Apply date filter for MongoDB
     * MongoDB requires special handling for date ranges
     */
    public function applyDateFilter($query, string $column, array $dateRange): mixed
    {
        $startDate = \Carbon\Carbon::make($dateRange[0])->startOfDay();
        $endDate = \Carbon\Carbon::make($dateRange[1])->endOfDay();

        $query->where(function ($query) use ($column, $startDate) {
            // Check if MongoDB BSON class exists (only when MongoDB extension is installed)
            if (class_exists('MongoDB\BSON\UTCDateTime')) {
                $mongoStartDate = new \MongoDB\BSON\UTCDateTime($startDate);
                $query->where($column, '>=', $mongoStartDate);
            }
            $query->orWhere($column, '>=', $startDate->toDateString());
        });

        $query->where(function ($query) use ($column, $endDate) {
            // Check if MongoDB BSON class exists (only when MongoDB extension is installed)
            if (class_exists('MongoDB\BSON\UTCDateTime')) {
                $mongoEndDate = new \MongoDB\BSON\UTCDateTime($endDate);
                $query->where($column, '<=', $mongoEndDate);
            }
            $query->orWhere($column, '<=', $endDate->toDateString());
        });

        return $query;
    }

    public function getSelectColumns($model, array $indexFields): array
    {
        // MongoDB specific: Just get field names without table prefixes
        return collect($indexFields)->pluck('name')->toArray();
    }

    public function getSql($query): array
    {
        // MongoDB specific: Get the raw query string
        return $query->toMql();
    }

    // ========================================
    // MODEL FILLING & DATA PERSISTENCE 
    // ========================================

    /**
     * Process fillable data for MongoDB with dot notation support
     * Handles nested field filtering based on scaffolder field definitions
     */
    public function processFillableData(array $requestData, array $scaffolderFields, $model): array
    {
        // MongoDB: Request is already processed and in correct nested format
        // Just return the data as-is for filling
        return $requestData;
    }

    /**
     * Apply filtered data directly to MongoDB model
     * Handles nested array data from processRequestData with selective updates
     * Implements priority-based fillable security with guarded field protection
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
            $scaffolderFillable = collect($scaffolderFields)
                ->filter(function ($field) {
                    // Only include fields that are fillable (default true, or explicitly set to true)
                    return !isset($field->fillable) || $field->fillable !== false;
                })
                ->pluck('request')
                ->flatten()
                ->unique()
                ->toArray();

            if (!empty($scaffolderFillable)) {
                $allowedFields = $scaffolderFillable;
            } else {
                // Fallback: If no explicit fillable rules, allow all filtered data keys
                $allowedFields = array_keys($filteredData);
            }
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
                // If no model fillable, use scaffolder fillable (MongoDB common pattern)
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

        // Step 3: Filter data to only include allowed fields and apply to model
        $secureData = $this->filterDataByAllowedFields($filteredData, $allowedFields);

        // Step 4: Apply secure data with selective updates vs complete array replacement
        foreach ($secureData as $key => $value) {
            if (is_array($value) && $this->isArrayField($key, $value)) {
                // Complete replacement for array fields (like family.*)
                $model->{$key} = $value;
            } else {
                // Selective update for nested object fields (like profile.*)
                $this->updateNestedField($model, $key, $value);
            }
        }

        return $model;
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

    /**
     * Check if a field represents an array that should be completely replaced
     * Array fields are identified dynamically by having numeric keys (indexed arrays)
     */
    private function isArrayField(string $key, $value): bool
    {
        // Check if value has numeric keys (indicating it's an indexed array)
        if (is_array($value) && !empty($value)) {
            $keys = array_keys($value);
            // If the first key is numeric, it's an indexed array that should be replaced entirely
            return is_numeric($keys[0]);
        }

        return false;
    }

    /**
     * Filter data array to only include fields that are allowed by fillable/guarded rules
     * Supports nested dot notation fields (like profile.eyes_color, family.*.first_name)
     */
    private function filterDataByAllowedFields(array $data, array $allowedFields): array
    {
        $filteredData = [];

        foreach ($data as $key => $value) {
            $isAllowed = false;

            // Check exact match first
            if (in_array($key, $allowedFields)) {
                $isAllowed = true;
            } else {
                // Check if key starts with any allowed field pattern (for nested fields)
                foreach ($allowedFields as $allowedField) {
                    // Handle dot notation patterns like profile.eyes_color, family.*.first_name
                    if (strpos($key, $allowedField . '.') === 0 ||
                        strpos($allowedField, $key . '.') === 0 ||
                        $this->matchesPatternWithWildcard($key, $allowedField)) {
                        $isAllowed = true;
                        break;
                    }
                }
            }

            if ($isAllowed) {
                $filteredData[$key] = $value;
            }
        }

        return $filteredData;
    }

    /**
     * Check if a field key matches a pattern with wildcards (like family.*.first_name)
     */
    private function matchesPatternWithWildcard(string $key, string $pattern): bool
    {
        // Convert pattern with * to regex
        $regexPattern = str_replace(['\\*', '.'], ['[^.]+', '\\.'], preg_quote($pattern, '/'));
        return preg_match('/^' . $regexPattern . '$/', $key) === 1;
    }

    /**
     * Update nested fields selectively without overwriting other nested fields
     * Uses deep merge for unlimited nesting levels while preserving existing data
     */
    private function updateNestedField($model, string $key, $value): void
    {
        if (is_array($value)) {
            // For nested objects like profile.*, use deep merge with existing data
            $existingData = $model->{$key} ?? [];
            if (!is_array($existingData)) {
                $existingData = [];
            }
            $model->{$key} = $this->deepMergeArrays($existingData, $value);
        } else {
            // For simple values, set directly
            $model->{$key} = $value;
        }
    }

    /**
     * Recursively merge arrays while preserving existing nested data
     * Handles unlimited nesting levels for object fields
     * Arrays (with numeric keys) are replaced entirely as intended
     */
    private function deepMergeArrays(array $existing, array $new): array
    {
        $result = $existing;

        foreach ($new as $key => $value) {
            if (is_array($value)) {
                // Check if this is an indexed array (should be replaced entirely)
                $isIndexedArray = !empty($value) && array_keys($value) === range(0, count($value) - 1);
                
                if ($isIndexedArray) {
                    // For indexed arrays (like documents.contract.*.test), replace entirely
                    $result[$key] = $value;
                } else {
                    // For associative arrays (objects), recursively merge
                    if (isset($result[$key]) && is_array($result[$key])) {
                        $result[$key] = $this->deepMergeArrays($result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                }
            } else {
                // For scalar values, replace directly
                $result[$key] = $value;
            }
        }

        return $result;
    }

}
