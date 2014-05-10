<?php
/**
 * Syseleven PowerDns API
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
namespace SysEleven\PowerDnsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use SysEleven\PowerDnsBundle\Form\Transformer\PtrTransformer;

/**
 * Repository Class PowerDns Domains.
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package syseleven_powerdns
 * @subpackage library
 */
class DomainsRepository extends EntityRepository
{
    /**
     * Returns a list of domains/zones filtered by $filter and optionally
     * ordered by $order.
     * <code>
     *     filter can contain one or more of:
     *     - id mixed one or more domain ids
     *     - name string name of a domain exact match
     *     - search string searches in name, id, type, master, account
     *     - type mixed one or more of (NOTIFY,MASTER,SLAVE,SUPERSLAVE)
     *     - master string exact match
     *     - account string exact match
     *     order can contain one or more of:
     *     - id, name. search, type, master, account, last_check,
     *       notified_serial
     *       the format is: $order[<keyname>] = DESC|ASC
     * </code>
     *
     * @param array $filter
     * @param array $order
     * @return array
     */
    public function findByFilter(array $filter = array(), array $order = array())
    {
        $qb = $this->createFilterQuery($filter, $order);

        return $qb->getQuery()->getResult();
    }

    /**
     * Creates a domain query and adds the given filter conditions.
     *
     * @param array $filter
     * @param array $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createFilterQuery(array $filter = array(), array $order = array())
    {
        $qb = $this->createQueryBuilder('d');

        $allowed = array('id','name','search','type','master','account');

        foreach($filter AS $k => $v) {
            if(!in_array($k, $allowed)) {
                continue;
            }

            if(!is_array($v) &&  0 == strlen($v)) {
                continue;
            }

            if(is_array($v) && 0 == count($v)) {
                continue;
            }

            if($k == 'search') {

                $qb->andWhere($qb->expr()
                                    ->orX()->add($qb->expr()->like('d.name',':search'))
                                            ->add($qb->expr()->like('d.master',':search'))
                                            ->add($qb->expr()->like('d.type',':search'))
                                            ->add($qb->expr()->like('d.account',':search')))
                    ->setParameter('search','%'.$v.'%');
                continue;
            }

            if($k == 'type') {
                if(!is_array($v)) {
                    $v = array($v);
                }

                $use = array();
                foreach($v AS $vv) {
                    if(!in_array(strtoupper($vv), array('NATIVE','MASTER','SLAVE','SUPERSLAVE'))) {
                        continue;
                    }
                    $use[] = $vv;
                }

                if(0 == count($use)) {
                    continue;
                }

                $qb->andWhere($qb->expr()->in('d.type',':type'))
                    ->setParameter('type',$use);
                continue;
            }

            if($k == 'id') {
                if(!is_array($v)) {
                    $v = array($v);
                }
                $intOptions = array(
                    "options"=>
                    array("min_range"=>0,'max_range' => 9999999999));
                $use = array();
                foreach($v AS $vv) {
                    if(!filter_var($vv, FILTER_VALIDATE_INT,$intOptions)) {
                        continue;
                    }

                    $use[] = $vv;
                }

                if(0 == count($use)) {
                    continue;
                }

                $qb->andWhere($qb->expr()->in('d.id',':id'))
                    ->setParameter('id',$use);

                continue;
            }

            $qb->andWhere($qb->expr()->eq('d.'.$k,':'.$k))->setParameter($k,$v);
        }

        if (!is_array($order) || 0 == count($order)) {
            $order = array('name' => 'asc');
        }

        foreach ($order AS $k => $v) {
            if (false === in_array($k,$allowed) && $k != 'search') {
                continue;
            }

            $qb->addOrderBy('d.'.$k,$v);
        }

        return $qb;
    }

    /**
     * Returns a list of domains/zones filtered by $filter and optionally
     * ordered by $order. In addition to the domains relation the records are
     * also queried
     * <code>
     *     filter can contain one or more of:
     *     - id mixed one or more domain ids
     *     - name string name of a domain exact match
     *     - search string searches in name, id, type, master, account,
     *          records.name, records.content
     *     - type mixed one or more of (NOTIFY,MASTER,SLAVE,SUPERSLAVE)
     *     - master string exact match
     *     - account string exact match
     *     order can contain one or more of:
     *     - id, name. search, type, master, account, last_check,
     *       notified_serial
     *       the format is: $order[<keyname>] = DESC|ASC
     * </code>
     *
     * @param array $filter
     * @param array $order
     * @return array
     */
    public function findBySearch(array $filter = array(), array $order = array())
    {
        $qb = $this->createSearchQuery($filter, $order);
        return $qb->getQuery()->getResult();
    }


    /**
     * @param array $filter
     * @param array $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createSearchQuery(array $filter = array(), array $order = array())
    {
        $qb = $this->createQueryBuilder('d');
        $qb->leftJoin('d.records', 'r');

        $allowed = array('id','name','search','type','master','account');
        $add = $parameters = array();

        foreach($filter AS $k => $v) {
            if(!in_array($k, $allowed)) {
                continue;
            }

            if(!is_array($v) &&  0 == strlen($v)) {
                continue;
            }

            if(is_array($v) && 0 == count($v)) {
                continue;
            }

            if($k == 'search') {

                $fields = array('d.name','d.master','d.type','r.name','r.content');

                $or = $qb->expr()->orX();
                foreach ($fields AS $field) {
                    $or->add($qb->expr()->like($field, ':search'));
                }

                $or->add($qb->expr()->like($qb->expr()->concat('r.name','d.name'),':search'));
                $or->add($qb->expr()->like('r.name',':search_transformed'));

                $transformer = new PtrTransformer();;
                $searchTransformed = $transformer->transform($v);

                $qb->andWhere($or)->setParameter('search','%'.$v.'%')
                                  ->setParameter('search_transformed','%'.$searchTransformed.'%');
                continue;
            }

            if($k == 'type') {
                if(!is_array($v)) {
                    $v = array($v);
                }

                $use = array();
                foreach($v AS $vv) {
                    if(!in_array(strtoupper($vv), array('NATIVE','MASTER','SLAVE','SUPERSLAVE'))) {
                        continue;
                    }
                    $use[] = $vv;
                }

                if(0 == count($use)) {
                    continue;
                }

                $qb->andWhere($qb->expr()->in('d.type',':type'))->setParameter('type', $use);
                continue;
            }

            if($k == 'id') {
                if(!is_array($v)) {
                    $v = array($v);
                }
                $intOptions = array(
                    "options"=>
                    array("min_range"=>0,'max_range' => 9999999999));
                $use = array();
                foreach($v AS $vv) {
                    if(!filter_var($vv, FILTER_VALIDATE_INT,$intOptions)) {
                        continue;
                    }

                    $use[] = $vv;
                }

                if(0 == count($use)) {
                    continue;
                }

                $qb->andWhere($qb->expr()->in('d.id',':id'))->setParameter('id', $use);
                continue;
            }

            $qb->andWhere($qb->expr()->eq('d.'.$k,':'.$k))->setParameter($k,$v);
        }

        if (!is_array($order) || 0 == count($order)) {
            $order = array('name' => 'asc');
        }

        foreach ($order AS $k => $v) {
            if (false === in_array($k,$allowed) && $k != 'search') {
                continue;
            }

            $qb->addOrderBy('d.'.$k,$v);
        }

        return $qb;
    }

}