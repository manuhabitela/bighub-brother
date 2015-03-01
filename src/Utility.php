<?php

namespace BigHubBrother;

class Utility
{
    /** https://github.com/cakephp/cakephp/blob/master/lib/Cake/Utility/Hash.php
     *
     * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
     * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     *
     * Licensed under The MIT License
     *
     * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     * @link          http://cakephp.org CakePHP(tm) Project
     * @package       Cake.Utility
     * @since         CakePHP(tm) v 2.2.0
     * @license       http://www.opensource.org/licenses/mit-license.php MIT License
     **/
    public static function merge(array $data, $merge) {
        $args = array_slice(func_get_args(), 1);
        $return = $data;
        foreach ($args as &$curArg) {
            $stack[] = array((array)$curArg, &$return);
        }
        unset($curArg);
        while (!empty($stack)) {
            foreach ($stack as $curKey => &$curMerge) {
                foreach ($curMerge[0] as $key => &$val) {
                    if (!empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                        $stack[] = array(&$val, &$curMerge[1][$key]);
                    } elseif ((int)$key === $key && isset($curMerge[1][$key])) {
                        $curMerge[1][] = $val;
                    } else {
                        $curMerge[1][$key] = $val;
                    }
                }
                unset($stack[$curKey]);
            }
            unset($curMerge);
        }
        return $return;
    }

    protected static function _pickOrWithout($pick = true, array $data, array $list)
    {
        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $list) === $pick) {
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

    public static function pick(array $data, array $whitelist)
    {
        return self::_pickOrWithout(true, $data, $whitelist);
    }

    public static function without(array $data, array $blacklist)
    {
        return self::_pickOrWithout(false, $data, $blacklist);
    }
}