<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\Lib
 */
namespace SysEleven\PowerDnsBundle\Lib;

use JMS\Serializer\Annotation AS Serializer;
 
/**
 * Class Soa
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 * @Serializer\ExclusionPolicy("all")
 */
class Soa 
{
    /**
     * Primary ns of the zone
     *
     * @var string
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $primary;

    /**
     * Email of the hostmaster
     *
     * @var string
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $hostmaster;

    /**
     * Serialnumber of the Zone
     *
     * @var int
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $serial = 0;

    /**
     * Refresh interval in seconds
     *
     * @var int
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $refresh = 10800;

    /**
     * Retry interval in seconds
     *
     * @var int
     */
    protected $retry = 3600;

    /**
     * Expire interval in seconds
     *
     * @var int
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $expire = 604800;

    /**
     * Default TTL in seconds
     *
     * @var int
     * @Serializer\Groups({"list", "details","record","search"})
     * @Serializer\Expose
     */
    protected $default_ttl = 3600;

    /**
     * @param int $default_ttl
     *
     * @return Soa
     *
     */
    public function setDefaultTtl($default_ttl)
    {
        $this->default_ttl = $default_ttl;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultTtl()
    {
        return $this->default_ttl;
    }

    /**
     * @param int $expire
     *
     * @return Soa
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param string $hostmaster
     *
     * @return Soa
     */
    public function setHostmaster($hostmaster)
    {
        $this->hostmaster = $hostmaster;
        return $this;
    }

    /**
     * @return string
     */
    public function getHostmaster()
    {
        return $this->hostmaster;
    }

    /**
     * @param string $primary
     *
     * @return Soa
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @param int $refresh
     *
     * @return Soa
     */
    public function setRefresh($refresh)
    {
        $this->refresh = $refresh;
        return $this;
    }

    /**
     * @return int
     */
    public function getRefresh()
    {
        return $this->refresh;
    }

    /**
     * @param int $retry
     *
     * @return Soa
     */
    public function setRetry($retry)
    {
        $this->retry = $retry;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetry()
    {
        return $this->retry;
    }

    /**
     * @param int $serial
     *
     * @return Soa
     */
    public function setSerial($serial)
    {
        if (is_null($serial)) {
            $serial = strtotime('now');
        }

        $this->serial = $serial;
        return $this;
    }

    /**
     * @return int
     */
    public function getSerial()
    {
        return $this->serial;
    }




    /**
     * Returns the record as a string
     *
     * @return string
     */
    public function toString()
    {
        $map = array('primary','hostmaster',
                     'serial','refresh','retry','expire','default_ttl');

        $r = array();
        foreach ($map AS $k) {
            $r[$k] = $this->$k;;
        }

        return implode(' ',$r);
    }

    /**
     * Parses a soa string and fills the object
     *
     * @param $value
     *
     * @return array
     */
    public function fromString($value)
    {
        $map = array('primary','hostmaster',
                     'serial','refresh','retry','expire','default_ttl');

        $cnt = explode(' ',$value);
        $r = array();
        foreach($map AS $idx => $v) {
            if(in_array($v, array('primary','hostmaster'))) {
                $this->$v = (isset($cnt[$idx]))? $cnt[$idx]:'';
                continue;
            }
            $this->$v = (isset($cnt[$idx]) && 0 != strlen($cnt[$idx]))? intval($cnt[$idx]):'';
        }

        return $r;
    }

    public function fromArray($value)
    {
        $map = array('primary','hostmaster',
                    'serial','refresh','retry','expire','default_ttl');

        if (!is_array($value)) {
            return false;
        }

        foreach ($map AS $k) {
            if (!isset($value[$k])) {
                continue;
            }

            if(in_array($k, array('primary','hostmaster'))) {
                $this->$k = $value[$k];
                continue;
            }

            $this->$k = (0 == strlen($value[$k]))? '':intval($value[$k]);
        }

        return true;
    }
}
 