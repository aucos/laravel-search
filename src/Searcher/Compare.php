<?php

namespace Aucos\LaravelSearch\Searcher;

class Compare extends Searcher
{
    /**
     * Which conditions must ne met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        return starts_with($this->searchQuery, ['>=', '<=', '!=', '>', '<', '=']);
    }

    /**
     * Database search operator
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function operator()
    {
        if (starts_with($this->searchQuery, ['>=', '<=', '!='])) {
            return substr($this->searchQuery, 0, 2);
        }

        if (starts_with($this->searchQuery, ['>', '<', '='])) {
            return substr($this->searchQuery, 0, 1);
        }

        throw new \InvalidArgumentException('searchQuery must be >=, <=, !=, >, < or =');
    }

    /**
     * Value to search for
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function value()
    {
        if (starts_with($this->searchQuery, ['>=', '<=', '!='])) {
            return substr($this->searchQuery, 2);
        }

        if (starts_with($this->searchQuery, ['>', '<', '='])) {
            return substr($this->searchQuery, 1);
        }

        throw new \InvalidArgumentException('searchQuery must be >=, <=, !=, >, < or =');
    }
}
