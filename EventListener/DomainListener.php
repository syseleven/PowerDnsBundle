<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\EventListener
 */
namespace SysEleven\PowerDnsBundle\EventListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Form\Transformer\SoaTransformer;


/**
 * EventListener for domains table
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\EventListener
 */
class DomainListener
{
    /**
     * Sets the current date to the object
     */
    public function prePersist(Domains $domain, LifecycleEventArgs $event)
    {
        $domain->setCreated(new \DateTime());
        $domain->setModified(new \DateTime());
    }


    /**
     * sets the modification date
     */
    public function preUpdate(Domains $domain, PreUpdateEventArgs $event)
    {
        $domain->setModified(new \DateTime());

        if ($event->hasChangedField('name')) {
            $domain->setNeedsSoaUpdate(true);
        }

        return true;
    }
}
 