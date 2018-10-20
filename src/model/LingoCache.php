<?php
/**
 * Created by PhpStorm.
 * User: emilberg
 * Date: 2018-10-20
 * Time: 08:04
 */

namespace NorthCreationAgency\SilverStripeLingo;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;

class LingoCache
{

    public static function get_cache(){
        return Injector::inst()->get(CacheInterface::class . '.LingoCache');
    }

    public static function get_value($cacheKey){
        $cache = self::get_cache();
        return $cache->get($cacheKey);
    }

    public static function set_value($cacheKey, $value){
        $cache = self::get_cache();
        $cache->set($cacheKey, $value);
    }

    public static function has_value($cacheKey){
        $cache = self::get_cache();
        return $cache->has($cacheKey);
    }

    /**
     * Generates a cachekey with the given parameters
     *
     * @param $entity
     * @param $locale
     * @return string
     */
    public static function get_cache_key($entity, $locale)
    {
        return md5(serialize($entity)) . '#' . md5(serialize($locale));
    }
}