<?php

namespace Aucos\LaravelSearch\Searcher;

class Like extends Searcher
{
    /**
     * Which conditions must be met -based on the search query
     * and database field name- in order to use this searcher
     *
     * @return bool
     */
    public function useMe()
    {
        if ($this->isDateFieldWithNonDateValue()) {
            return false;
        }

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

    /**
     * The DateFrom/DateTo Searcher are ignored if it can't parse the value.
     * This can happen if the Placeholder is submitted...
     * Without this check, the Like Searcher would become active, because
     * it is the last search to take place.
     *
     * @return bool
     */
    private function isDateFieldWithNonDateValue(): bool
    {
        return ends_with($this->field(), [DateFrom::suffix(), DateTo::suffix()]);
    }
}
