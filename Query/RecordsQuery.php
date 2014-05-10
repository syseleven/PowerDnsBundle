<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Query;

use SysEleven\PowerDnsBundle\Lib\QueryAbstract;

/**
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Query
 */
class RecordsQuery extends QueryAbstract
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $name_exact;

    /**
     * @var string
     */
    public $search;

    /**
     * @var array
     */
    public $type;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var int
     */
    public $domain_id;

    /**
     * @var int
     */
    public $managed;

    /**
     *
     * @param string $content
     * @return $this;
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;

    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     *
     * @param string $domain
     * @return $this;
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;

    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     *
     * @param int $domain_id
     * @return $this;
     */
    public function setDomainId($domain_id)
    {
        $this->domain_id = $domain_id;
        return $this;

    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domain_id;
    }

    /**
     *
     * @param int $id
     * @return $this;
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param int $managed
     * @return $this;
     */
    public function setManaged($managed)
    {
        $this->managed = $managed;
        return $this;

    }

    /**
     * @return int
     */
    public function getManaged()
    {
        return $this->managed;
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
     * @param string $name_exact
     * @return $this;
     */
    public function setNameExact($name_exact)
    {
        $this->name_exact = $name_exact;
        return $this;

    }

    /**
     * @return string
     */
    public function getNameExact()
    {
        return $this->name_exact;
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