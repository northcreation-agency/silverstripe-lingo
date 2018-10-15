<?php
/**
 * Lingo. Lingo item.
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2016-10-18
 * Time: 15:01
 */
namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\ORM\GroupedList;
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\DropdownField;

class Lingo extends DataObject {

    private static $instance = null;


    const STATUS_ACTIVE = 'Active';
    const STATUS_OBSOLETE = 'Obsolete';

    private static $db = array(
        'Name'      => 'Varchar(255)',
        'Familyname' => 'Varchar(255)',
        'Value'	=> 'Text',
        'OriginalValue'	=> 'Text',
        'Entity' => 'Varchar(255)',
        'Locale' => 'Varchar(10)',
        'Status' => "Enum('Active,Obsolete', 'Active')",
    );

    private static $summary_fields = array(
        'Entity',
        'Value',
        'Familyname',
        'Name',
        'Modified',
        'Locale'
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
        $fields->removeByName('OriginalValue');

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('Entity', _t('Lingo.Entity','Entity')));
        //$fields->addFieldToTab('Root.Main', ReadonlyField::create('Name', _t('Lingo.Name','Name')));
        $fields->addFieldToTab('Root.Main',  TextareaField::create('Value', _t('Lingo.Value','Value'))->setRows(3));
        $fields->addFieldToTab('Root.Main', ReadonlyField::create('Locale', _t('Lingo.Locale','Locale')));


        if($this->isModified()){
            $fields->addFieldToTab('Root.Main', ReadonlyField::create('OriginalValue', _t('Lingo.OriginalValue', 'Original value'))->addExtraClass('muted'));
            $fields->addFieldToTab('Root.Main', ReadonlyField::create('LastEdited', _t('Lingo.Modified', 'Modified'))->addExtraClass('muted'));
        }

        return $fields;
    }

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['Familyname'] = _t('Lingo.Familyname', 'Familyname');
        $labels['Name'] =  _t('Lingo.Name', 'Name');
        $labels['Value'] =  _t('Lingo.Value', 'Value');
        $labels['Modified'] =  _t('Lingo.Modified', 'Modified');
        $labels['Locale'] =  _t('Lingo.Locale', 'Locale');
        $labels['Entity'] =  _t('Lingo.Entity', 'Entity');

        return $labels;
    }


    public function getDefaultSearchContext(){
        $fields = $this->scaffoldSearchFields(array(
            'restrictFields' => array('Name', 'Value', 'Familyname', 'Locale')
        ));

        //TODO: Use "get()->distinct()" instead. But couldnt get it to work...
        //Tried: Lingo::get()->distinct(true)->setQueriedColumns(array('Familyname'))
        $source = GroupedList::create(Lingo::get())->GroupedBy('Familyname')->map('Familyname','Familyname');
        $fields->dataFieldByName('Familyname')->setSource($source);
        $fields->dataFieldByName('Familyname')->setEmptyString('');

        $locsource = GroupedList::create(Lingo::get())->GroupedBy('Locale')->map('Locale','Locale');
        $fields->dataFieldByName('Locale')->setSource($locsource);
        $fields->dataFieldByName('Locale')->setEmptyString('');

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

    public function isModified(){
        return strcmp($this->Value, $this->OriginalValue) !== 0;
    }

    public function getModified(){
        return $this->isModified() ?  _t('Lingo.IsEdited', 'Yes') :  _t('Lingo.NotEdited', ' ');
    }

    /**
     * Helper function to use in cases where translation is needed but no inheritance from DataObject exists.
     * Ex: Lingo::instance()->tL('Example.Text', 'Fallback text')
     * @return Lingo
     */
    public static function instance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;

    }
}
