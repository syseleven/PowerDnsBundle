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
 * @package SysEleven\PowerDnsBundle\Tests
 */
namespace SysEleven\PowerDnsBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;
use SysEleven\PowerDnsBundle\Lib\RecordWorkflow;
use SysEleven\PowerDnsBundle\Query\DomainsQuery;
use SysEleven\PowerDnsBundle\Query\RecordsQuery;


/**
 * Tests the functionality of the record workflow class
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Tests
 */
class RecordWorkflowTest extends WebTestCase
{

    /**
     * @var RecordWorkflow
     */
    public $workflow = null;

    /**
     * @var ContainerInterface
     */
    public $container = null;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->container = static::$kernel->getContainer();
        $this->workflow  = static::$kernel->getContainer()->get('syseleven.pdns.workflow.records');
    }


    public function testSearch()
    {
        /**
         * @type Domains
         */
        $domainObj = $this->container->get('syseleven.pdns.workflow.domains')->getRepository()->findOneBy(array());


        $query = new RecordsQuery();
        $query->setId(1);
        $query->setType(array('PTR','A','AAAA'));
        $query->setName('1.1.1.1');
        $query->setNameExact('www.example.com');
        $query->setContent('1.1.1.1');
        $query->setDomainId(1);
        $query->setDomain($domainObj);
        $query->setManaged(1);
        $query->setSearch('search');


        $qb = $this->workflow->search($query->toArray());

        $parameters = $qb->getParameters();
        $p = array();
        foreach ($parameters AS $parameter) {
            /**
             * @type \Doctrine\ORM\Query\Parameter $parameter
             */
            $p[$parameter->getName()] = $parameter->getValue();
        }


        $this->assertArrayHasKey('id', $p);
        $this->assertArrayHasKey('type', $p);
        $this->assertArrayHasKey('name', $p);
        $this->assertArrayHasKey('name_exact', $p);
        $this->assertArrayHasKey('content', $p);
        $this->assertArrayHasKey('managed', $p);
        $this->assertArrayHasKey('search', $p);
        $this->assertArrayHasKey('domain_id',$p);

        try {
            $qb->getQuery()->getResult();

        } catch (\Exception $e) {
            $this->fail('SQL did not validate: '.$e->getMessage());
        }
    }


    public function testCreate()
    {
        /**
         * @type Domains $domainObj
         */
        $domainObj = $this->container->get('syseleven.pdns.workflow.domains')->getRepository()->findOneBy(array('type' => 'NATIVE'));

        $serial = $domainObj->getSerial();

        sleep(4);

        $recordObj = new Records();
        $recordObj->setName('www.'.$domainObj->getName());
        $recordObj->setType('A');
        $recordObj->setContent('1.1.1.9');
        $recordObj->setDomain($domainObj);

        $recordObj = $this->workflow->create($recordObj);

        $this->assertNotEmpty($recordObj->getId());
        $this->assertNotEquals($serial, $domainObj->getSerial());

        return $recordObj->getId();

    }

    /**
     * @param $id
     * @depends testCreate
     */
    public function testGet($id)
    {
        try {

            $this->workflow->get(99999999);
            $this->fail('Expected Exception');

        } catch (NotFoundException $nf) {
            $this->assertInstanceOf('\SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException', $nf);
        }

        /**
         * @type Records $recordObj
         */
        $recordObj = $this->workflow->get($id);

        $this->assertInstanceOf('\SysEleven\PowerDnsBundle\Entity\Records', $recordObj);
        $this->assertEquals($id, $recordObj->getId());

        return $id;
    }

    /**
     * @param $id
     * @depends testGet
     */
    public function testUpdate($id)
    {
        // Sleep for two seconds to make sure the serial is updated correctly
        sleep(2);
        /**
         * @type Records $recordObj
         */
        $recordObj = $this->workflow->get($id);

        $serial = $recordObj->getDomain()->getSerial();

        $recordObj->setContent('1.1.1.8');

        $recordObj = $this->workflow->update($recordObj);
        $this->assertEquals('1.1.1.8',$recordObj->getContent());

        $newSerial = $recordObj->getDomain()->getSerial();
        $this->assertNotEquals($serial, $newSerial);

        return $id;
    }

    /**
     * @param $id
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        /**
         * @type Records $recordObj
         */
        $recordObj = $this->workflow->get($id);
        $this->workflow->delete($recordObj);

        try {

            $this->workflow->get($id);
            $this->fail('Expected Exception');

        } catch (NotFoundException $nf) {
            $this->assertInstanceOf('\SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException', $nf);
        }
    }

}
 