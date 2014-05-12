<?php
/**
 * SysEleven SMAPI 2 Project
 *
 * @author     Markus Seifert <m.seifert@syseleven.de>
 * @package    package
 * @subpackage subpackage
 */

namespace SysEleven\PowerDnsBundle\Lib;

 
/**
 * Tools
 *
 * @author     Markus Seifert <m.seifert@syseleven.de>
 * @package    package
 * @subpackage subpackage
 */ 
class Tools 
{
    /**
     * Checks if the first char of the given string is one of > < ! and return
     * the sql operator for it.
     *
     * @param string $str
     * @return string
     */
    public static function getOperatorFromString($str)
    {
        if(!is_string($str) || is_null($str) || 0 == strlen($str)) {
            return '=';
        }

        $fc = substr($str, 0, 1);
        $op = '=';
        if (in_array($fc, array('!','>','<'))) {
            switch ($fc) {
                case '!':
                    $op = '<>';
                    break;
                case '>':
                    $op = '>=';
                    break;
                case '<':
                    $op = '<=';
                    break;
            }
        }

        return $op;
    }

    /**
     * Strips the first char from the string if it is in [<, >, !]
     *
     * @param $str
     * @return mixed
     */
    public static function stripOperatorFromString($str)
    {
        if (!is_string($str) || is_null($str) || 0 == strlen($str)) {
            return $str;
        }

        $fc = substr($str, 0, 1);
        if (in_array($fc, array('!','>','<'))) {
            $str = substr_replace($str, '', 0, 1);
        }

        return $str;
    }


    public static function prepareSymfonyErrorArray($errors)
    {
        $r = array();
        /**
         * @type \Symfony\Component\Validator\ConstraintViolation $v
         */
        foreach($errors AS $v) {
            $r[$v->getPropertyPath()] = $v->getMessage();
        }

        return $r;
    }
}
