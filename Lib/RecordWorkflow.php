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


use JMS\Serializer\SerializationContext;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Entity\RecordsHistory;
use SysEleven\PowerDnsBundle\Entity\RecordsHistoryRepository;

/**
 * Provides method for creating, updating and deleting records
 *
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */
class RecordWorkflow extends WorkflowAbstract
{
    /**
     * @var string
     */
    protected $repositoryClass = 'SysElevenPowerDnsBundle:Records';

    /**
     * Creates a new record in the backend, updates the soa serial number, and
     * creates a history entry for the record
     *
     * @param PowerDnsObjectInterface $obj
     * @param array $options
     * @param bool $force
     *
*@return mixed
     */
    public function create(PowerDnsObjectInterface $obj, array $options = array(), $force = false)
    {
        /**
         * @type Records $obj
         */
        $obj = parent::create($obj, $options, $force);
        if ($obj->getType() != 'SOA') {
            $this->updateSoa($obj->getDomain());
            $this->createHistory($obj, 'CREATE');
        }

        return $obj;
    }

    /**
     * Updates a given record in the backend, updates the soa serial number, and
     * creates a history entry for the record
     *
     * @param PowerDnsObjectInterface $obj
     * @param array $options
     * @param bool $force
     * @return mixed
     */
    public function update(PowerDnsObjectInterface $obj, array $options = array(), $force = false)
    {
        /**
         * @type Records $obj
         */
        $obj = parent::update($obj, $options, $force);
        if ($obj->getType() != 'SOA') {
            $this->updateSoa($obj->getDomain());
            $this->createHistory($obj, 'UPDATE');
        }


        return $obj;
    }

    /**
     * Deletes a given record from the backend, updates the soa serial number, and
     * creates a history entry for the record
     *
     * @param PowerDnsObjectInterface $obj
     * @param bool $force
     * @return mixed
     */
    public function delete(PowerDnsObjectInterface $obj, $force = false)
    {
        /**
         * @type Records $obj
         */
        $domain = $obj->getDomain();
        if ($obj->getType() != 'SOA') {
            $this->createHistory($obj, 'DELETE');
        }


        parent::delete($obj);
        if ($obj->getType() != 'SOA') {
            $this->updateSoa($domain);
        }

        return true;
    }

    /**
     *
     *
     * @param array $filter
     * @param array $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function searchHistory(array $filter = array(), array $order = array())
    {
        /**
         * @type RecordsHistoryRepository $repo
         */
        $repo = $this->getDatabase()->getRepository('SysElevenPowerDnsBundle:RecordsHistory');


        return $repo->createFilterQuery($filter, $order);
    }

    /**
     * Creates a new history.
     *
     * @param Records $record
     * @param string  $action
     *
     * @return bool
     */
    public function createHistory(Records $record, $action = 'CREATE')
    {
        $changes = $record->getChanges();

        if (0 == count($changes) && !in_array($action,array('CREATE','DELETE'))) {
            return true;
        }

        $content = $this->getSerializer()
                        ->serialize($record,'json', SerializationContext::create()->setGroups('history'));

        $history = new RecordsHistory();

        $history->setDomainId($record->getDomain()->getId());
        $history->setRecordId($record->getId());
        $history->setDomainName($record->getDomain()->getName());
        $history->setRecordType($record->getType());
        $history->setAction($action);
        $history->setContents(json_decode($content, true));
        $history->setChanges($changes);
        $history->setCreated(new \DateTime());
        $history->setUser($record->getUser());

        $this->getDatabase()->persist($history);
        $this->getDatabase()->flush($history);

        return true;
    }


    /**
     * @return \JMS\Serializer\Serializer
     */
    public function getSerializer()
    {
        $serializer = $this->getContainer()->get('jms_serializer');

        return $serializer;
    }


}