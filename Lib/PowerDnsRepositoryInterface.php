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
interface PowerDnsRepositoryInterface 
{

    /**
     * @param array $filter
     * @param array $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createSearchQuery(array $filter = array(), array $order = array());

}