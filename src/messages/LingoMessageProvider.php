<?php
/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2018-10-15
 * Time: 21:22
 */

namespace NorthCreationAgency\SilverStripeLingo;

use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\i18n\i18n;
use SilverStripe\i18n\Messages\Symfony\SymfonyMessageProvider;

class LingoMessageProvider extends SymfonyMessageProvider
{
    private static $is_build;
    private static $intl_locale;

    /**
     * @param $entity
     * @return mixed|null
     */
    private function getLingoValue($entity)
    {
        if(self::$is_build === null){
            self::$is_build = LingoBuild::is_dev_build();
        }

        if(self::$is_build){
            return null;
        }

        // get current locale
        if(self::$intl_locale === null){
            self::$intl_locale = new IntlLocales();
        }

        $locale = i18n::get_locale();
        $lang = self::$intl_locale->langFromLocale($locale);
        $lingo = Lingo::get()->filter(array(
            'Locale' => $lang,
            'Entity' => $entity
        ))->first();
        return $lingo ? $lingo->Value : null;

    }

    public function translate($entity, $default, $injection){

        $lingo = $this->getLingoValue($entity);

        if($lingo){
            return $lingo;
        }

        return parent::translate($entity, $default, $injection);
    }

}