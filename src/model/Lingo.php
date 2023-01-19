<?php
/**
 * Lingo. Lingo item.
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2016-10-18
 * Time: 15:01
 */
namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\Core\Flushable;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\GroupedList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use NorthCreationAgency\SilverStripeLingo\LingoCache;

class Lingo extends DataObject implements Flushable{

    private static $instance = null;


    const STATUS_ACTIVE = 'Active';
    const STATUS_OBSOLETE = 'Obsolete';

    private static $db = array(
        'Name'      => 'Varchar(255)',
        'Familyname' => 'Varchar(255)',
        'Value'	=> 'Text',
        'FileValue'	=> 'Text',
        'Entity' => 'Varchar(255)',
        'Locale' => 'Varchar(10)',
        'Status' => "Enum('Active,Obsolete', 'Active')",
    );

    private static $summary_fields = array(
        'Entity',
        'Value',
        'FileValueModified',
        'Locale',
        'Status'
    );

    private static $default_sort = 'Familyname ASC';

    private static $table_name = 'Lingo';

    private static $searchable_fields = array(
        'Familyname' => array(
            'field' => DropdownField::class,
            'filter' => PartialMatchFilter::class),
        'Name' => array(
            'filter' => PartialMatchFilter::class),
        'Value' => array(
            'filter' => PartialMatchFilter::class),
        'Locale' => array(
            'field' => DropdownField::class,
            'filter' => PartialMatchFilter::class)
    );


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        //remove fields we dont want to show in admin
        //$fields->removeByName('KeyID');
        //$fields->removeByName('Locale');
        $fields->removeByName('Familyname');
        $fields->removeByName('Name');
        $fields->removeByName('Status');
        $fields->removeByName('FileValue');

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('Entity', _t('Lingo.Entity','Entity')));
        $fields->addFieldToTab('Root.Main',  TextareaField::create('Value', _t('Lingo.Value','Value'))->setRows(3));
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('Locale', _t('Lingo.Locale','Locale')));


        if($this->isModified()){
            $fields->addFieldToTab('Root.Main', ReadonlyField::create('FileValue', _t('Lingo.FileValue', 'File value')));
            $fields->addFieldToTab('Root.Main', ReadonlyField::create('LastEdited', _t('Lingo.Modified', 'Modified')));
        }

        if($this->canDelete()){
            $fields->addFieldToTab('Root.Main',
                ReadonlyField::create('Status', _t('Lingo.Status', 'Status'))
                    ->setRightTitle(_t('Lingo.InfoObsolete', 'The entity can be deleted')));

        }

        return $fields;
    }

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        //$labels['Familyname'] = _t('Lingo.Familyname', 'Familyname');
        //$labels['Name'] =  _t('Lingo.Name', 'Name');
        $labels['Value'] =  _t('Lingo.Value', 'Value');
        $labels['Entity'] =  _t('Lingo.Entity', 'Entity');
        $labels['FileValueModified'] =  _t('Lingo.FileValueModified', 'File value');
        $labels['Locale'] =  _t('Lingo.Locale', 'Locale');
        $labels['Modified'] =  _t('Lingo.Modified', 'Modified');


        return $labels;
    }


    public function getDefaultSearchContext(){
        $fields = $this->scaffoldSearchFields(array(
            'restrictFields' => array('Name', 'Value', 'Familyname', 'Locale')
        ));

        $source = GroupedList::create(Lingo::get())->GroupedBy('Familyname')->map('Familyname','Familyname');
        $fields->dataFieldByName('Familyname')->setSource($source);
        $fields->dataFieldByName('Familyname')->setEmptyString(_t('Lingo.Select', 'Select'));

        $locsource = GroupedList::create(Lingo::get())->GroupedBy('Locale')->map('Locale','Locale');
        $fields->dataFieldByName('Locale')->setSource($locsource);
        $fields->dataFieldByName('Locale')->setEmptyString(_t('Lingo.Select', 'Select'));

        $fields->push(TextField::create('Value', 'Value'));

        $filters = array(
            'Locale' => new PartialMatchFilter('Locale'),
            'Familyname' => new PartialMatchFilter('Familyname'),
            'Name' => new PartialMatchFilter('Name'),
            'Value' => new PartialMatchFilter('Value')
        );
        return new SearchContext(
            Lingo::class,
            $fields,
            $filters
        );
    }

    public function canDelete($member = null) {
        //an Admin can delete obsolete items
        return Permission::check('ADMIN') && $this->Status == self::STATUS_OBSOLETE;
    }

    public function getTitle(){
        return $this->Name;
    }

   /*public function getEdited(){
        return $this->LastEdited > $this->Created ?  _t('Lingo.IsEdited', 'Editerad') :  _t('Lingo.NotEdited', '');
    }*/

    public static function flush()
    {
        LingoCache::clear();
    }

    public function isModified(){
        if($this->Value === null){
            return true;
        }
        
        return strcmp($this->Value, $this->FileValue) !== 0;
    }

    public function getModified(){
        return $this->isModified() ?  _t('Lingo.IsEdited', 'Yes') :  _t('Lingo.NotEdited', ' ');
    }

    public function getFileValueModified(){
        return $this->isModified() ? $this->FileValue : ' ';
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        /*$cacheKey = LingoCache::get_cache_key($this->Entity, $this->Locale);
        if(LingoCache::has_value($cacheKey)){
            return LingoCache::delete_value($cacheKey);
        }*/
        //TODO: Find out why the above code to clear only the items cache is not working
        //For now, clear the whole Lingo cache when a value changes/ is written
        LingoCache::clear();
    }
}
