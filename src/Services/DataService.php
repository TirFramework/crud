<?php

namespace Tir\Crud\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Tir\Crud\Support\Enums\FilterType;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Builder;
use Tir\Crud\Support\Hooks\IndexDataHooks;
use Tir\Crud\Support\Database\DatabaseAdapterFactory;

class DataService
{
    use IndexDataHooks;
    private $scaffolder;
    private $model;
    private $query;
    private $onlyTrashed = false;
    private array $selectFields = [];



    public function __construct($scaffolder, $model)
    {
        $this->scaffolder = $scaffolder;
        $this->model = $model;

    }

    /**
     * Set hooks from controller
     */
    public final function setHooks(array $hooks): void
    {
        $this->crudHookCallbacks = $hooks;
    }

    public final function getData($onlyTrashed = false)
    {
        // Store the trash mode for use in initQuery
        $this->onlyTrashed = $onlyTrashed;
        $this->query = $this->dataQuery();
        return $this->query;

    }

    private function model()
    {
        return $this->model;
    }

    private function scaffolder()
    {
        return $this->scaffolder;
    }

    /**
     * Build the main data query with all modifications
     */
    private function dataQuery(): mixed
    {
        // Define the default behavior as a closure
        $this->query = $this->initQuery();
        $this->query = $this->select($this->query);
        $this->query = $this->getRelations($this->query);
        $this->query = $this->applySearch($this->query);
        $this->query = $this->applyFilters($this->query);
        $this->query = $this->applySort($this->query);
        $this->query = $this->applyModifiedQuery($this->query);

        $adapter = DatabaseAdapterFactory::create($this->model()->getConnection());
        Log::debug('Data query initialized', [

            'query' => $adapter->getSql($this->query),
        ]);
        $this->query = $this->applyPaginate($this->query);


        return $this->query;
    }


