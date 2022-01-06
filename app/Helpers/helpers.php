<?php

namespace App\Helpers;

use App\Services\System;
use Carbon\Carbon;

function autover($resource)
{
    $time = filemtime(public_path() . $resource);
    $dot = strrpos($resource, '.');
    return asset(substr($resource, 0, $dot) . '.' . $time . substr($resource, $dot));
}

function array_search_key_recursive($key, $array, $parents = false, $skip = null)
{
    if (isset($array[$key])) {
        return ($parents ? [$key] : $array[$key]);
    } else {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return = array_search_key_recursive($key, $v, $parents, $skip);
                if ($return) {
                    if ($parents && $k != $skip) {
                        $return[] = $k;
                    }
                    return $return;
                }
            }
        }
    }
    return false;
}

function array_search_value_recursive($value, $array, $parents = false)
{
    if ($key = array_search($value, $array)) {
        return ($parents ? [$key] : $array[$key]);
    } else {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return = array_search_value_recursive($value, $v, $parents);
                if ($return) {
                    if ($parents) {
                        $return[] = $k;
                    }
                    return $return;
                }
            }
        }
    }
    return false;
}

function multiKsort(&$array)
{
    ksort($array);
    foreach (array_keys($array) as $k) {
        if (is_array($array[$k])) {
            multiKsort($array[$k]);
        }
    }
}

function arrayToTree($array, $parent = null)
{
    $array = array_combine(array_column($array, 'id'), array_values($array));
    foreach ($array as $k => &$v) {
        if (isset($array[$v['parent']])) {
            $array[$v['parent']]['children'][$k] = &$v;
        }
        unset($v);
    }
    return array_filter($array, function($v) use ($parent) {
        return $v['parent'] == $parent;
    });
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function displayWindowsDate($date, $charset = 'WINDOWS-1251') {
    if (System::getOS() == 2) { // && \Locales::getCurrent() == 'bg'
        return iconv($charset, 'utf-8', $date);
    } else {
        return $date;
    }
}

function randomStr($length = 6, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*-=+;:?/,.')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}

function localizedDate($date = null, $format = '%d.%m.%Y', $charset = 'WINDOWS-1251') {
    return displayWindowsDate(Carbon::parse($date ?: Carbon::now(), 'Europe/Sofia')->formatLocalized($format), $charset);
}
