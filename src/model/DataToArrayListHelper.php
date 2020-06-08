<?php

namespace muskie9\DataToArrayList\ORM;

use SilverStripe\ORM\DataList,
    SilverStripe\ORM\ArrayList;

/**
 * Class DataToArrayListHelper
 */
class DataToArrayListHelper
{

    /**
     * This method converts a {@link DataList} to an {@link ArrayList} with an option to add
     * an additional column to each list item to be used for sorting. This is particularly useful when you are
     * trying to group or sort a list by a value on a somehow related object, or not able to query from the database.
     *
     * @param DataList $list
     * @param bool $additionalSortColumn If you have MyObject with a has_one relation to OtherObject
     *                                      which then has a has_one to ThirdObject,
     *                                      you would pass the following for column: 'OtherObject.ThirdObject.FieldName
     *                                      where FieldName is on the ThirdObject
     * @return ArrayList
     */
    public static function to_array_list(DataList $list, $additionalSortColumn = false)
    {
        $arrayList = ArrayList::create();

        $push = function ($item) use (&$arrayList, &$additionalSortColumn) {
            if ($additionalSortColumn) {
                $item = self::additional_sort_column($item, $additionalSortColumn);
            }
            $arrayList->push($item);
        };

        $list->each($push);

        return $arrayList;
    }

    /**
     * This method helps traverse `$has_one` relations to allow for sorting or grouping by the related
     * object's data.
     * I.E. If you have MyObject with a has_one relation to OtherObject which then has a has_one to ThirdObject,
     * you would pass the following for column: 'OtherObject.ThirdObject.FieldName` where FieldName is on the ThirdObject
     *
     * @param $item
     * @param $column
     * @return mixed
     */
    protected static function additional_sort_column($item, $column)
    {
        $parts = preg_split('/\./', $column, 0, PREG_SPLIT_NO_EMPTY);
        if (count($parts) > 1) {
            foreach ($parts as $key => $part) {
                if (!isset($related)) {
                    if ($item->getRelationClass($part)) {
                        $related = $item->$part();
                    }
                } else {
                    if ($related->getRelationClass($part)) {
                        $related = $related->$part();
                    } else {
                        $item->$part = $related->$part;
                    }
                }
            }
        }
        return $item;
    }

}
