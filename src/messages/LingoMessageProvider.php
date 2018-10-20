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
    private static $intl_locale;

    /**
     * @param $entity
     * @return mixed|null
     */
    private function getLingoValue($entity, $locale)
    {
        $cacheKey = LingoCache::get_cache_key($entity, $locale);

        //see if entity is in cache
        if(LingoCache::has_value($cacheKey)){
            return LingoCache::get_value($cacheKey);
        }

        if(self::$intl_locale === null){
            self::$intl_locale = new IntlLocales();
        }

        $lang = self::$intl_locale->langFromLocale($locale);
        $lingo = Lingo::get()->filter(array(
            'Locale' => $lang,
            'Entity' => $entity
        ))->first();

        if(!$lingo){
            return null;
        }

        LingoCache::set_value($cacheKey, $lingo->Value);

        return $lingo->Value;

    }

    public function translate($entity, $default, $injection)
    {
        // Ensure localisation is ready
        $locale = i18n::get_locale();
        $this->load($locale);

        // Prepare arguments
        $arguments = $this->templateInjection($injection);

        // Pass to symfony translator
        $result = $this->getTranslator()->trans($entity, $arguments, 'messages', $locale);

        //See if we have a Lingo translation if none is found
        if ($entity === $result) {
            $result = $this->getLingoValue($entity, $locale);

            if($result){
                return $result;
            }

            // else Manually inject default if no translation found
            $result = $this->getTranslator()->trans($default, $arguments, 'messages', $locale);
        }

        return $result;
    }

}