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

use Doctrine\Common\Collections\ArrayCollection;


/**
 * Class QueryAbstract
 *
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */
class QueryAbstract
{
    /**
     * @var array
     */
    protected $blacklist = array();

    /**
     * @param array $blacklist
     *
     * @return QueryAbstract
     */
    public function setBlacklist($blacklist)
    {
        $this->blacklist = $blacklist;
        return $this;
    }

    /**
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }


    /**
     * Creates an array from the properties of the object.
     *
     * @return array
     */
    public function toArray()
    {
        $ref = new \ReflectionObject($this);
        $r = array();

        $blacklist = $this->getBlacklist();
        $blacklist[] = 'blacklist';

        foreach ($ref->getProperties() AS $property) {
            $n = $property->getName();

            if (in_array($n, $blacklist)) {
                continue;
            }

            if (is_array($this->$n) || $this->$n instanceof \Countable) {
                if (0 == count($this->$n)) {
                    continue;
                }
            }

            if (!is_null($this->$n)) {
                $r[$n] = $this->$n;
            }
        }

        return $r;
    }
}
