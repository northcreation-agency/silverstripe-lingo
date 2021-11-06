<?php
/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2018-10-15
 * Time: 21:22
 */

namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\i18n\i18n;
use SilverStripe\Security\Security;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Permission;
use SilverStripe\Admin\AdminRootController;
use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\i18n\Messages\Symfony\SymfonyMessageProvider;

class LingoMessageProvider extends SymfonyMessageProvider
{
    static $BUILD_URL = "dev/build";
    static $TASKS_URL = "dev/tasks";

    private function getLingoTranslator(){
        return new LingoTranslator();
    }

    private static function getAdminUrl(){
        return AdminRootController::admin_url();
    }

    public function translate($entity, $default, $injection)
    {
        $url = $_SERVER['REQUEST_URI'];

        $userInCMS = str_contains($url, static::getAdminUrl()) || str_contains($url, static::$BUILD_URL) || str_contains($url, static::$TASKS_URL);

        //call symfony "original" function if in admin mode
        if($userInCMS){
            return parent::translate($entity, $default, $injection);
        }

        // Ensure localisation is ready
        $locale = i18n::get_locale();
        $this->load($locale);

        // Prepare arguments
        $arguments = $this->templateInjection($injection);

        // Pass to symfony translator
        $result = $this->getTranslator()->trans($entity, $arguments, 'messages', $locale);

        //See if we have a Lingo translation if none is found
        if ($entity === $result) {
            //$result = $this->getLingoValue($entity, $locale);

            $result = $this->getLingoTranslator()->trans($entity, $arguments, $locale);

            if($result){
                return $result;
            }

            // else Manually inject default if no translation found
            $result = $this->getTranslator()->trans($default, $arguments, 'messages', $locale);
        }

        return $result;
    }

}