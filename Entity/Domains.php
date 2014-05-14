<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Entity
 */
namespace SysEleven\PowerDnsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation AS Serializer;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\PowerDnsObjectInterface;
use SysEleven\PowerDnsBundle\Lib\Soa;

/**
 * Table structure for domains table, holds the information about zones. Note:
 * there are three additional fields compared to the version that ships with powerdns.
 * user holds the username of the user who last changed the domain,
 * created and modified are holding the date of the creation and last modification.
 *
 * @ORM\Table(name="domains")
 * @ORM\Entity(repositoryClass="SysEleven\PowerDnsBundle\Entity\DomainsRepository")
 * @ORM\EntityListeners({"SysEleven\PowerDnsBundle\EventListener\DomainListener"})
 *
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields = {"name"}, message = "A zone with the same name already exists")
 *
 * @author  M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Entity
 */
class Domains implements PowerDnsObjectInterface
{
    /**
     * ID of the domain / zone
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"list", "details"})
     */
    protected $id;

    /**
     * Name of the Zone
     *
     * @var string $name
     * @Assert\Length(
     *      min = "2",
     *      max = "255",
     *      minMessage = "Please provide a zone name that is at least 2 long",
     *      maxMessage = "The name of the zone cannot exceed 255 in length")
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Serializer\Groups({"list", "details"})
     */
    protected $name;

    /**
     * Master of the zone (slave zone only)
     *
     * @var string $master
     *
     * @ORM\Column(name="master", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"list", "details"})
     */
    protected $master;

    /**
     * Date of the last check (slave zone only)
     *
     * @var integer $lastCheck
     *
     * @ORM\Column(name="last_check", type="integer", nullable=true)
     */
    protected $lastCheck;

    /**
     * Type of the zone either master, native, slave or supermaster.
     *
     * @var string $type
     *
     * @Assert\NotBlank(message="Type not given or not supported")
     * @Assert\Choice(choices = {"MASTER", "NATIVE", "SLAVE", "SUPERSLAVE"}, message = "Type not supported")
     *
     * @ORM\Column(name="type", type="string", length=6, nullable=false)
     *
     * @Serializer\Groups({"list", "details"})
     */
    protected $type;

    /**
     * Notified serial of the master zone
     *
     * @var integer $notifiedSerial
     *
     * @ORM\Column(name="notified_serial", type="integer", nullable=true)
     *
     * @Serializer\Groups({"list", "details"})
     */
    protected $notifiedSerial;

    /**
     * @var string $account
     *
     * @ORM\Column(name="account", type="string", length=40, nullable=true)
     *
     * @Serializer\Groups({"list", "details"})
     */
    protected $account;

    /**
     * @ORM\Column(name="user", type="text", nullable=true, length=100)
     * @var string username
     * @Serializer\Groups({"list", "details"})
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Records", mappedBy="domain", cascade={"all"})
     * @ORM\OrderBy({"name" = "ASC", "type" = "ASC"})
     *
     * @Serializer\Groups({"details"})
     * @Serializer\Accessor(getter="getRecords")
     */
    protected $records;

    /**
     * @var \DateTime
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var \DateTime
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    protected $modified;

    /**
     * @var boolean
     */
    protected $needsSoaUpdate = false;

    /**
     * Initializes the object
     */
    public function __construct()
    {
        $this->records = new ArrayCollection();
    }

    /**
     * Returns the id of the domain
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name of the domain to $name.
     *
     * @param string $name
     * @return Domains
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Returns the name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the master to $master.
     *
     * @param string $master
     * @return Domains
     */
    public function setMaster($master)
    {
        $this->master = $master;
    
        return $this;
    }

    /**
     * Returns the value of master
     *
     * @return string 
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * Sets last check to $lastCheck
     *
     * @param integer $lastCheck
     * @return Domains
     */
    public function setLastCheck($lastCheck)
    {
        $this->lastCheck = $lastCheck;
    
        return $this;
    }

    /**
     * Gets lastCheck
     *
     * @return integer 
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * Sets the type of the zone to $type
     *
     * @param string $type one of [MASTER, NATIVE, SLAVE, SUPERSLAVE]
     * @return Domains
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Gets the type of the domain
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the notified serial to $serial
     *
     * @param integer $notifiedSerial
     * @return Domains
     */
    public function setNotifiedSerial($notifiedSerial)
    {
        $this->notifiedSerial = $notifiedSerial;
    
        return $this;
    }

