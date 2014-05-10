<?php
/**
 * Syseleven PowerDns API
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
namespace SysEleven\PowerDnsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation Contraints for dns records.
 *
 * @Annotation
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
class Record extends Constraint
{
    public $message = 'The given Record is not Valid';

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'syseleven_validator_record';
    }
}