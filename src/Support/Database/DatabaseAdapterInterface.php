<?php

namespace Tir\Crud\Support\Database;

/**
 * Database Adapter Interface
 *
 * Defines the contract for database-specific operations
 * allowing the CRUD system to work with different database types
 * without scattering database-specific code throughout the system.
 */
interface DatabaseAdapterInterface
{
    /**
     * Get the database driver name
     */
    public function getDriverName(): string;

    /**
     * Check if this adapter supports the given connection
     */
    public function supports(\Illuminate\Database\Connection $connection): bool;

    /**
     * Process request data for this database type
     * Handle field filtering, format conversion, and database-specific transformations
     */
    public function processRequestData(array $requestData, array $scaffolderFields = []): array;

    /**
     * Configure query relations for this database type
     * Handle any database-specific relation logic
     */
    public function configureRelations($query, $field, $model): mixed;

    /**
     * Handle many-to-many relation filtering for this database type
     */
    public function handleManyToManyFilter($query, $field, $value, $model): mixed;

    /**
     * Get the primary key column name for relations
     */
    public function getRelationPrimaryKey($model, $field): string;

    /**
     * Apply date range filter for this database type
     * Handle database-specific date filtering (e.g., MongoDB BSON dates)
     */
    public function applyDateFilter($query, string $column, array $dateRange): mixed;

    /**
     * Get select columns for index queries
     * Handle database-specific column selection logic
     */
    public function getSelectColumns($model, array $indexFields): array;

    /**
     * Process fillable data for database-specific mass assignment handling
     * Handle nested field filtering and fillable rules
     */
    public function processFillableData(array $requestData, array $scaffolderFields, $model): array;

    /**
     * Apply filtered data to model with database-specific logic
     * Bypass or enhance Laravel's fillable mechanism as needed
     * @param mixed $model The model instance to fill
     * @param array $filteredData The data to fill the model with
     * @param array $scaffolderFields The scaffolder field definitions for fallback fillable rules
     */
    public function fillModel($model, array $filteredData, array $scaffolderFields = []): mixed;

    public function getSql($query): array;
}
