<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\Tests
 */

namespace SysEleven\PowerDnsBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator;

class RecordValidatorTest extends WebTestCase
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

    public function testValidateUnsupported()
    {
        $validator = $this->container->get('validator');

        $recordObj = new Records();
        $recordObj->setName('wwww.foo.de');
        $recordObj->setType('NO');
        $recordObj->setContent('wwww2.foo.de');


        $errors = $validator->validate($recordObj);

        $this->assertCount(1, $errors);
    }

    public function testValidateNotEmpty()
    {
        $validator = $this->container->get('validator');

        $recordObj = new Records();
        $recordObj->setName('');
        $recordObj->setType('A');
        $recordObj->setContent('');

        $errors = $validator->validate($recordObj);

        $this->assertCount(2, $errors);

        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $validator = $this->container->get('validator');


        $recordObj = new Records();
        $recordObj->setName('');
        $recordObj->setType('CNAME');
        $recordObj->setContent('wwww.dummy.de');
        $recordObj->setDomain($domainObj);

        $errors = $validator->validate($recordObj);

        $this->assertCount(1, $errors);
    }


    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::validate
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validateDuplicateRecord
     */
    public function testValidateDuplicateRecord()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $records = $domainObj->getRecords();

        /**
         * @type Records $recordObj
         */
        $recordObj = null;
        foreach ($records AS $recordObj) {
            if ($recordObj->getType() != 'SOA') {
                break;
            }
        }

        $newRecord = new Records();
        $newRecord->setType($recordObj->getType());
        $newRecord->setName($recordObj->getName());
        $newRecord->setContent($recordObj->getContent());
        $newRecord->setDomain($domainObj);

        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(0, $errors);

        $errors = $validator->validate($newRecord);

        $this->assertCount(1, $errors);
    }

    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validatePtr
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkHostname
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkPtr
     */
    public function testValidatePtr()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('bogus___.');
        $recordObj->setType('PTR');
        $recordObj->setContent('1_');
        $recordObj->setPrio(-1);

        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);


        $this->assertCount(3, $errors);


        $domainObj = new Domains();
        $domainObj->setName('1.1.1.in-addr.arpa');
        $domainObj->setType('NATIVE');

        $domainObj = $workflow->create($domainObj);
        $recordObj = new Records();
        $recordObj->setName('1.1.1.1.in-addr.arpa');
        $recordObj->setType('PTR');
        $recordObj->setContent('www.example.com');
        $recordObj->setDomain($domainObj);

        $errors = $validator->validate($recordObj);
        $this->assertCount(0, $errors);
    }

    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validateNs
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkHostname
     */
    public function testValidateNs()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('bogus___.');
        $recordObj->setType('NS');
        $recordObj->setContent('1_');
        $recordObj->setPrio(-1);

        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);


        $this->assertCount(3, $errors);

    }

    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validateMx
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkHostname
     */
    public function testValidateMx()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('bogus___.');
        $recordObj->setType('MX');
        $recordObj->setContent('1_');


        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);


        $this->assertCount(3, $errors);

    }


    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validateCname
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkHostname
     */
    public function testValidateCname()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('bogus___.');
        $recordObj->setType('CNAME');
        $recordObj->setContent('1_');


        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(2, $errors);

        $recordObj->setName('bogus__+.'.$domainObj->getName());

        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(2, $errors);

        $recordObj->setLooseCheck(1);
        $errors = $validator->validate($recordObj);

        $this->assertCount(0, $errors);
    }


    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validateA
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkHostname
     */
    public function testValidateA()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('bogus___.');
        $recordObj->setType('A');
        $recordObj->setContent('name');


        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(2, $errors);

        $recordObj->setName('a+.'.$domainObj->getName());
        $recordObj->setContent('127.0.0.1');

        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(1, $errors);
    }

    /**
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_validateAaaa
     * @covers SysEleven\PowerDnsBundle\Validator\Constraints\RecordValidator::_checkHostname
     */
    public function testValidateAaaa()
    {
        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->container->get('syseleven.pdns.workflow.domains');

        /**
         * @type Domains $domainObj
         */
        $domainObj = $workflow->getRepository()->findOneBy(array());

        $recordObj = new Records();
        $recordObj->setDomain($domainObj);
        $recordObj->setName('bogus___.');
        $recordObj->setType('AAAA');
        $recordObj->setContent('name');


        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(2, $errors);

        $recordObj->setName('a+.'.$domainObj->getName());
        $recordObj->setContent('::1');

        $validator = $this->container->get('validator');
        $errors = $validator->validate($recordObj);

        $this->assertCount(1, $errors);
    }


    public function testCheckHostname()
    {
        $validator = new RecordValidator($this->container->get('doctrine'));

        $this->assertFalse($validator->_checkHostname('_.'));
    }


}
 