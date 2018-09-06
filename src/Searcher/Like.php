<?php

namespace Aucos\LaravelSearch\Searcher;

class Like extends Searcher
{
    /**
     * Which conditions must ne met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        return true;
    }

    /**
     * Database search operator
     *
     * @return string
     */
    public function operator()
    {
        if (starts_with($this->searchQuery, '!')) {
            return 'not like';
        }

        return 'like';
    }

    /**
     * Value to search for
     *
     * @return string
     */
    public function value()
    {
        $searchQuery = starts_with($this->searchQuery, '!')
            ? substr($this->searchQuery, 1)
            : $this->searchQuery;

        return starts_with($searchQuery, '%') || ends_with($searchQuery, '%')
            ? $searchQuery
            : "%{$searchQuery}%";
    }
}
