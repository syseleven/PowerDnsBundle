<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Lib\Exceptions;


/**
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib\Exceptions
 */
class NotFoundException extends \Exception
{
    protected $code = 404;
}