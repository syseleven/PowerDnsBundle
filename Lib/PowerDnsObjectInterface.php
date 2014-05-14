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
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */
interface PowerDnsObjectInterface 
{
    /**
     * Returns the value of the loose_check flag to the object
     *
     * @return mixed
     */
    public function looseCheck();

    /**
     * Returns the id of the record
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the username to the object
     *
     * @param $username
     *
     * @return mixed
     */
    public function setUser($username);
}