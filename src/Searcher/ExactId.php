<?php

namespace Aucos\LaravelSearch\Searcher;

use Illuminate\Support\Str;

class ExactId extends Searcher
{
    /**
     * Which conditions must ne met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        return Str::endsWith($this->dbField, '_id');
    }

    /**
     * Database search operator
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function operator()
    {
        return '=';
    }

    /**
     * Value to search for
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function value()
    {
        return $this->searchQuery;
    }
}
