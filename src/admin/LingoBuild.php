<?php

/**
 * Called on dev/build from PageExtension to fetch all texts to translate and store them in DB
 *
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2016-10-19
 * Time: 15:30
 */
namespace NorthCreationAgency\SilverStripeLingo;

use DirectoryIterator;
use muskie9\DataToArrayList\ORM\DataToArrayListHelper;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\i18n\Data\Locales;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\View\ArrayData;
use Symfony\Component\Yaml\Yaml;
use NorthCreationAgency\SilverStripeLingo\Lingo;

class LingoBuild
{
    private static $yml_list_entities = null;

    private static $moduleCatalog = null;
    private static $textCatalog = null;

    private static $isBuilt = false;
    private static $status = false;

    const STATUS_BUILT = 'Built';
    const STATUS_CREATED = 'Created';
    const STATUS_ERROR = 'Error';


    public function __construct(){
        $this->init();
    }

    public static function get_lang_dir($module) {
        $modulePath = ModuleLoader::getModule($module)->getRelativePath();
        return Controller::join_links(Director::baseFolder(), $modulePath, self::$textCatalog);
    }

    public static function get_lang_file($module, $lang) {
        $file = self::get_lang_dir($module).DIRECTORY_SEPARATOR."{$lang}.yml";
//		if(!file_exists($file)) user_error("$file does not exist!");
//		if(!is_readable($file)) user_error("$file is not readable!");
        return $file;
    }

    public static function getLanguages() {
        $extensions = array('yml');
        $langs = new ArrayList();
        $dirPath =  self::get_lang_dir(self::$moduleCatalog);
        $directory = new DirectoryIterator($dirPath);
        $locales = new IntlLocales();

        foreach ($directory as $fileinfo) {
            // must be a file
            if ($fileinfo->isFile()) {
                // file extension
                $extension = strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION));
                // check if extension match
                if (in_array($extension, $extensions)) {
                    // add to result
                    $label = basename($fileinfo->getFilename(),".yml"); //remove file suffix
                    $langs->push(new ArrayData(array(
                        'Locale' => $label,
                        'Name' => $locales->languageName(self::get_lang_from_locale($label)),
                        //'Current' => $label == self::$currentLocale ? true : false
                    )));
                }
            }
        }

        return $langs;
    }
    
    /**
     * @param $locale
     * @return mixed
     */
    public static function get_lang_from_locale($locale) {
        $locales = new IntlLocales();
        if($str = $locales->langFromLocale($locale)) {
            if(stristr($str,"_") !== false) {
                $parts = explode("_", $str);
                return $parts[0];
            }
            return $str;
        }
        return $locale;
    }

    private function init(){

        //get config settings
        self::$moduleCatalog = Config::inst()->get(Lingo::class, 'moduleCatalog');
        self::$textCatalog = Config::inst()->get(Lingo::class, 'textCatalog');

        if( !(isset(self::$moduleCatalog) && isset(self::$textCatalog)) ){
            self::$status = self::STATUS_ERROR;
            return;
        }

        $languages = self::getLanguages();

        //create language specific texts in DB
        foreach ($languages as $lang){
            $this->createData($lang->Locale);
        }

    }

    private function createData($lang){
        //get current data from DB
        $lingoList = Lingo::get()->filter(array(
            'Locale' => $lang
        ));

        $lingoArrayList = DataToArrayListHelper::to_array_list($lingoList);

        //load data from file
        $ymlList = $this->loadTranslationData($lang);

        $lingoArrayList->merge($ymlList);
        $lingoArrayList->removeDuplicates('Entity');

        //save data from yml file to array for later access
        self::$yml_list_entities = $ymlList->map('Entity', 'Value');

        self::$status  = self::STATUS_BUILT;


        foreach ($lingoArrayList as $item) {
            if($item->ID){
                //= the item already exists in the database

                /* Find entries that can be removed from DB */
                //search for entries that exists in DB but not in the file == removed from file
                $isItemInYmlList = isset(self::$yml_list_entities[$item->Entity]);

                if($isItemInYmlList){
                    //= the database item does exist in the yml-list

                    //check if it is marked as obsolete in DB.. and undo it if thats the case
                    if($item->Status == Lingo::STATUS_OBSOLETE){
                        $item->Status = Lingo::STATUS_ACTIVE;
                        $item->write();
                    }
                }
                else{
                    //= the database item does not exist in the yml-list, mark it as removable from db
                    $item->Status = Lingo::STATUS_OBSOLETE;
                    $item->write();
                }

                /* Find entries that have changed text in yml-file but not in DB
                 * and can be updated with the new value from the yml-file */
                if($isItemInYmlList){
                    //= the database item does exist in the yml-list

                    $ymlValue = self::$yml_list_entities[$item->Entity];

                    //test if the value in the yml file has changed since the object was created
                    if(strcmp($item->FileValue, $ymlValue) != 0){
                        //the values are not the same

                        //test if user has updated the value in DB
                        if(strcmp($item->Value, $item->FileValue) == 0){
                            //the value and file value is the same, update
                            $item->Value = $ymlValue;
                            $item->FileValue = $ymlValue;
                            $item->write();
                        }
                        else{ //update only File value, ie the value from the Yml file
                            $item->FileValue = $ymlValue;
                            $item->write();
                        }
                    }
                }



            }
            else{
                //= the item does not exist in the db, create it

                $lingo = new Lingo();
                $lingo->Name = $item->Name;
                $lingo->Familyname = $item->Familyname;
                $lingo->Value = $item->Value;
                $lingo->FileValue = $item->Value;
                $lingo->Entity = $item->Entity;
                $lingo->Locale = $item->Locale;
                $lingo->write();
                self::$status  = self::STATUS_CREATED;
            }
        }
    }

    private function loadTranslationData($lang) {
        $lang_file = self::get_lang_file(self::$moduleCatalog, $lang);

        $temp_lang = Yaml::parseFile($lang_file);

        $entities = new ArrayList();

        if(is_array($temp_lang) && isset($temp_lang[$lang])) {
            foreach($temp_lang[$lang] as $familyname => $array_of_entities) {

                if(is_array($array_of_entities)) {
                    foreach($array_of_entities as $name => $value) {
                        if (is_array($value)) {
                            $value = $value[0];
                        }
                        $entities->push(new ArrayData(array(
                            'Familyname' => $familyname,
                            'Name' => $name,
                            'Value' => stripslashes($value),
                            'Entity' => $familyname . '.' . $name,
                            'Locale' => $lang
                        )));
                    }
                }
            }
        }

        return $entities;
    }

    public function getBuildStatus(){
        switch (self::$status) {
            case self::STATUS_ERROR:
                return DB::alteration_message(_t('LingoBuild.StatusErrorConfig', 'Lingo config error'), 'error');
            case self::STATUS_CREATED:
               return DB::alteration_message(_t('LingoBuild.StatusCreated', 'Lingo texts read and saved to DB'), 'created');
            default:
                return DB::alteration_message(self::class, '');
        }
    }



}
