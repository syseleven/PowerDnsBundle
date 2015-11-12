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
 * @package SysEleven\PowerDnsBundle\Form\Transformer
 */
namespace SysEleven\PowerDnsBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Takes a given ip and transforms it into a form suitable for a PTR record
 * and vice versa
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form\Transformer
 *
 */
class PtrTransformer implements DataTransformerInterface
{

    /**
     * Transforms an ip address into a PTR record format.
     *
     * @param mixed $value The value in the original representation
     * @return mixed The value in the transformed representation
     */
    public function transform($value)
    {
        // Dont do anything if $value is not an ip
        if(!filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }

        if(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $rev = implode('.',array_reverse(explode('.',$value)));

            return $rev.'.in-addr.arpa';
        }

        if(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {

            $rev = implode('.',str_split(strrev(str_replace(':','',$this->_expandIpV6($value)))));

            return $rev.'.ip6.arpa';
        }

        return $value;

    }

    /**
     * Reverse transforms an PTR record in the form xxxxxxx.in-addr.arpa or
     * xxxxxxx.ip6.arpa into a normal ip Address.
     *
     * @param mixed $value The value in the transformed representation
     * @return mixed The value in the original representation
     *
     */
    public function reverseTransform($value)
    {
        if(false == strpos($value,'in-addr.arpa') && false == strpos($value, 'ip6.arpa')) {
            return $value;
        }

        if(strpos($value,'ip6.arpa')) {
            $value = strrev(str_replace('.ip6.arpa','',$value));
            $value = str_replace('.','',$value);
            $arr   = str_split($value,4);

            return implode(':',$arr);
        }

        if(strpos($value,'in-addr.arpa')) {
            $value = str_replace('.in-addr.arpa','',$value);
            $value = array_reverse(explode('.',$value));

            return implode('.',$value);
        }

        return $value;
    }

    /**
     * Expands a given ipv6 address in a full length format
     *
     * @param $ip
     * @return string
     */
    protected function _expandIpV6($ip)
    {
        if(!filter_var($ip, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) {
            return $ip;
        }

        $hex = unpack("H*hex", inet_pton($ip));
        $ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);

        return $ip;
    }
}