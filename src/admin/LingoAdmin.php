<?php

/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2016-10-18
 * Time: 15:22
 */
namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldPaginator;

class LingoAdmin extends ModelAdmin {
    private static $managed_models = array('NorthCreationAgency\SilverStripeLingo\Lingo');
    private static $url_segment = 'lingo';
    private static $table_name = 'LingoAdmin'; 

    //private static $menu_icon = 'vendor/lingo/client/images/icon-lingo.png';
    private static $model_importers = array();

    /**
     * @param null $id
     * @param null $fields
     * @return \SilverStripe\Forms\Form
     */
    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);
        if($gridField instanceof GridField) {
            $conf= $gridField->getConfig();
            $btnNew = $conf->getComponentByType(GridFieldAddNewButton::class);
            $conf->removeComponentsByType($btnNew);
            $items = $conf->getComponentByType(GridFieldPaginator::class);
            $items->setItemsPerPage(300);
        }

        return $form;
    }


}
