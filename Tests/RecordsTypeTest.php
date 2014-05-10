<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\Tests
 */

namespace SysEleven\PowerDnsBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Form\RecordsType;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\Soa;

class RecordsTypeTest extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->container = static::$kernel->getContainer();
    }


    public function testFormSimple()
    {
        /**
         * @type DomainWorkflow $workflow;
         * @type Domains $domainObj
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');
        $domainObj = $workflow->get(1);

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('www.'.$domainObj->getName());
        $recordObj->setType('A');
        $recordObj->setContent('1.1.1.1');

        /**
         * @type FormBuilder $form
         */
        $form = $this->container->get('form.factory')->create(new RecordsType(), $recordObj);

        $requestData = array('content' => '1.1.1.2');

        $form->submit($requestData, false);
        $this->assertEquals('1.1.1.2', $recordObj->getContent());
    }


    public function testSoa()
    {
        /**
         * @type DomainWorkflow $workflow;
         * @type Domains $domainObj
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');
        $domainObj = $workflow->get(1);

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);


        /**
         * @type FormBuilder $form
         */
        $form = $this->container->get('form.factory')->create(new RecordsType(), $recordObj);

        $requestData = array('type' => 'SOA', 'content' => array('default_ttl' => 3600), 'name' => $domainObj->getName());

        $form->submit($requestData, false);


        $data = $form->getData();

        $this->assertEquals('SOA', $recordObj->getType());
        $this->assertInstanceOf('SysEleven\PowerDnsBundle\Lib\Soa',$recordObj->getContent());
        $this->assertEquals('3600', $recordObj->getContent()->getDefaultTtl());
    }


    public function testPtr()
    {
        /**
         * @type DomainWorkflow $workflow;
         * @type Domains $domainObj
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');
        $domainObj = $workflow->get(1);

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);

        /**
         * @type FormBuilder $form
         */
        $form = $this->container->get('form.factory')->create(new RecordsType(), $recordObj);

        $requestData = array( 'content' => 'www.test.de', 'name' => '1.2.3.4','type' => 'PTR');

        $form->submit($requestData, false);


        $data = $form->getData();

        $this->assertEquals('PTR', $recordObj->getType());
        $this->assertEquals('4.3.2.1.in-addr.arpa', $recordObj->getName());
        $this->assertEquals('1.2.3.4',$recordObj->getForwardName());
    }

}
 