<?php

namespace Aucos\LaravelSearch\Searcher;

use Illuminate\Database\Eloquent\Builder;

abstract class Searcher
{
    /**
     * @var string
     */
    protected $searchQuery;

    /**
     * @var string
     */
    protected $dbField;

    /**
     * @param string $searchQuery
     * @param string $dbField
     */
    public function __construct($searchQuery, $dbField)
    {
        $this->searchQuery = $searchQuery;
        $this->dbField = $dbField;
    }

    /**
     * Which conditions must ne met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public abstract function useMe();

    /**
     * Here a suffix will be set, if the database field name has one
     *
     * @return string
     */
    public static function suffix()
    {
        return '';
    }

    /**
     * Database field name
     *
     * @return string
     */
    public function field()
    {
        if (ends_with($this->dbField, static::suffix())) {
            return substr($this->dbField, 0, -1 * strlen(static::suffix()));
        }

        return $this->dbField;
    }

    /**
     * Database search operator
     *
     * @return string
     */
    public abstract function operator();

    /**
     * Value to search for
     *
     * @return string|\Carbon\Carbon|null
     */
    public abstract function value();

    public function searchOperation(Builder $query) {
        return $query->orWhere(
            $this->field(),
            $this->operator(),
            $this->value()
        );
    }
}
