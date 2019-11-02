<?php

namespace Aucos\LaravelSearch;

use Aucos\LaravelSearch\Searcher\DateFrom;
use Aucos\LaravelSearch\Searcher\DateTo;
use Aucos\LaravelSearch\Searcher\GroupedCount;
use Aucos\LaravelSearch\Searcher\SearchFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Search
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $normalizedSearchable;

    public function scopeSearch(Builder $query)
    {
        $this->request = request();

        if (count($this->request->request)) {
            $this->normalizeSearchArray();

            if ($this->request->has('search')) {
                return $this->globalSearch($query);
            }

            return $this->localSearch($query);
        }

        return $query;
    }

    public function scopeOrder(Builder $query, $orderField = 'id', $orderDir = 'asc')
    {
        $this->request = request();
        $this->normalizeSearchArray();

        $orderFields = collect(explode(',', $this->request->get('order_by', $orderField)));
        $orderDirs = collect(explode(',', $this->request->get('order_dir', $orderDir)));

        $searchable = $this->normalizedSearchable->flatten()->map(function($orderField) {
            return preg_replace(['/\_\_from$/', '/\_\_to$/'], '', $orderField);
        })->unique();

        $orderFields->filter(function ($orderField) use ($searchable) {
            return $searchable->contains($orderField);
        })->each(function ($orderField, $index) use ($query, $orderDirs) {
            $orderDir = $orderDirs->get($index, 'asc');
            $query->orderBy($orderField, $orderDir);
        });

        return $query;
    }

    private function globalSearch(Builder $query)
    {
        $dbFields = $this->normalizedSearchable->flatten()->filter(function ($dbField) {
            return !Str::endsWith($dbField, [DateFrom::suffix(), DateTo::suffix(), GroupedCount::suffix()]);
        })->toArray();

        $q = $this->request->get('search');
        $this->performSearch($query, $dbFields, $q);

        return $query;
    }

    private function localSearch(Builder $query)
    {
        $this->normalizedSearchable->each(function ($dbFields, $aliasName) use ($query) {
            $q = $this->request->get($aliasName);
            $this->performSearch($query, $dbFields, $q);
        });

        return $query;
    }

    private function performSearch(Builder $query, array $dbFields, $q)
    {
        $q = trim($q);

        if ($q !== '') {
            foreach ($dbFields as $dbField) {
                if (SearchFactory::isOuterWhere($dbField)) {
                    SearchFactory::build($q, $dbField)->searchOperation($query);
                }
            }

            $query->where(function ($query) use ($dbFields, $q) {
                foreach ($dbFields as $dbField) {
                    if (SearchFactory::isInnerWhere($dbField)) {
                        SearchFactory::build($q, $dbField)->searchOperation($query);
                    }
                }
            });
        }
    }

    public function getNormalizedSearchable()
    {
        return $this->normalizedSearchable;
    }

    public function normalizeSearchArray()
    {
        if ($this->normalizedSearchablesIsSet()) {
            return $this->normalizedSearchable;
        }

        $this->initSearchableFields();
    }

    private function initSearchableFields()
    {
        $this->normalizedSearchable = collect([]);

        foreach ($this->getFieldsFromModel() as $alias => $dbFields) {
            $normalizedKey = $this->normalizeQueryName($alias, $dbFields);
            $normalizedDbFields = $this->normalizeDbFields($dbFields);
            $this->normalizedSearchable[$normalizedKey] = $normalizedDbFields;
        }
    }

    private function getFieldsFromModel()
    {
        $fields = [];

        if ($this->searchableIsSet()) {
            $fields = $this->searchable;
        } elseif ($this->fillableIsSet()) {
            $fields = $this->fillable;
        }

        return $this->addDateFields($fields);
    }

    private function addDateFields($searchable)
    {
        foreach ($this->getDates() as $date) {
            $fromField = $date . DateFrom::suffix();
            $toField = $date . DateTo::suffix();

            if ($this->hasDateSearchOverwrite($date)) {
                $searchable[$fromField] = [$this->dateSearchOverwrite[$date] . DateFrom::suffix()];
                $searchable[$toField] = [$this->dateSearchOverwrite[$date] . DateTo::suffix()];
            } else {
                $searchable[$fromField] = [$fromField];
                $searchable[$toField] = [$toField];
            }
        }
        return $searchable;
    }

    private function hasDateSearchOverwrite($date)
    {
        return isset($this->dateSearchOverwrite) && array_key_exists($date, $this->dateSearchOverwrite);
    }

    private function normalizeQueryName($alias, $dbFields)
    {
        return $this->arrayElementIsSingle($alias, $dbFields)
            ? $dbFields
            : $alias;
    }

    private function normalizeDbFields($dbFields)
    {
        $dbFields = (array)$dbFields;

        return array_map(function ($dbField) {
            return $this->addSelfTableName($dbField);
        }, $dbFields);
    }

    private function arrayElementIsSingle($alias, $dbFields)
    {
        return is_int($alias) && is_string($dbFields);
    }

    private function addSelfTableName($dbField)
    {
        if (str_contains($dbField, '.')) {
            return $dbField;
        }

        return "{$this->getTable()}.{$dbField}";
    }

    private function searchableIsSet()
    {
        return null !== $this->searchable && count($this->searchable);
    }

    private function fillableIsSet()
    {
        return null !== $this->fillable && count($this->fillable);
    }

    private function normalizedSearchablesIsSet()
    {
        return isset($this->normalizedSearchable) && (bool)count($this->normalizedSearchable);
    }
}
