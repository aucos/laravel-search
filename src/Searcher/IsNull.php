<?php

namespace Aucos\LaravelSearch\Searcher;

class IsNull extends Searcher
{
    /**
     * Which conditions must be met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        return trim($this->searchQuery) === '-';
    }

    /**
     * Database search operator
     *
     * @return string
     */
    public function operator()
    {
        return '=';
    }

    /**
     * Value to search for
     *
     * @return null
     */
    public function value()
    {
        return null;
    }
}
