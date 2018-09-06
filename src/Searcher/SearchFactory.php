<?php

namespace Aucos\LaravelSearch\Searcher;

class SearchFactory
{
    /**
     * Based on a search query and the database field, this
     * basic factory returns the correct searcher class in
     * order to get the correct field, operator and value
     *
     * @param string $searchQuery
     * @param string $dbField
     *
     * @return Searcher
     */
    public static function build($searchQuery, $dbField)
    {
        $availableSearcher = [
            new IsNull($searchQuery, $dbField),
            new GroupedCount($searchQuery, $dbField), // must stand before compare
            new Compare($searchQuery, $dbField),
            new DateFrom($searchQuery, $dbField),
            new DateTo($searchQuery, $dbField),
        ];

        foreach ($availableSearcher as $searcher) {
            if ($searcher->useMe()) {
                return $searcher;
            }
        }

        return new Like($searchQuery, $dbField);
    }


    public static function isOuterWhere($dbField)
    {
        return ends_with($dbField, '__raw');
    }

    public static function isInnerWhere($dbField)
    {
        return !static::isOuterWhere($dbField);
    }
}
