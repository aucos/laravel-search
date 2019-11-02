<?php

namespace Aucos\LaravelSearch\Searcher;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GroupedCount extends Searcher
{
    /**
     * Which conditions must be met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        return Str::endsWith($this->dbField, static::suffix());
    }

    /**
     * Here a suffix will be set, if the database field name has one
     *
     * @return string
     */
    public static function suffix()
    {
        return '__count__raw';
    }

    /**
     * Database search operator
     *
     * @return string
     */
    public function operator()
    {
        if (Str::startsWith($this->searchQuery, ['>=', '<='])) {
            return substr($this->searchQuery, 0, 2);
        }

        if (Str::startsWith($this->searchQuery, ['>', '<'])) {
            return substr($this->searchQuery, 0, 1);
        }

        return '=';
    }

    /**
     * Value to search for
     *
     * @return string
     */
    public function value()
    {
        $value = $this->searchQuery;
        if (Str::startsWith($this->searchQuery, ['>=', '<='])) {
            $value = substr($this->searchQuery, 2);
        }

        if (Str::startsWith($this->searchQuery, ['>', '<'])) {
            $value = substr($this->searchQuery, 1);
        }

        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public function searchOperation(Builder $query)
    {
        return $query->havingRaw("COUNT({$this->field()}) {$this->operator()} {$this->value()}");
    }
}
