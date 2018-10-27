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
    private function getLingoTranslator(){
        return new LingoTranslator();
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