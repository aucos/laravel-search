<?php

namespace Aucos\LaravelSearch\Searcher;

use Illuminate\Support\Str;

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
        if (Str::startsWith($this->searchQuery, '!')) {
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
        $searchQuery = Str::startsWith($this->searchQuery, '!')
            ? substr($this->searchQuery, 1)
            : $this->searchQuery;

        return Str::startsWith($searchQuery, '%') || Str::endsWith($searchQuery, '%')
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
        return Str::endsWith($this->field(), [DateFrom::suffix(), DateTo::suffix()]);
    }
}
