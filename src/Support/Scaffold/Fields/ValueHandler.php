<?php

namespace Tir\Crud\Support\Scaffold\Fields;

trait ValueHandler
{
    /**
     * Extract values from a relational field for display/editing
     *
     * This method orchestrates the extraction of relation values by:
     * 1. Validating the relation configuration
     * 2. Determining whether to use DATA MODE (IDs) or DISPLAY MODE (readable values)
     * 3. Extracting the appropriate values based on relation type
     *
     * TWO-APPROACH PATTERN:
     * =====================
     *
     * Approach 1: DATA MODE (Select Fields - Editable)
     * - Returns: Array of IDs [1, 2, 3]
     * - Used when: Field has data (options), is not virtual, is not readonly
     * - Purpose: Populate select/multiselect fields with current selections
     * - Example: User editing a post can see which categories are selected
     *
     * Approach 2: DISPLAY MODE (Show Fields - Readonly)
     * - Returns: Array of display values ["Category A", "Category B"]
     * - Used when: Field has no data, is virtual, or is readonly
     * - Purpose: Show human-readable values on index/detail pages
     * - Example: Index page shows "John Doe" instead of user ID "5"
     *
     * @param mixed $model The model instance to extract relational values from
     * @return array Array of values (IDs or display values depending on mode)
     * @throws \Exception If relation is not properly configured
     */
    private function setRelationalValue($model)
    {
        // Step 1: Validate relation configuration
        $this->validateRelation($model);

        // Step 2: Get relation instance and determine its type
        $relation = $model->{$this->relation->name}();
        $relationType = class_basename($relation);

        // Step 3: Determine which field to extract (ID or display value)
        $fieldKey = $this->determineFieldKey();

        // Step 4: Extract values based on relation type
        return $this->extractRelationValues($model, $relationType, $fieldKey);
    }

    /**
     * Validate that the relation is properly configured
     *
     * @param mixed $model The model instance
     * @throws \Exception If relation is not properly defined
     */
    private function validateRelation($model): void
    {
        if (!isset($this->relation->name)) {
            throw new \Exception('Relation is not defined for field: ' . $this->name);
        }

        if (!isset($model->{$this->relation->name})) {
            throw new \Exception(
                'For the field: ' . $this->name .
                ' The Relation "' . $this->relation->name . '" not found on model'
            );
        }
    }

    /**
     * Determine which field to extract: primary key (ID) or display field (name, title, etc.)
     *
     * Decision Logic:
     * - If field has NO data (no options)  → DISPLAY MODE (show readable values)
     * - If field is virtual                → DISPLAY MODE (computed, readonly)
     * - If field is readonly               → DISPLAY MODE (can't edit anyway)
     * - Otherwise                          → DATA MODE (return IDs for select)
     *
     * @return string The field key to extract (e.g., 'id' or 'name')
     */
    private function determineFieldKey(): string
    {
        $isDisplayMode = count($this->data) === 0 || $this->virtual === true || $this->readonly === true;

        if ($isDisplayMode) {
            // DISPLAY MODE: Use the display field (e.g., 'name', 'title')
            // Force readonly since we're showing display values, not editable IDs
            $this->readonly = true;
            return $this->relation->field; // Example: 'name' → returns ["John", "Jane"]
        } else {
            // DATA MODE: Use the primary key (e.g., 'id')
            return $this->relation->key; // Example: 'id' → returns [1, 2, 3]
        }
    }

    /**
     * Extract relation values based on the relation type
     *
     * Handles three main relation types:
     * - BelongsToMany: Many-to-many relations (via pivot table)
     * - HasMany: One-to-many relations
     * - BelongsTo: Many-to-one relations
     *
     * @param mixed $model The model instance
     * @param string $relationType The relation type (BelongsTo, BelongsToMany, HasMany, etc.)
     * @param string $fieldKey The field to extract from related records
     * @return array Array of extracted values
     */
    private function extractRelationValues($model, string $relationType, string $fieldKey): array
    {
        // Handle collection-based relations (multiple records)
        if ($relationType === 'BelongsToMany' || $relationType === 'HasMany') {
            return $this->extractCollectionValues($model, $fieldKey);
        }

        // Handle single-record relations
        if ($relationType === 'BelongsTo') {
            return $this->extractBelongsToValue($model, $fieldKey);
        }

        // Fallback: Return empty array if relation type is not handled
        return [];
    }

    /**
     * Extract values from collection-based relations (BelongsToMany, HasMany)
     *
     * @param mixed $model The model instance
     * @param string $fieldKey The field to extract from each related record
     * @return array Array of values from the collection
     *
     * Example (DATA MODE):    [1, 3, 5] (category IDs)
     * Example (DISPLAY MODE): ["Tech", "News", "Sports"] (category names)
     */
    private function extractCollectionValues($model, string $fieldKey): array
    {
        return $model->{$this->relation->name}
            ->map(function ($relatedModel) use ($fieldKey) {
                return $relatedModel->{$fieldKey};
            })
            ->toArray();
    }

    /**
     * Extract value from BelongsTo relation (single record)
     *
     * @param mixed $model The model instance
     * @param string $fieldKey The field to extract from the related record
     * @return array Array with single value (wrapped for consistency)
     *
     * Example (DATA MODE):    [5] (author ID)
     * Example (DISPLAY MODE): ["John Doe"] (author name)
     *
     * Note: Value is wrapped in array to maintain consistent return type with other relation types
     */
    private function extractBelongsToValue($model, string $fieldKey): array
    {
        $relatedModel = $model->{$this->relation->name};

        // Return empty array if no related model exists (null relationship)
        if (!$relatedModel) {
            return [];
        }

        return [$relatedModel->{$fieldKey}];
    }
}
