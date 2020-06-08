<?php

namespace NorthCreationAgency\SilverStripeLingo;
    
use SilverStripe\ORM\DataExtension;

/**
 * Class ArrayListRandomSortExtension
 */
class ArrayListRandomSortExtension extends DataExtension
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
