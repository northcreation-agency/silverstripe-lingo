<?php

/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2016-10-20
 * Time: 12:43
 */
namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;

class LingoDOExtension extends DataExtension {
    private static $buildLingo = true;
    private static $isLingoSync = null;

    public function requireDefaultRecords(){
       
        $isSync = $this->getIsLingoSync();
        //only run once per build
        if(self::$buildLingo && $isSync){
            $lingo = new LingoBuild();
                
            self::$buildLingo = false;
        }
    }

    private function getIsLingoSync():bool {
        if(self::$isLingoSync !== null ){
            return self::$isLingoSync;
        }

        $syncOnBuild = Config::inst()->get(Lingo::class, 'syncOnBuild');

        if($syncOnBuild == true){
            self::$isLingoSync = true;
            return self::$isLingoSync;
        }


        self::$isLingoSync = Controller::curr()->getRequest()->getVar('synclingo') !== null ? true : false;
        return self::$isLingoSync;

    }
}
