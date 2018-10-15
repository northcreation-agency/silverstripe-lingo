<?php

/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2016-10-20
 * Time: 12:43
 */
namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\ORM\DataExtension;

class LingoPageExtension extends DataExtension {
    private static $buildLingo = true;

    public function requireDefaultRecords(){
        //only run once per build
        if(self::$buildLingo){
            $lingo = new LingoBuild();
            $lingo->getBuildStatus();
            self::$buildLingo = false;
        }
    }
}
