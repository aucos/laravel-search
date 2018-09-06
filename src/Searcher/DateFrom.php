<?php

namespace Aucos\LaravelSearch\Searcher;

use Carbon\Carbon;

class DateFrom extends Searcher
{
    /**
     * Which conditions must ne met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        return ends_with($this->dbField, static::suffix()) && $this->value();
    }

    /**
     * Here a suffix will be set, if the database field name has one
     *
     * @return string
     */
    public static function suffix()
    {
        return '__from';
    }

    /**
     * Database search operator
     *
     * @return string
     */
    public function operator()
    {
        return '>=';
    }

    /**
     * Value to search for
     *
     * @return Carbon
     */
    public function value()
    {
        try {
            $fromDateTime = Carbon::parse($this->searchQuery);
            return $fromDateTime->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}
