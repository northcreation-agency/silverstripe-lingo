<?php

use NorthCreationAgency\SilverStripeLingo\LingoBuild;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\i18n\Data\Locales;
use SilverStripe\i18n\i18n;

class SyncLingoTask extends BuildTask
{
    public function run($request)
    {

        $lingo = new LingoBuild();
        echo _t('LingoBuild.StatusCreated', 'Lingo texts read and saved to DB');
    
    }

}