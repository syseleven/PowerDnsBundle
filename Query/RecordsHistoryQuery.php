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
use Doctrine\Common\Collections\ArrayCollection;
use SysEleven\PowerDnsBundle\Lib\QueryAbstract;


/**
 * Query class for record history searches, provides the field definition for the record
 * history search form
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Query
 */
class RecordsHistoryQuery extends QueryAbstract
{
    /**
     * @var ArrayCollection
     */
    public $domain_id;

    /**
     * @var ArrayCollection
     */
    public $record_id;

    /**
     * @var string
     */
    public $search;

    /**
     * @var string
     */
    public $user;

    /**
     * @var array
     */
    public $record_type;

    /**
     * @var \DateTime
     */
    public $from;

    /**
     * @var  \DateTime
     */
    public $to;

    /**
     * @var array
     */
    public $action;

    /**
     * @param array $action
     *
     * @return RecordsHistoryQuery
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param \SysEleven\PowerDnsBundle\Query\ArrayCollection $domain_id
     *
     * @return RecordsHistoryQuery
     */
    public function setDomainId($domain_id)
    {
        $this->domain_id = $domain_id;
        return $this;
    }

    /**
     * @return \SysEleven\PowerDnsBundle\Query\ArrayCollection
     */
    public function getDomainId()
    {
        return $this->domain_id;
    }

    /**
     * @param \DateTime $from
     *
     * @return RecordsHistoryQuery
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param ArrayCollection $record_id
     *
     * @return RecordsHistoryQuery
     */
    public function setRecordId($record_id)
    {
        $this->record_id = $record_id;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRecordId()
    {
        return $this->record_id;
    }

    /**
     * @param array $record_type
     *
     * @return RecordsHistoryQuery
     */
    public function setRecordType($record_type)
    {
        $this->record_type = $record_type;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecordType()
    {
        return $this->record_type;
    }

    /**
     * @param string $search
     *
     * @return RecordsHistoryQuery
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
     * @param \DateTime $to
     *
     * @return RecordsHistoryQuery
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $user
     *
     * @return RecordsHistoryQuery
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }
}
 