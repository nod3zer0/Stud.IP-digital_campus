<?php

## Copyright (c) 2011 Elmar Ludwig, University of Osnabrueck
##
## PHPLIB Data Storage Container using Stud.IP cache
## for use with Stud.IP and cache only!

class CT_Cache
{
    protected const CACHE_KEY_PREFIX = 'session_data';
    protected const SESSION_LIFETIME = 7200;

    private $cache;

    public function ac_start()
    {
        $this->cache = StudipCacheFactory::getCache();
    }

    public function ac_get_lock()
    {
    }

    public function ac_release_lock()
    {
    }

    public function ac_newid($str, $name = null)
    {
        return $this->ac_get_value($str) === false ? $str : false;
    }

    public function ac_store($id, $name, $str)
    {
        $cache_key = self::CACHE_KEY_PREFIX . '/' . $id;
        return $this->cache->write($cache_key, $str, ini_get('session.gc_maxlifetime') ?: self::SESSION_LIFETIME);
    }

    public function ac_delete($id, $name = null)
    {
        $cache_key = self::CACHE_KEY_PREFIX . '/' . $id;
        $this->cache->expire($cache_key);
    }

    public function ac_gc($gc_time, $name = null)
    {
    }

    public function ac_halt($s)
    {
        echo "<b>$s</b>";
        exit;
    }

    public function ac_get_value($id, $name = null)
    {
        $cache_key = self::CACHE_KEY_PREFIX . '/' . $id;
        return $this->cache->read($cache_key);
    }

    public function ac_get_changed($id, $name = null)
    {
    }

    public function ac_set_changed($id, $name, $timestamp)
    {
    }
}
