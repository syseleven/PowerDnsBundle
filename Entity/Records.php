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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use SysEleven\PowerDnsBundle\Form\Transformer\SoaTransformer;
use SysEleven\PowerDnsBundle\Lib\PowerDnsObjectInterface;
use SysEleven\PowerDnsBundle\Validator\Constraints AS SyselevenAssert;
use SysEleven\PowerDnsBundle\Form\Transformer\PtrTransformer;
use SysEleven\PowerDnsBundle\EventListener\RecordListener;

/**
 * SysEleven\PowerDnsBundle\Entity\Records
 *
 * @ORM\Table(name="records")
 * @ORM\Entity
 * @SyselevenAssert\Record
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="SysEleven\PowerDnsBundle\Entity\RecordsRepository")
 * @ORM\EntityListeners({"SysEleven\PowerDnsBundle\EventListener\RecordListener"})
 *
 * @author  M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Entity
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Records implements PowerDnsObjectInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"list", "details","record","search","history"})
     * @Serializer\Expose
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Serializer\Groups({"list", "details","record","search","history"})
     *
     * @Serializer\Expose
     */
    protected $name;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=true)
     * @Serializer\Groups({"list", "details","record","search","history"})
     * @Serializer\Expose
     */
    protected $type;

    /**
     * @var mixed $content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     *
     * @Serializer\Exclude
     */
    protected $content;

    /**
     * @var integer $ttl
     *
     * @ORM\Column(name="ttl", type="integer", nullable=true)
     * @Serializer\Groups({"list", "details","record","search","history"})
     * @Serializer\Expose
     */
    protected $ttl;

    /**
     * @var integer $prio
     *
     * @ORM\Column(name="prio", type="integer", nullable=true)
     * @Serializer\Groups({"list", "details","record","search","history"})
     * @Serializer\Expose
     *
     */
    protected $prio;

    /**
     * @var integer $changeDate
     *
     * @ORM\Column(name="change_date", type="integer", nullable=true)
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $changeDate;

    /**
     * @var integer $managed
     *
     * @ORM\Column(name="managed", type="integer", nullable=true)
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $managed;

    /**
     * @var Domains
     *
     * @ORM\ManyToOne(targetEntity="Domains", inversedBy="records")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     * })
     *
     */
    protected $domain;

    /**
     * @ORM\Column(name="user", type="text", nullable=true, length=100)
     * @var string username
     */
    protected $user;

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
     * @var string $forward_name
     * @Serializer\Accessor(getter="getForwardName")
     * @Serializer\Groups({"list", "details","record","search","history"})
     * @Serializer\Expose
     */
    protected $forward_name;

    /**
     * @var int
     * @Serializer\Accessor(getter="getDomainId")
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $domain_id;

    /**
     * Indicates if a loose hostname check is allowed.
     *
     * @ORM\Column(name="loose_check", type="integer", nullable=true)
     * @var bool
     */
    protected $looseCheck = 0;

    /**
     * @var array
     */
    protected $_cleanData = array();

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
     * @return Records
     */
    public function setName($name)
    {
        if ($this->type == 'PTR') {
            $transformer = new PtrTransformer();
            $name = $transformer->transform($name);
        }


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
        if ($this->type == 'PTR') {
            $transformer = new PtrTransformer();
            $this->name = $transformer->transform($this->name);
        }

        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Records
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
     * Set content
     *
     * @param string $content
     * @param bool   $raw, skips conversion of the record
     *
     * @return Records
     */
    public function setContent($content, $raw = false)
    {

        if($this->type == 'SOA' && $raw === false) {
            $transformer = new SoaTransformer();
            $this->content = $transformer->transform($this->content);
        }

        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content, if type == 'SOA' the content will be returned as an Array
     * representing the SOA values.
     *
     * @param bool $raw
     * @return mixed
     */
    public function getContent($raw = false)
    {
        if($raw === true) {
            return $this->content;
        }

        if($this->type == 'SOA') {
            $transformer = new SoaTransformer();
            $this->content = $transformer->transform($this->content);
        }


        return $this->content;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"list", "details","record","search","history"})
     * @Serializer\SerializedName("content")
     */
    public function serializeContent()
    {
        return $this->getContent();
    }

    /**
     * Set ttl
     *
     * @param integer $ttl
     * @return Records
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    
        return $this;
    }

    /**
     * Get ttl
     *
     * @return integer 
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set prio
     *
     * @param integer $prio
     * @return Records
     */
    public function setPrio($prio)
    {
        $this->prio = $prio;
    
        return $this;
    }

    /**
     * Get prio
     *
     * @return integer 
     */
    public function getPrio()
    {
        return $this->prio;
    }

    /**
     * Set changeDate
     *
     * @param integer $changeDate
     * @return Records
     */
    public function setChangeDate($changeDate)
    {
        $this->changeDate = $changeDate;
    
        return $this;
    }

    /**
     * Get changeDate
     *
     * @return integer 
     */
    public function getChangeDate()
    {
        return $this->changeDate;
    }

    /**
     * Set managed
     *
     * @param integer $managed
     * @return Records
     */
    public function setManaged($managed)
    {
        $this->managed = intval($managed);

        return $this;
    }

    /**
     * Get managed
     *
     * @return integer
     */
    public function getManaged()
    {
        return $this->managed;
    }

    /**
     * Set domain
     *
     * @param Domains $domain
     * @return Records
     */
    public function setDomain(Domains $domain = null)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return \SysEleven\PowerDnsBundle\Entity\Domains
     */
    public function getDomain()
    {
        return $this->domain;
    }

    public function getForwardName()
    {
        if(!in_array($this->getType(),array('PTR','SOA'))) {
            return null;
        }

        if(null == $this->name) {
            return null;
        }

        $tr = new PtrTransformer();

        return $tr->reverseTransform($this->name);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $map = array('name','type','content','ttl','prio','domain');

        $r = array();
        foreach($map AS $k) {
            if($k == 'domain') {
                $r[$k] = $this->domain->getId();
            }
            $r[$k] = $this->$k;
        }

        return $r;
    }

    /**
     * Returns the domain id
     *
     * @return int
     */
    public function getDomainId()
    {
        return $this->getDomain()->getId();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s",$this->getId());
    }

    /**
     * Returns the value of this loose check.
     *
     * @return bool
     */
    public function getLooseCheck()
    {
        return $this->looseCheck;
    }

    /**
     * Sets this->looseCheck to $flag.
     *
     * @param int $flag
     *
     * @return $this
     */
    public function setLooseCheck($flag = 0)
    {
        $this->looseCheck = $flag;

        return $this;
    }



    /**
     * @param null $key
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function getChanges($key = null)
    {
        $keys = array('name','type','content','ttl','prio','managed','looseCheck');

        $changes = array();
        foreach ($keys AS $k) {
            if (!array_key_exists($k, $this->_cleanData)) {
                continue;
            }

            if ($this->$k != $this->_cleanData[$k]) {
                $changes[$k] = array($this->$k,$this->_cleanData[$k]);
            }
        }

        if (is_null($key) || 0 == strlen($key)) {
            if (is_null($this->id)) {
                return array();
            }

            return $changes;
        }

        if (!in_array($key, $keys)) {
            throw new \BadMethodCallException('Unknown change key requested');
        }

        if (array_key_exists($key, $changes)) {
            return $changes[$key];
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function looseCheck()
    {
        return (1 == $this->looseCheck);
    }

    /**
     * @param string $user
     *
     * @return Records
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array $cleanData
     *
     * @return Records
     */
    public function setCleanData($cleanData)
    {
        $this->_cleanData = $cleanData;
        return $this;
    }

    /**
     * @return array
     */
    public function getCleanData()
    {
        return $this->_cleanData;
    }

    /**
     * @param \DateTime $created
     *
     * @return Records
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
     * @return Records
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




}