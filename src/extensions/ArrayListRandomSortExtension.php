<?php

namespace muskie9\DataToArrayList\Extension;
    
use SilverStripe\Core\Extension;

/**
 * Class ArrayListRandomSortExtension
 */
class ArrayListRandomSortExtension extends Extension
{

    /**
     * @param string $columnName
     * @return Object
     */
    public function applyRandomSortColumn($columnName = 'RandomSort')
    {
        $applyRandomSortColumn = function ($item) use ($columnName) {
            if (!$item->$columnName) {
                $item->$columnName = mt_rand();
            }
        };
        foreach ($this->owner->items as $item) {
            $applyRandomSortColumn($item);
        }
        return $this->owner;
    }

}
