<?php
/**
 * powerdns-api
 * @author   M. Seifert <m.seifert@syseleven.de>
  * @package SysEleven\PowerDnsBundle\Entity
 */
namespace SysEleven\PowerDnsBundle\Entity;
use Doctrine\ORM\EntityRepository;
use SysEleven\PowerDnsBundle\Lib\PowerDnsObjectInterface;


/**
 * Class RecordsHistoryRepository
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Entity
 */
class RecordsHistoryRepository extends EntityRepository
{
    /**
     * Creates a QueryBuilder object applies the filters and returns it.
     *
     * @param array $filter
     * @param array $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createFilterQuery(array $filter = array(), array $order = array())
    {
        $qb = $this->createQueryBuilder('h');

        $supported = array('domain_id','record_id','search','user','record_type','from','to','action');

        foreach ($filter AS $k => $v) {
            if (!in_array($k, $supported)) {
                continue;
            }

            if (is_null($v)
                || (is_string($v) && 0 == strlen($v))
                || (is_array($v) && 0 == count($v))
                || ($v instanceof \Countable && 0 == count($v))) {

                continue;
            }

            if (in_array($k, array('record_id', 'domain_id'))) {
                $use = array();
                /**
                 * @type PowerDnsObjectInterface $obj
                 */
                foreach ($v AS $obj) {
                    $use[] = $obj->getId();
                }

                if (0 == count($use)) {
                    continue;
                }

                $f = ($k == 'record_id')? 'h.recordId':'h.domainId';

                $qb->andWhere($qb->expr()->in($f,':'.$k))->setParameter($k, $v);
                continue;
            }

            if ($k == 'search') {
                $or = $qb->expr()->orX()->add($qb->expr()->like('h.domainName',':search'))->add($qb->expr()->like('h.content',':search'))->add($qb->expr()->like('h.changes',':search'));
                $qb->andWhere($or)->setParameter('search','%'.$v.'%');
                continue;
            }

            if ($k == 'user') {
                $qb->andWhere($qb->expr()->eq('h.user',':user'))->setParameter('user', $v);
                continue;
            }

            if ($k == 'from') {
                if ($v instanceof \DateTime) {
                    $v = $v->format('Y-m-d 00:00:00');
                }
                $qb->andWhere($qb->expr()->gte('h.created',':from'))->setParameter('from', $v);
                continue;
            }

            if ($k == 'to') {
                if ($v instanceof \DateTime) {
                    $v = $v->format('Y-m-d 00:00:00');
                }
                $qb->andWhere($qb->expr()->lte('h.created',':to'))->setParameter('to', $v);
                continue;
            }
        }

        if (!is_array($order) || 0 == count($order)) {
            $order = array('created' => 'asc');
        }

        $allowed = array('domainName','recordId','created');
        foreach ($order AS $k => $v) {
            if (false === in_array($k,$allowed) && $k != 'search') {
                continue;
            }

            $qb->addOrderBy('h.'.$k,$v);
        }

        return $qb;
    }

}
 