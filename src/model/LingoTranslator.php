<?php
/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2018-10-27
 * Time: 16:16
 */

namespace NorthCreationAgency\SilverStripeLingo;


use SilverStripe\i18n\Data\Intl\IntlLocales;

class LingoTranslator
{

    private static $intl_locale;

    /**
     * @param $entity
     * @return mixed|null
     */
    private function getLingoValue($localeOrLang, $entity)
    {
        $cacheKey = LingoCache::get_cache_key($entity, $localeOrLang);

        //see if entity is in cache
        if(LingoCache::has_value($cacheKey)){
            return LingoCache::get_value($cacheKey);
        }

        $lingo = Lingo::get()->filter(array(
            'Locale' => $localeOrLang,
            'Entity' => $entity
        ))->first();

        if(!$lingo){
            return null;
        }

        LingoCache::set_value($cacheKey, $lingo->Value);

        return $lingo->Value;

    }

    public function trans($id, array $parameters = array(), $locale = null)
    {
        if(self::$intl_locale === null){
            self::$intl_locale = new IntlLocales();
        }

        //get lang
        $lang = self::$intl_locale->langFromLocale($locale);
        //see if we got a value for lingo with lang value as "locale"
        $value = $this->getLingoValue($lang, $id);

        //see if we got a value thats defined with full locale
        if(!$value){
            $value = $this->getLingoValue($locale, $id);
        }

        return strtr($value, $parameters);
    }

}