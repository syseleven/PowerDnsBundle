<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;
use SysEleven\PowerDnsBundle\Query\DomainsQuery;

class DomainWorkflowTest extends WebTestCase
{

    /**
     * @var DomainWorkflow
     */
    public $workflow = null;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->workflow = static::$kernel->getContainer()->get('syseleven.pdns.workflow.domains');
    }


    public function testSearch()
    {
        $query = new DomainsQuery();
        $query->setSearch('search')
              ->setType('MASTER')
              ->setName('name')
              ->setAccount('account')
              ->setId(1)
              ->setMaster('master');

        /**
         * @type \Doctrine\ORM\QueryBuilder $qb
         */
        $qb = $this->workflow->search($query->toArray());

        $parameters = $qb->getParameters();
        $p = array();
        foreach ($parameters AS $parameter) {
            /**
             * @type \Doctrine\ORM\Query\Parameter $parameter
             */
            $p[$parameter->getName()] = $parameter->getValue();
        }

        $this->assertArrayHasKey('search', $p);
        $this->assertArrayHasKey('type', $p);
        $this->assertArrayHasKey('name', $p);
        $this->assertArrayHasKey('account', $p);
        $this->assertArrayHasKey('id', $p);
        $this->assertArrayHasKey('master', $p);
    }


    /**
     * @return Domains
     */
    public function testCreate()
    {
        $domainObj = new Domains();
        $domainObj->setName('test.de');
        $domainObj->setType('NATIVE');

        $domainObj = $this->workflow->create($domainObj);


        $this->assertNotEmpty($domainObj->getId());

        $soa = $domainObj->getSoa();
        $this->assertInstanceOf('\SysEleven\PowerDnsBundle\Entity\Records', $soa);
        $this->assertEquals('test.de', $soa->getName());


        return $domainObj->getId();

    }

    /**
     * @param $id
     *
     * @return mixed
     * @depends testCreate
     */
    public function testGet($id)
    {
        $domainObj = $this->workflow->get($id);

        $this->assertEquals($id, $domainObj->getId());

        return $id;
    }


    /**
     * @depends testGet
     */
    public function testUpdate($id)
    {
        /**
         * @type Domains
         */
        $domainObj = $this->workflow->get($id);
        $domainObj->setName('test-neu.de');

        $domainObj = $this->workflow->update($domainObj);

        $soa = $domainObj->getSoa();
        $this->assertInstanceOf('\SysEleven\PowerDnsBundle\Entity\Records', $soa);
        $this->assertEquals('test-neu.de', $soa->getName());

        return $id;
    }

    /**
     * @depends testUpdate
     * @expectedException \SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException
     */
    public function testDelete($id)
    {
        /**
         * @type Domains
         */
        $domainObj = $this->workflow->get($id);
        $this->workflow->delete($domainObj);
        $this->workflow->get($id);
    }


    public function testCreateErrors()
    {
        try {
            $domainObj = new Domains();
            $domainObj->setName('foo.de');
            $this->workflow->create($domainObj);

        } catch (\Exception $e) {
            $this->assertInstanceOf('SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException', $e);

            /**
             * @type ValidationException $e
             */
            $this->assertCount(2, $e->getErrors());

            $p = array();
            /**
             * @type \Symfony\Component\Validator\ConstraintViolation $error
             */
            foreach ($e->getErrors() AS $error) {
                $p[] = $error->getPropertyPath();
            }

            $this->assertContains('name', $p);
            $this->assertContains('type', $p);
        }
    }


    public function testCreateForceIsDisabled()
    {
        try {
            $domainObj = new Domains();
            $domainObj->setName('foo.de');
            $this->workflow->create($domainObj, array(), true);

        } catch (\Exception $e) {
            $this->assertInstanceOf('SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException', $e);

            /**
             * @type ValidationException $e
             */
            $this->assertCount(2, $e->getErrors());

            $p = array();
            /**
             * @type \Symfony\Component\Validator\ConstraintViolation $error
             */
            foreach ($e->getErrors() AS $error) {
                $p[] = $error->getPropertyPath();
            }

            $this->assertContains('name', $p);
            $this->assertContains('type', $p);
        }

    }



}
