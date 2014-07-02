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
 * @package SysEleven\PowerDnsBundle\Validator\Constraints
 */
namespace SysEleven\PowerDnsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Form\Transformer\PtrTransformer;
use SysEleven\PowerDnsBundle\Lib\Soa;
use Zend\Validator\Hostname;

/**
 * Validates a given record entity depending on the type of the record.
 *
 * @author  M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Validator\Constraints
 */
class RecordValidator extends ConstraintValidator
{
    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em = null;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Checks if record is valid
     *
     * @param mixed                                   $record
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @return bool
     */
    public function validate($record, Constraint $constraint)
    {


        if (!$this->_validateSupported($record)) {
            return false;
        }

        if (!$this->_validateNotEmpty($record)) {
            return false;
        }

        /**
         * @var \SysEleven\PowerDnsBundle\Entity\Records $record
         */
        if (!$this->_validateDomain($record)) {
            return false;
        }

        if (!$this->_validateDuplicateRecord($record)) {
            return false;
        }

        // Check for any special method for this type
        $method = sprintf('_validate%s',ucfirst(strtolower($record->getType())));

        if (method_exists($this, $method)) {
            $this->{$method}($record);
        }

        return true;
    }

    /**
     * Checks if $ptr is a valid value for a PTR entry
     *
     * @param $ptr
     * @return bool
     */
    protected function _checkPtr($ptr)
    {
        $transformer = new PtrTransformer();

        if($ptr == ($t = $transformer->reverseTransform($ptr))) {
            return false;
        }

        return filter_var($t, FILTER_VALIDATE_IP);
    }

    /**
     * Checks if the given hostname is valid.
     *
     * @param      $hostname
     * @param bool $allowIp       Allow IP Addresses
     * @param bool $allowWildcard Allow Wildcard names
     * @param bool $allowLocal    Allow local Addresses
     * @param bool $looseCheck    Switches checking off checks only if
     *                            given variable is not empty
     *
     * @return bool
     */
    public function _checkHostname($hostname, $allowIp = true, $allowWildcard = false, $allowLocal = true, $looseCheck = false)
    {
        if('.' == substr($hostname,-1)) {
            return false;
        }

        if ($looseCheck === true) {
            return (0 == strlen(trim($hostname)))? false:true;
        }

        $n = new Hostname();
        $n->setAllow(Hostname::ALLOW_DNS);
        if($allowIp) {
           $n->setAllow(Hostname::ALLOW_IP | Hostname::ALLOW_DNS);
        }

        if($allowWildcard) {
            $hostname = str_replace('*.','bogus',$hostname);
        }

        if(!filter_var($hostname, FILTER_VALIDATE_IP)) {
            $exp = explode('.',$hostname);

            if(1 == count($exp)) {
                if(!$allowLocal) {
                    return false;
                }

                $hostname = $hostname.'.syseleven.de';
            }
        }

        if(!$n->isValid($hostname)) {
            return false;
        }

        return true;
    }

    public function _validateDomain(Records $record)
    {
        $domainObj = $record->getDomain();

        if (!in_array($domainObj->getType(), array('NATIVE', 'MASTER'))) {

            $msg = sprintf('Cannot create record, wrong domain type');

            $this->context->addViolationAt('name',
                $msg, array(), null);
            return false;
        }

        return true;
    }

