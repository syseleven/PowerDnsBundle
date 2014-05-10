<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Lib;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\DomainsRepository;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;

/**
 * Provides methods for creating and manipulating domain object.
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */
class DomainWorkflow extends WorkflowAbstract
{

    /**
     * @var string
     */
    protected $repositoryClass = 'SysElevenPowerDnsBundle:Domains';

    /**
     * Creates a new Domain in the backend. Note: the parameter $force is disabled.
     *
     * @param PowerDnsObjectInterface $domainObj
     * @param array $options
     * @param bool $force
     * @throws Exceptions\ValidationException
     * @return \SysEleven\PowerDnsBundle\Entity\Domains
     */
    public function create(PowerDnsObjectInterface $domainObj, array $options = array(), $force = false)
    {

        $domainObj = parent::create($domainObj, $options, false);

        /**
         * @type Domains $domainObj
         */
        $this->createSoa($domainObj);

        return $this->get($domainObj->getId());
    }

    /**
     * @param PowerDnsObjectInterface $domainObj
     * @param array $options
     * @param bool $force
     * @throws Exceptions\ValidationException
     * @return \SysEleven\PowerDnsBundle\Entity\Domains
     */
    public function update(PowerDnsObjectInterface $domainObj, array $options = array(), $force = false)
    {
        $domainObj = parent::update($domainObj, $options, false);

        /**
         * @type Domains $domainObj
         */
        if ($domainObj->needsSoaUpdate()) {
            $this->updateSoa($domainObj);
        }

        return $domainObj;
    }
}