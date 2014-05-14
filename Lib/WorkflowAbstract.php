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


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException;
use Symfony\Component\Validator\Validator;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;

/**
 * Abstract Workflow for accessing and manipulating domain and record objects.
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */
abstract class WorkflowAbstract implements ContainerAwareInterface
{

    /**
     * Name of the repository class of this object
     *
     * @var string
     */
    protected $repositoryClass = null;

    /**
     * Array of warnings
     *
     * @var null
     */
    protected $warnings = array();

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Name of the current user
     *
     * @var string
     */
    protected $username;

    /**
     * Name of the entity manager to use
     *
     * @var string
     */
    protected $connectionName;


    /**
     * Searches in the backend and returns a query builder object
     *
     * @param array $filter
     * @param array $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function search(array $filter = array(), array $order = array())
    {
        /**
         * @type PowerDnsRepositoryInterface $repo;
         */
        $repo = $this->getRepository();

        return $repo->createSearchQuery($filter, $order);
    }

    /**
     * Returns the object specified by id
     *
     * @param $id
     * @throws Exceptions\NotFoundException
     * @return mixed
     */
    public function get($id)
    {
        $powerDnsObj = $this->getRepository()->find($id);

        if (!$powerDnsObj) {
            throw new NotFoundException('Cannot find object with id: '.$id);
        }

        return $powerDnsObj;
    }

    /**
     * Creates a new Object in the backend
     *
     * @param PowerDnsObjectInterface $obj
     * @param array $options
     * @param bool $force
     * @throws Exceptions\ValidationException
     * @return mixed
     */
    public function create(PowerDnsObjectInterface $obj, array $options = array(), $force = false)
    {
        $loose = $obj->looseCheck();
        $groups = ($loose)? array('loose'):array();

        /**
         * @type Validator $validator
         */
        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validate($obj, $groups);

        if (0 != count($errors)) {
            if (!$force) {
                throw new ValidationException('Cannot create object not valid', $errors);
            }

            $this->setWarnings($errors);
        }

        $obj->setUser($this->getUsername());

        $this->getDatabase()->persist($obj);
        $this->getDatabase()->flush();

        return $obj;
    }

    /**
     * Updates the object in the backend
     *
     * @param PowerDnsObjectInterface $obj
     * @param array $options
     * @param bool $force
     * @throws Exceptions\ValidationException
     * @return mixed
     */
    public function update(PowerDnsObjectInterface $obj, array $options = array(), $force = false)
    {
        $loose = $obj->looseCheck();
        $groups = ($loose)? array('loose'):array();

        /**
         * @type Validator $validator
         */
        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validate($obj, $groups);

        if (0 != count($errors)) {
            if (!$force) {
                throw new ValidationException('Cannot create object not valid', $errors);
            }

            $this->setWarnings($errors);
        }

        $this->getDatabase()->persist($obj);
        $this->getDatabase()->flush();

        return $obj;
    }

    /**
     * Deletes the object in the database
     *
     * @param PowerDnsObjectInterface $obj
     * @return mixed
     */
    public function delete(PowerDnsObjectInterface $obj)
    {
        $this->getDatabase()->remove($obj);
        $this->getDatabase()->flush();

        return true;
    }

    /**
     * Creates a new Soa Entry for the class
     *
     * @param Domains $domainObj
     * @return Records
     */
    public function createSoa(Domains $domainObj)
    {
        if ($soa = $domainObj->getSoa()) {
            $this->updateSoa($domainObj);
        }

        $soaDefaults = new Soa();

        $soa = new Records();
        $soa->setName($domainObj->getName());
        $soa->setType('SOA');
        $soa->setContent($soaDefaults);
        $soa->setDomain($domainObj);
        $soa->setTtl($soaDefaults->getDefaultTtl());

        $this->getDatabase()->persist($soa);
        $this->getDatabase()->flush();

        $domainObj->addRecord($soa);

        return $soa;
    }

    /**
     * Updates the soa record for the given domain
     * @param Domains $domainObj
     * @param null $serial
     * @return Records
     */
    public function updateSoa(Domains $domainObj, $serial = null)
    {
        if (!($soa = $domainObj->getSoa())) {
            $this->createSoa($domainObj);
        }

        /**
         * @type Soa $soaObj
         */
        $soaObj = $soa->getContent();

        if (is_null($serial) || !filter_var($serial, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $serial = strtotime('now');
        }

        $soaObj->setSerial($serial);
        $soa->setName($domainObj->getName());
        $soa->setContent($soaObj);

        $this->getDatabase()->persist($soa);
        $this->getDatabase()->flush();
        $this->getDatabase()->refresh($soa);

        return $soa;
    }

    /**
     * Returns the soa defaults
     *
     * @return array
     */
    public function getSoaDefaults()
    {
        return $this->soaDefaults;
    }

    /**
     * @param $defaults
     * @return $this
     */
    public function setSoaDefaults($defaults)
    {
        $this->soaDefaults = $defaults;

        return $this;
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

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getDatabase()
    {
        return $this->container->get('doctrine')->getManager($this->getConnection());
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getDatabase()->getRepository($this->repositoryClass);
    }

    /**
     *
     * @param null $warnings
     * @return $this;
     */
    public function setWarnings($warnings)
    {
        $this->warnings = $warnings;
        return $this;

    }

    /**
     * @return null
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return bool
     */
    public function hasWarnings()
    {
        return (!is_null($this->warnings) && 0 != count($this->warnings));
    }


    /**
     * Sets the username to $username.
     *
     * @param $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setConnection($name)
    {
        $this->connectionName = $name;

        return $this;
    }

    public function getConnection()
    {
        return $this->connectionName;
    }
}