    /**
     * Returns the notified serial
     *
     * @return integer 
     */
    public function getNotifiedSerial()
    {
        return $this->notifiedSerial;
    }

    /**
     * Sets the account
     *
     * @param string $account
     * @return Domains
     */
    public function setAccount($account)
    {
        $this->account = $account;
    
        return $this;
    }

    /**
     * Gets the account
     *
     * @return string 
     */
    public function getAccount()
    {
        return $this->account;
    }


    /**
     * Add a new record to $this->records
     *
     * @param Records $records
     * @return Domains
     */
    public function addRecord(Records $records)
    {
        if ($this->records->contains($records)) {
            return $this;
        }

        $this->records->add($records);
    
        return $this;
    }

    /**
     * Remove the given record from this records
     *
     * @param Records $records
     *
     * @return $this
     */
    public function removeRecord(Records $records)
    {
        if ($this->records->contains($records)) {
            $this->records->removeElement($records);
        }

        return $this;
    }

    /**
     * Returns the records of the domain / zone. The records are sorted by
     * type first SOA, NS. MX then all other records.
     */
    public function getRecords()
    {
        $r = array('SOA'   => array(),
                   'NS'    => array(),
                   'MX'    => array(),
                   'OTHER' => array());

        /**
         * @var Records $record
         */
        foreach($this->records AS $record) {
            if ('SOA' == $record->getType()) {
                $r['SOA'][] = $record;
                continue;
            }

            if ('NS' == $record->getType()) {
                $r['NS'][] = $record;
                continue;
            }

            if ('MX' == $record->getType()) {
                $r['MX'][] = $record;
                continue;
            }

            if ('PTR' == $record->getType()) {
                $ip = $record->getForwardName();

                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    $r['OTHER'][$record->getName().'_'.uniqid()] = $record;
                    continue;
                }

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $r['OTHER'][$ip] = $record;
                    continue;
                }

                $l = sprintf("%u",ip2long($ip));
                $r['OTHER'][$l] = $record;
                continue;
            }

            $r['OTHER'][$record->getName().'_'.uniqid()] = $record;

        }

        $rr = array();

        foreach ($r AS $t => $records) {
            if($t == 'OTHER') {
                ksort($records);
            }

            $rr = array_merge($rr, array_values($records));
        }

        return new ArrayCollection($rr);

    }

    /**
     * Converts the object to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $map = array('name','master','type','account');

        $r = array();
        foreach($map AS $k) {
            $r[$k] = $this->$k;
        }

        return $r;
    }

    /**
     * Checks if the given zone is a reverse zone
     *
     * @return bool
     */
    public function isReverse()
    {
        if (false == strpos($this->name, 'in-addr.arpa') && false == strpos($this->name, 'ip6.arpa')) {
            return false;
        }

        return true;
    }

    /**
     * Returns the SOA Record of the domain
     * @return Records
     */
    public function getSoa()
    {
        /**
         * @type Records $record
         */
        foreach ($this->records AS $record) {
            if ('SOA' == $record->getType()) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Returns the serial number of the domain from the SOA record.
     *
     * @return mixed
     */
    public function getSerial()
    {
        $soa = $this->getSoa();
        if ($soa instanceof Records) {
            return $soa->getContent()->getSerial();
        }

        return null;
    }


    /**
     * @ORM\PreUpdate
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $event
     * @return bool
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('name')) {
            $this->needsSoaUpdate = true;
        }

        return true;
    }

    public function needsSoaUpdate()
    {
        return $this->needsSoaUpdate;
    }

    /**
     * @return mixed
     */
    public function looseCheck()
    {
        return false;
    }

    /**
     * Sets the username to the object
     *
     * @param $username
     *
     * @return mixed
     */
    public function setUser($username)
    {
        $this->user = $username;

        return $this;
    }

    /**
     * @param \DateTime $created
     *
     * @return Domains
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $modified
     *
     * @return Domains
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param boolean $needsSoaUpdate
     *
     * @return Domains
     */
    public function setNeedsSoaUpdate($needsSoaUpdate)
    {
        $this->needsSoaUpdate = $needsSoaUpdate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getNeedsSoaUpdate()
    {
        return $this->needsSoaUpdate;
    }





}