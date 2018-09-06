<?php

namespace Aucos\LaravelSearch\Searcher;

use Carbon\Carbon;

class DateTo extends Searcher
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
        return '__to';
    }

    /**
     * Database search operator
     *
     * @return string
     */
    public function operator()
    {
        return '<=';
    }

    /**
     * Value to search for
     *
     * @return DateTo|Carbon
     */
    public function value()
    {
        try {
            $toDateTime = Carbon::parse($this->searchQuery);
            return $toDateTime->endOfDay();
        } catch (\Exception $e) {
            dump($e);
            return null;
        }
    }
}
