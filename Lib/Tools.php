<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */

namespace SysEleven\PowerDnsBundle\Lib;

 
/**
 * Several tools used in the app.
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */ 
class Tools 
{

    /**
     * converts a given symfony violation list to an array containing the keys
     * and the messages
     *
     * @param $errors
     *
     * @return array
     */
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
