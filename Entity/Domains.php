<?php
/**
 * Syseleven PowerDns API
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
namespace SysEleven\PowerDnsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation AS Serializer;
use Gedmo\Mapping\Annotation as Gedmo;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Lib\PowerDnsObjectInterface;
use SysEleven\PowerDnsBundle\Lib\Soa;

/**
 * SysEleven\PowerDnsBundle\Entity\Domains
 *
 * @ORM\Table(name="domains")
 * @ORM\Entity(repositoryClass="SysEleven\PowerDnsBundle\Entity\DomainsRepository")
 * @ORM\EntityListeners({"SysEleven\PowerDnsBundle\EventListener\DomainListener"})
 *
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields = {"name"}, message = "A zone with the same name already exists")
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
class Domains implements PowerDnsObjectInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"list", "details"})
     */
    protected $id;

    /**
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
     * @var string $master
     *
     * @ORM\Column(name="master", type="string", length=128, nullable=true)
     *
     * @Serializer\Groups({"list", "details"})
     */
    protected $master;

    /**
     * @var integer $lastCheck
     *
     * @ORM\Column(name="last_check", type="integer", nullable=true)
     */
    protected $lastCheck;

    /**
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
     * Constructor
     */
    public function __construct()
    {
        $this->records = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set master
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
     * Get master
     *
     * @return string 
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * Set lastCheck
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
     * Get lastCheck
     *
     * @return integer 
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Domains
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set notifiedSerial
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
     * Get notifiedSerial
     *
     * @return integer 
     */
    public function getNotifiedSerial()
    {
        return $this->notifiedSerial;
    }

    /**
     * Set account
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
     * Get account
     *
     * @return string 
     */
    public function getAccount()
    {
        return $this->account;
    }


    /**
     * Add records
     *
     * @param Records $records
     * @return Domains
     */
    public function addRecord(Records $records)
    {
        $this->records[] = $records;
    
        return $this;
    }

    /**
     * Remove records
     *
     * @param Records $records
     */
    public function removeRecord(Records $records)
    {
        $this->records->removeElement($records);
    }

    /**
     * Get records
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