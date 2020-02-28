<?php

namespace Aucos\LaravelSearch\Searcher;

use Illuminate\Support\Str;

class Money extends Searcher
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
        return '__money';
    }

    /**
     * Database search operator
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function operator()
    {
        if (Str::startsWith($this->searchQuery, ['>=', '<=', '!='])) {
            return substr($this->searchQuery, 0, 2);
        }

        if (Str::startsWith($this->searchQuery, ['>', '<', '='])) {
            return substr($this->searchQuery, 0, 1);
        }

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
        if (Str::startsWith($this->searchQuery, ['>=', '<=', '!='])) {
            return intval(substr($this->searchQuery, 2) * 100);
        }

        if (Str::startsWith($this->searchQuery, ['>', '<', '='])) {
            return intval(substr($this->searchQuery, 1) * 100);
        }

        return intval($this->searchQuery) * 100;
    }
}
