<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\DataFixtures\Orm
 */
namespace SysEleven\PowerDnsBundle\DataFixtures\Orm;


use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;
use SysEleven\PowerDnsBundle\Lib\RecordWorkflow;


/**
 * Class LoadDomainsData
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\DataFixtures\Orm
 */
class LoadDomainsData implements FixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    public $container;


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {

        $data = array();
        $data[] = array('name' => 'foo.de', 'type' => 'NATIVE');
        $data[] = array('name' => 'bar.de', 'type' => 'NATIVE');
        $data[] = array('name' => 'foobar.de', 'type' => 'NATIVE');
        $data[] = array('name' => 'barfoo.de', 'type' => 'NATIVE');
        $data[] = array('name' => '0.1.1.in-addr.arpa', 'type' => 'NATIVE');
        $data[] = array('name' => '0.1.2.in-addr.arpa', 'type' => 'NATIVE');
        $data[] = array('name' => '0.0.0.8.2.1.0.2.0.0.0.3.0.0.0.0.0.0.4.8.c.3.1.0.0.a.2.ip6.arpa', 'type' => 'NATIVE');
        $data[] = array('name' => '0.0.0.9.2.1.0.2.0.0.0.3.0.0.0.0.0.0.4.8.c.3.1.0.0.a.2.ip6.arpa', 'type' => 'NATIVE');
        $data[] = array('name' => 'foo-master.de', 'type' => 'MASTER');
        $data[] = array('name' => 'foo-slave.de', 'type' => 'SLAVE');


        $this->loadFoo_de($manager);
        $this->loadBar_de($manager);
        $this->loadFooBar_de($manager);
        $this->loadMaster_de($manager);
    }


    public function loadFoo_de (ObjectManager $manager)
    {
        try {

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->container->get('syseleven.pdns.workflow.records');


            $domainObj = new Domains();
            $domainObj->setName('foo.de');
            $domainObj->setType('NATIVE');

            $manager->persist($domainObj);

            $manager->flush();

            $soa = new Records();
            $soa->setName('foo.de');
            $soa->setType('SOA');
            $soa->setContent('  1375872531 10800 604800 3600 ');
            $soa->setTtl(3600);
            $soa->setChangeDate(strtotime('now'));
            $soa->setManaged(1);
            $soa->setDomain($domainObj);

            $recordWorkflow->create($soa);
            $domainObj->addRecord($soa);
            $recordWorkflow->createHistory($soa, 'CREATE');

            $ns1 = new Records();
            $ns1->setName('foo.de');
            $ns1->setContent('ns1.ns.de');
            $ns1->setPrio(10);
            $ns1->setTtl(3600);
            $ns1->setType('NS');
            $ns1->setDomain($domainObj);

            $recordWorkflow->create($ns1);

            $ns2 = new Records();
            $ns2->setName('foo.de');
            $ns2->setContent('ns2.ns.de');
            $ns2->setPrio(20);
            $ns2->setTtl(3600);
            $ns2->setType('NS');
            $ns2->setDomain($domainObj);

            $recordWorkflow->create($ns2);

            $mx = new Records();
            $mx->setName('foo.de');
            $mx->setContent('mail.foo.de');
            $mx->setPrio(20);
            $mx->setTtl(3600);
            $mx->setType('MX');
            $mx->setDomain($domainObj);

            $recordWorkflow->create($mx);

            $www = new Records();
            $www->setName('www.foo.de');
            $www->setType('A');
            $www->setContent('1.1.1.1');
            $www->setDomain($domainObj);

            $recordWorkflow->create($www);

            $www = new Records();
            $www->setName('www2.foo.de');
            $www->setType('CNAME');
            $www->setContent('www.foo.de');
            $www->setDomain($domainObj);

            $recordWorkflow->create($www);


            $manager->flush();

        } catch (ValidationException $ve) {
            var_dump($ve->getErrors());
            exit;
        }


    }


    public function loadBar_de (ObjectManager $manager)
    {

        /**
         * @type RecordWorkflow $recordWorkflow
         */
        $recordWorkflow = $this->container->get('syseleven.pdns.workflow.records');
        
        $domainObj = new Domains();
        $domainObj->setName('bar.de');
        $domainObj->setType('NATIVE');

        $manager->persist($domainObj);

        $manager->flush();

        $soa = new Records();
        $soa->setName('bar.de');
        $soa->setType('SOA');
        $soa->setContent('  1375872531 10800 604800 3600 ');
        $soa->setTtl(3600);
        $soa->setChangeDate(strtotime('now'));
        $soa->setManaged(1);
        $soa->setDomain($domainObj);

        $recordWorkflow->create($soa);
        $domainObj->addRecord($soa);
        $recordWorkflow->createHistory($soa, 'CREATE');

        $ns1 = new Records();
        $ns1->setName('bar.de');
        $ns1->setContent('ns1.ns.de');
        $ns1->setPrio(10);
        $ns1->setTtl(3600);
        $ns1->setType('NS');
        $ns1->setDomain($domainObj);

        $recordWorkflow->create($ns1);

        $ns2 = new Records();
        $ns2->setName('bar.de');
        $ns2->setContent('ns2.ns.de');
        $ns2->setPrio(20);
        $ns2->setTtl(3600);
        $ns2->setType('NS');
        $ns2->setDomain($domainObj);

        $recordWorkflow->create($ns2);

        $mx = new Records();
        $mx->setName('bar.de');
        $mx->setContent('mail.bar.de');
        $mx->setPrio(20);
        $mx->setTtl(3600);
        $mx->setType('MX');
        $mx->setDomain($domainObj);

        $recordWorkflow->create($mx);

        $www = new Records();
        $www->setName('www.bar.de');
        $www->setType('A');
        $www->setContent('2.1.1.1');
        $www->setDomain($domainObj);

        $recordWorkflow->create($www);

        $www = new Records();
        $www->setName('www2.bar.de');
        $www->setType('CNAME');
        $www->setContent('www.bar.de');
        $www->setDomain($domainObj);

        $recordWorkflow->create($www);
    }

    public function loadFooBar_de (ObjectManager $manager)
    {
        /**
         * @type RecordWorkflow $recordWorkflow
         */
        $recordWorkflow = $this->container->get('syseleven.pdns.workflow.records');
        
        $domainObj = new Domains();
        $domainObj->setName('foobar.de');
        $domainObj->setType('NATIVE');

        $manager->persist($domainObj);

        $manager->flush();

        $soa = new Records();
        $soa->setName('foobar.de');
        $soa->setType('SOA');
        $soa->setContent('  1375872531 10800 604800 3600 ');
        $soa->setTtl(3600);
        $soa->setChangeDate(strtotime('now'));
        $soa->setManaged(1);
        $soa->setDomain($domainObj);

        $recordWorkflow->create($soa);
        $domainObj->addRecord($soa);
        $recordWorkflow->createHistory($soa, 'CREATE');

        $ns1 = new Records();
        $ns1->setName('foobar.de');
        $ns1->setContent('ns1.ns.de');
        $ns1->setPrio(10);
        $ns1->setTtl(3600);
        $ns1->setType('NS');
        $ns1->setDomain($domainObj);

        $recordWorkflow->create($ns1);

        $ns2 = new Records();
        $ns2->setName('foobar.de');
        $ns2->setContent('ns2.ns.de');
        $ns2->setPrio(20);
        $ns2->setTtl(3600);
        $ns2->setType('NS');
        $ns2->setDomain($domainObj);

        $recordWorkflow->create($ns2);

        $mx = new Records();
        $mx->setName('foobar.de');
        $mx->setContent('mail.foobar.de');
        $mx->setPrio(20);
        $mx->setTtl(3600);
        $mx->setType('MX');
        $mx->setDomain($domainObj);

        $recordWorkflow->create($mx);

        $www = new Records();
        $www->setName('www.foobar.de');
        $www->setType('A');
        $www->setContent('2.1.1.1');
        $www->setDomain($domainObj);

        $recordWorkflow->create($www);

        $www = new Records();
        $www->setName('www2.foobar.de');
        $www->setType('CNAME');
        $www->setContent('www.foobar.de');
        $www->setDomain($domainObj);

        $recordWorkflow->create($www);
    }

    public function loadMaster_de (ObjectManager $manager)
    {
        $domainObj = new Domains();
        $domainObj->setName('master.de');
        $domainObj->setType('MASTER');

        $manager->persist($domainObj);

        $manager->flush();
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @return $this
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }
}
 