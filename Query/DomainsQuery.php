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
 * @package SysEleven\PowerDnsBundle\Query
 */
namespace SysEleven\PowerDnsBundle\Query;


use SysEleven\PowerDnsBundle\Lib\QueryAbstract;

/**
 * Query class for domain searches, provides the field definition for the domain
 * search form
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Query
 */
class DomainsQuery extends QueryAbstract
{
    /**
     * Performs a like search within the backend
     *
     * @var string
     */
    public $search = '';

    /**
     * Filters by domain name
     *
     * @var string
     */
    public $name = '';

    /**
     * Filters by domain type (NATIVE,MASTER,SLAVE,SUPERMASTER)
     *
     * @var array
     */
    public $type = array();

    /**
     * Filters by domain id
     *
     * @var array
     */
    public $id = array();

    /**
     * Filters by master
     *
     * @var string
     */
    public $master = '';

    /**
     * Filters by account
     *
     * @var string
     */
    public $account = '';

    /**
     *
     * @param string $account
     * @return $this;
     */
    public function setAccount($account)
    {
        $this->account = $account;
        return $this;

    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     *
     * @param array $id
     * @return $this;
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;

    }

    /**
     * @return array
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param string $master
     * @return $this;
     */
    public function setMaster($master)
    {
        $this->master = $master;
        return $this;

    }

    /**
     * @return string
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     *
     * @param string $name
     * @return $this;
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;

    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param string $search
     * @return $this;
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;

    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     *
     * @param array $type
     * @return $this;
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;

    }

    /**
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }
}