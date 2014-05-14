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
 * @package SysEleven\PowerDnsBundle\EventListener
 */
namespace SysEleven\PowerDnsBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Form\Transformer\SoaTransformer;


/**
 * Class RecordListener
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\EventListener
 */
class RecordListener 
{

    /**
     * Creates a snapshot of the loaded data and if the record is of type soa
     * transforms the content record into a soa object
     *
     * @param \SysEleven\PowerDnsBundle\Entity\Records $record
     * @param \Doctrine\ORM\Event\LifecycleEventArgs           $event
     *
     * @return bool
     */
    public function postLoad(Records $record, LifecycleEventArgs $event)
    {
        $keys = array('name','type','content','ttl','prio','managed','looseCheck');

        $clean = array();
        foreach ($keys AS $k) {
            $method = sprintf('get%s',ucfirst($k));
            $clean[$k] = $record->{$method}();
        }
        $record->setCleanData($clean);


        if ($record->getType() == 'SOA') {
            $transformer = new SoaTransformer();
            $record->setContent($transformer->transform($record->getContent()));

        }

        return true;
    }

    /**
     * Sets the current date to the object, and if the record is of type
     * transforms the soa object to a string
     */
    public function prePersist(Records $record, LifecycleEventArgs $event)
    {

        $record->setCreated(new \DateTime());
        $record->setModified(new \DateTime());
        $record->setChangeDate(strtotime('now'));

        if ($record->getType() == 'SOA') {
            $transformer = new SoaTransformer();
            $record->setContent($transformer->reverseTransform($record->getContent()), true);
        }
    }

    /**
     * Retransforms the content to a soa object if applicable
     */
    public function postPersist(Records $record, LifecycleEventArgs $event)
    {
        if ($record->getType() == 'SOA') {
            $transformer = new SoaTransformer();
            $record->setContent($transformer->transform($record->getContent()));
        }
    }

    /**
     * Transforms the soa record to a string and sets the modification date
     * and the changedate
     */
    public function preUpdate(Records $record, PreUpdateEventArgs $event)
    {
        $record->setModified(new \DateTime());
        $record->setChangeDate(strtotime('now'));

        if ($record->getType() == 'SOA') {
            $transformer = new SoaTransformer();
            $record->setContent($transformer->reverseTransform($record->getContent()), true);
        }
    }

    /**
     * Retransforms the content to a soa object if applicable
     */
    public function postUpdate(Records $record, LifecycleEventArgs $event)
    {
        if ($record->getType() == 'SOA') {
            $transformer = new SoaTransformer();
            $record->setContent($transformer->transform($record->getContent()));
        }
    }

}
 