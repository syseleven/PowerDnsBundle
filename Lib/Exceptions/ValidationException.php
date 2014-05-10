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
class ValidationException extends \Exception
{

    /**
     * @var array
     */
    public $errors = array();

    /**
     * @param string $message
     * @param array $errors
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message = '', $errors = array(), $code = 409, \Exception $previous = null )
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors = array())
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addError($key, $value)
    {
        $this->errors[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function removeError($key)
    {
        if (!array_key_exists($key, $this->errors)) {
            return $this;
        }
        unset($this->errors[$key]);

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasError($key)
    {
        if (!array_key_exists($key, $this->errors) || is_null($this->errors[$key])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getError($key)
    {
        if (!$this->hasError($key)) {
            return false;
        }

        return $this->errors[$key];
    }
}