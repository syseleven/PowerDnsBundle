<?php
/**
 * Syseleven PowerDns API
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
namespace SysEleven\PowerDnsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SysEleven\PowerDnsBundle\Entity\Supermasters
 *
 * @ORM\Table(name="supermasters")
 * @ORM\Entity
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
class Supermasters
{
    /**
     * @var string $ip
     *
     * @ORM\Column(name="ip", type="string", length=25, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ip;

    /**
     * @var string $nameserver
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="nameserver", type="string", length=255, nullable=false)
     */
    private $nameserver;

    /**
     * @var string $account
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="account", type="string", length=40, nullable=true)
     */
    private $account;



    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set nameserver
     *
     * @param string $nameserver
     * @return Supermasters
     */
    public function setNameserver($nameserver)
    {
        $this->nameserver = $nameserver;
    
        return $this;
    }

    /**
     * Get nameserver
     *
     * @return string 
     */
    public function getNameserver()
    {
        return $this->nameserver;
    }

    /**
     * Set account
     *
     * @param string $account
     * @return Supermasters
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
     * Set ip
     *
     * @param string $ip
     * @return Supermasters
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }
}