    private function initQuery(): mixed
    {

        // Define the default behavior as a closure
        $defaultInitQuery = function () {
            $query = $this->model()->query();
            if ($this->onlyTrashed) {
                $query = $query->onlyTrashed();
            }
            return $query;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onInitQuery', $defaultInitQuery);
    }


    private function select($query): mixed
    {
        // Define the default behavior as a closure
        $defaultSelect = function ($q = null) use ($query) {
            if ($q !== null) {
                $query = $q;
            }
            $columns = $this->selectColumns();
            return $query->select($columns);
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onSelect', $defaultSelect, $query);
    }


    private function getRelations($query)
    {
        // Define the default behavior as a closure
        $defaultRelations = function ($q = null) use ($query) {
            if ($q !== null) {
                $query = $q;
            }

            // Use database adapter for relation handling
            $adapter = DatabaseAdapterFactory::create($this->model()->getConnection());

            foreach ($this->scaffolder()->getIndexFields() as $field) {
                if (isset($field->relation)) {
                    $query = $adapter->configureRelations($query, $field, $this->model());
                }
            }

            return $query;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onRelation', $defaultRelations, $query);
    }


    private function applySearch($query): mixed
    {

        $defaultSearch = function ($q = null) use ($query) {
            if ($q !== null) {
                $query = $q;
            }
            $req = request()->input('search');
            if ($req == null) {
                return $query;
            }
            $searchableFields = $this->scaffolder()->fieldsHandler()->getSearchableFields();

            if (empty($searchableFields)) {
                return $query;
            }

            foreach ($searchableFields as $field) {
                $query->orWhere($field->name, 'like', "%$req%");
            }
            return $query;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onSearch', $defaultSearch, $query);
    }

    private function applyFilters($query): mixed
    {

        $defaultFilters = function ($q = null) use ($query) {
            if ($q !== null) {
                $query = $q;
            }
            $req = json_decode(request()->input('filters'));
            if ($req == null) {
                return $query;
            }

            $filters = $this->getFilter($req);

            // Use database adapter for filtering
            $adapter = DatabaseAdapterFactory::create($this->model()->getConnection());

            foreach ($filters['original'] as $filter) {
                if ($filter['type'] == FilterType::Select) {
                    $query->whereIn($filter['column'], $filter['value']);
                } elseif ($filter['type'] == FilterType::Slider) {
                    $query->where($filter['column'], '>=', $filter['value'][0]);
                    $query->where($filter['column'], '<=', $filter['value'][1]);
                } elseif ($filter['type'] == FilterType::DatePicker) {
                    $query = $adapter->applyDateFilter($query, $filter['column'], $filter['value']);
                } elseif ($filter['type'] == FilterType::Search) {
                    $query->where($filter['column'], 'like', "%" . $filter['value'] . "%");
                }
            }foreach ($filters['relational'] as $filter) {
                $query->whereHas($filter['relation'], function (Builder $q) use ($filter) {
                    $q->whereIn($filter['primaryKey'], $filter['value']);
                });
            }

            foreach ($filters['customQuery'] as $filter) {
                $query = $filter['query']($query, $filter['req']);
            }

            return $query;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onFilter', $defaultFilters, $query);
    }

    private function applySort($query): mixed
    {
        // Define the default behavior as a closure
        $defaultSort = function ($q = null) use ($query) {

            if ($q !== null) {
                $query = $q;
            }

            $req = request()->input('sorter');
            if ($req == null) {
                return $query->orderBy('created_at', 'DESC');
            }

            $sort = json_decode($req);

            if (!isset($sort->field)) {
                return $query->orderBy('created_at', 'DESC');
            }
            $sort->order = $sort->order == 'ascend' ? 'ASC' : 'DESC';
            return $query->orderBy($sort->field, $sort->order);
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onSort', $defaultSort, $query);
    }

    private function applyModifiedQuery($query): mixed
    {
        // Define the default behavior as a closure
        $defaultModifiedQuery = function ($q = null) use ($query) {
            if ($q !== null) {
                $query = $q;
            }
            return $query;
        };

        return $this->executeWithHook('onModifyQuery', $defaultModifiedQuery, $query);
    }

    private function applyPaginate($query): mixed
    {
        $defaultPagination = function ($q = null) use ($query) {
            if ($q !== null) {
                $query = $q;
            }
            $perPage = request()->input('result', 15); // Default to 15 if not provided
            return $query->paginate($perPage);
        };
        return $this->executeWithHook('onPaginate', $defaultPagination, $query);
    }

    private function indexResponse($items): mixed
    {
        // Define the default behavior as a closure
        $defaultResponse = function ($i = null) use ($items) {
            if ($i !== null) {
                $items = $i;
            }
            return Response::json($items, 200);
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onIndexResponse', $defaultResponse, $items);
    }

    private function selectColumns(): array
    {
        // Use database adapter for column selection
        $adapter = DatabaseAdapterFactory::create($this->model()->getConnection());
        $this->selectFields = $adapter->getSelectColumns($this->model(), $this->scaffolder()->scaffold('data-index')->fieldsHandler()->getIndexFields());

        return $this->selectFields;
    }

    private function getFilter($req): array
    {

        $filters = ['original' => [], 'relational' => [], 'customQuery' => []];

        $original = [];
        $relational = [];
        $customQuery = [];

        foreach ($req as $filter => $value) {
            $field = $this->scaffolder()->fieldsHandler()->getFieldByName($filter);
            if (isset($field->filterQuery)) {
                $customQuery[] = ['query' => $field->filterQuery, 'req' => $value];
                //if it has filterQuery, we escape rest of the code
                continue;
            }
            //if filter is manyToMany relation
            if (isset($field->relation) && $field->multiple ?? null == true) {
                // Use database adapter for many-to-many filtering
                $adapter = DatabaseAdapterFactory::create($this->model()->getConnection());
                $primaryKey = $adapter->getRelationPrimaryKey($this->model(), $field);

                $relational[] = ['relation' => $field->relation->name, 'value' => $value, 'primaryKey' => $primaryKey];
            } else {
                $original[] = ['column' => $field->name, 'value' => $req->{$field->name}, 'type' => $field->filterType];
            }
        }

        $filters['original'] = $original;
        $filters['relational'] = $relational;
        $filters['customQuery'] = $customQuery;

        return $filters;
    }

}
