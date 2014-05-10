<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
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