    /**
     * Checks if a record with the same properties already exists in the database
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateDuplicateRecord(Records $record)
    {
        $criteria = array(
            'name'     => $record->getName(),
            'content'  => $record->getContent(),
            'type'     => $record->getType(),
            'domain'   => $record->getDomain()->getId());

        if ($record->getType() == 'SOA') {
            $criteria = array(
                'name'     => $record->getName(),
                'type'     => $record->getType(),
                'domain'   => $record->getDomain()->getId());
        }

        $records = $this->em->getRepository('SysElevenPowerDnsBundle:Records')->findBy($criteria);

        if($records) {
            /**
             * @var \SysEleven\PowerDnsBundle\Entity\Records $rec
             */
            foreach($records AS $rec) {
                if($rec != $record) {

                    if ($record->getType() != 'SOA') {
                        $msg = sprintf('A record with the given properties '
                            . 'already exists: %s/%s/%s/%s',
                            $rec->getId(),$rec->getName(),$rec->getType(),$rec->getContent());
                    } else {
                        $msg = sprintf('A record with the given properties '
                            . 'already exists: %s/%s/%s',
                            $rec->getId(),$rec->getName(),$rec->getType());
                    }

                    $this->context->addViolationAt('name',
                        $msg, array(), null);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks if the name and the content are given and not empty.
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateNotEmpty(Records $record)
    {
        if ($record->getType() == 'SOA') {
            if (0 == strlen($record->getName())) {
                $this->context->addViolationAt('name',
                    'You must provide a value for name', array(), null);
                return false;
            }

            return true;
        }

        if(0 == strlen(trim($record->getContent(true))) && 0 == strlen(trim($record->getName()))) {
            $this->context->addViolationAt('name',
                'You must provide a value for name', array(), null);

            $this->context->addViolationAt('content',
                'You must provide a value for content', array(), null);

            return false;
        }

        // There must always be a value for the name, regardless of bind syntax
        if (0 == strlen($record->getName())) {
            $this->context->addViolationAt('name',
                'You must provide a name for type: '.$record->getType(), array(), null);

        }

        return true;
    }

    /**
     * Validates if the given record type is supported
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateSupported(Records $record)
    {
        if (!in_array($record->getType(), $this->_getSupportedTypes())) {
            $this->context->addViolationAt('type',
                'Type: '.$record->getType().' is not supported', array(), null);

            return false;
        }

        return true;
    }


    /**
     * Checks if the given PTR record is valid
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validatePtr(Records $record)
    {
        $ip       = $record->getName();
        $hostname = $record->getContent();
        $looseHostname = (1 == $record->getLooseCheck());

        // Check if the content is a valid hostname
        if(!$this->_checkHostname($hostname, false, false, false, $looseHostname)) {
            $this->context->addViolationAt('content',
                'Hostname: '.$hostname. ' is not a valid hostname',array(), null);
        }

        // Check if the ip is in the right format
        if(!$this->_checkPtr($ip)) {
            $this->context->addViolationAt('name',
                'IP address: '.$ip.' is not valid or has not a valid format for a PTR record',array(), null);
        }

        // Check if the record is part of the zone.
        $domainName = $record->getDomain()->getName();

        if (false == strpos($ip, '.'.$domainName)) {
            $this->context->addViolationAt('name',
                'IP address:'.$ip.' is not part of the zone: '.$domainName,array(), null);
        }

        return true;
    }


    /**
     * Validates a MX record
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateMx(Records $record)
    {
        if (0 == strlen($record->getPrio()) || !filter_var($record->getPrio(), FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 9999999)))) {
            $this->context->addViolationAt('prio',
                'Value: '.$record->getPrio().' is not a valid priority for MX',array(), null);
        }

        return $this->_validateNs($record);
    }

    /**
     * Validates a NS record
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateNs(Records $record)
    {
        $name   = $record->getName();
        $target = $record->getContent();

        if(0 != strlen($record->getName()) ) {
            if(!$this->_checkHostname($name.'.'.$record->getDomain()->getName(), true, $record->getLooseCheck())) {
                $this->context->addViolationAt('name',
                    'Value: '.$name.' is not a valid hostname or ip address',array(), null);
            }
        }

        if(!$this->_checkHostname($target, true)) {
            $this->context->addViolationAt('content',
                'Value: '.$target.' is not a valid hostname or ip address',array(), null);
        }

        $options = array('options' => array('min_range' => 1, 'max_range' => 99999));
        // We don't force a priority for NS
        // But if there, we check if it is valid
        if (0 != strlen($record->getPrio()) && !filter_var($record->getPrio(), FILTER_VALIDATE_INT, $options)) {
            $this->context->addViolationAt('prio',
                'Value: '.$record->getPrio().' is not a valid priority',array(), null);
        }


        return true;
    }


    /**
     * Checks a A record for validity.
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateA(Records $record)
    {
        $name = $record->getName();

        if(0 != strlen($name) ) {
            // Check the hostname only if necessary
            if (!$record->getLooseCheck()) {
                // First check if we're in the domain.
                if ($name != $record->getDomain()->getName() && strpos($name, '.'.$record->getDomain()->getName()) === false) {
                    $this->context->addViolationAt('name',
                        'Name: '.$name.' is not in domain: '.$record->getDomain()->getName(),
                        array(), null);
                } else {
                    if(!$this->_checkHostname($name, false, true)) {
                        $this->context->addViolationAt('name',
                            'Name: '.$name.' is not a valid hostname',array(), null);
                    }
                }
            }
        }

        if(!filter_var($record->getContent(),FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->context->addViolationAt('content',
                'You must provide a valid ip for a an record of type '.$record->getType(), array(), null);
        }

        return true;
    }

    /**
     * Checks a AAAA record for validity
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateAaaa(Records $record)
    {
        if(0 != strlen($record->getName()) ) {
            if (!$record->getLooseCheck()) {
                if (false === strpos($record->getName(), '.'.$record->getDomain()->getName())) {
                    $this->context->addViolationAt('name',
                        'Name: '.$record->getName().' is not in domain: '.$record->getDomain()->getName(),
                        array(), null);
                } else {
                    if(!$this->_checkHostname($record->getName(), false, true)) {
                        $this->context->addViolationAt('name',
                            'Name: '.$record->getName().' is not a valid hostname',array(), null);
                    }
                }
            }
        }

        if(!filter_var($record->getContent(),FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->context->addViolationAt('content',
                'You must provide a valid ip for a an record of type AAAA', array(), null);
        }

        return true;
    }

    /**
     * Checks a CNAME record for validity.
     *
     * @param Records    $record
     *
     * @return bool
     */
    public function _validateCname(Records $record)
    {
        $name = $record->getName();
        $looseHostname = (0 == $record->getLooseCheck())? false:true;

        if(0 != strlen($name) ) {
            if (false === $looseHostname) {
                if (false === strpos($name, '.'.$record->getDomain()->getName())) {
                    $this->context->addViolationAt('name',
                        'Name: '.$name.' is not in domain: '.$record->getDomain()->getName(),
                        array(), null);
                } else {
                    if(!$this->_checkHostname($name, false, true, true, $looseHostname)) {
                        $this->context->addViolationAt('name',
                            'Name: '.$name.' is not a valid hostname',array(), null);
                    }
                }
            }
        }

        if(!$this->_checkHostname($record->getContent(), false, true, true, $looseHostname)) {
            $this->context->addViolationAt('content',
                'Name: '.$record->getContent().' is not a valid hostname',array(), null);
        }

        return true;
    }


    /**
     * @return array
     */
    protected function _getSupportedTypes()
    {
        return array("SOA", "A", "AAAA", "ASFB", "CERT", "CNAME",
                   "DNSKEY", "DS", "HINFO", "KEY", "LOC", "MX", "NAPTR",
                   "NS", "NSEC", "PTR", "RP", "RRSIG", "SPF", "SSHFP",
                   "SRV","TXT","SOA");
    }


}