<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\Entity
 */
namespace SysEleven\PowerDnsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation AS Serializer;
 
/**
 * Holds the history for a record entry, Note: due to the fact that the table
 * holds information about deleted object there are no foreign keys to the
 * records table
 *
 *
 * @ORM\Table(name="records_history")
 * @ORM\Entity(repositoryClass="SysEleven\PowerDnsBundle\Entity\RecordsHistoryRepository")
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Entity
 * @Serializer\ExclusionPolicy("all")
 */
class RecordsHistory 
{
    /**
     * @var int
     * @ORM\Column(name="history_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     *
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="history_action", type="string", length=255, nullable=true)
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $action;

    /**
     * @var int
     * @ORM\Column(name="record_id", type="integer", nullable=false)
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $recordId;

    /**
     * @var int
     * @ORM\Column(name="domain_id", type="integer", nullable=false)
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $domainId;


    /**
     * @var int
     * @ORM\Column(name="domain_name", type="integer", nullable=false)
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $domainName;

    /**
     * @var string
     * @ORM\Column(name="history_record_type", type="string", length=255, nullable=true)
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $recordType;

    /**
     * @var array
     * @ORM\Column(name="history_content", type="json_array", nullable=true)
     * @Serializer\Groups({"full"})
     * @Serializer\Expose
     */
    protected $contents;

    /**
     * @var array
     * @ORM\Column(name="history_changes", type="json_array", nullable=true)
     * @Serializer\Groups({"compact"})
     * @Serializer\Expose
     */
    protected $changes;

    /**
     * @var string
     * @ORM\Column(name="history_user", type="string", length=255, nullable=true)
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(name="added", type="datetime", nullable=true, options={"default" = "1970-01-01 00:00:00"})
     * @Serializer\Groups({"compact","full"})
     * @Serializer\Expose
     */
    protected $created;


    /**
     * @param array $changes
     *
     * @return RecordsHistory
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;
        return $this;
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param mixed $contents
     *
     * @return RecordsHistory
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param \DateTime $created
     *
     * @return RecordsHistory
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
     * @param mixed $domainId
     *
     * @return RecordsHistory
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param int $domainName
     *
     * @return RecordsHistory
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        return $this;
    }

    /**
     * @return int
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param int $id
     *
     * @return RecordsHistory
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $recordId
     *
     * @return RecordsHistory
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * @param string $user
     *
     * @return RecordsHistory
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
     * @param string $action
     *
     * @return RecordsHistory
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $recordType
     *
     * @return RecordsHistory
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }
}
 