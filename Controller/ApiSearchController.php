<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 *  @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Controller
 */
namespace SysEleven\PowerDnsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations As Rest;
use SysEleven\PowerDnsBundle\Form\DomainsSearchType;
use SysEleven\PowerDnsBundle\Form\RecordsHistoryQueryType;
use SysEleven\PowerDnsBundle\Form\RecordsSearchType;
use SysEleven\PowerDnsBundle\Lib\ApiController;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\RecordWorkflow;
use SysEleven\PowerDnsBundle\Query\DomainsQuery;
use SysEleven\PowerDnsBundle\Query\RecordsHistoryQuery;
use SysEleven\PowerDnsBundle\Query\RecordsQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Performs searches in the database backend.
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Controller
 * @Route("")
 */
class ApiSearchController extends ApiController
{
    /**
     * Performs a search in the domains and records table. See the documentation for details on the search parameters.
     *
     * @ApiDoc(
     *      description="Searches in the domains table",
     *      input="SysEleven\PowerDnsBundle\Form\DomainsSearchType",
     *      requirements={
     *          {"name" = "_format", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Domains",
     *          "groups"="list"},
     *
     *      parameters={
     *          {"name"="limit", "dataType"="integer", "required"=false, "description"="Number of records to return"},
     *          {"name"="offset", "dataType"="integer", "required"=false, "description"="Record to start with"},
     *          {"name"="order", "dataType"="array", "required"=false, "description"="Sort the result"}
     *      }
     * )
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/search.{_format}", name="syseleven_powerdns_api_search")
     */
    public function searchAction(Request $request)
    {
        $query = new DomainsQuery();
        $form  = $this->createForm(new DomainsSearchType(), $query, array('method' => 'GET'));
        $form->handleRequest($request);

        $filter = $query->toArray();
        $order  = $request->get('order', array());

        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->get('syseleven.pdns.workflow.domains');

        $qb = $workflow->search($filter, $order);

        $limit = $request->get('limit', null);

        if (!is_null($limit) && filter_var($limit, FILTER_VALIDATE_INT)) {
            $offset = $request->get('offset',0);
            $qb->setFirstResult(abs(intval($offset)));
            $qb->setMaxResults(abs($offset));
        }

        $result = $qb->getQuery()->getResult();

        $data = array('status' => 'success', 'data' => $result);

        return $this->returnView($data, 200,array(), array('list'));
    }

    /**
     * Performs a search in the records table. See the documentation for details on the search parameters.
     *
     *
     * @ApiDoc(
     *      description="Searches in the records table",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsSearchType",
     *      requirements={
     *          {"name" = "_format", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Records",
     *          "groups"="list"},
     *
     *      parameters={
     *          {"name"="limit", "dataType"="integer", "required"=false, "description"="Number of records to return"},
     *          {"name"="offset", "dataType"="integer", "required"=false, "description"="Record to start with"},
     *          {"name"="order", "dataType"="array", "required"=false, "description"="Sort the result"}
     *      }
     * )
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/records.{_format}", name="syseleven_powerdns_api_search_records")
     */
    public function recordSearchAction(Request $request)
    {
        $query = new RecordsQuery();
        $form  = $this->createForm(new RecordsSearchType(), $query, array('method' => 'GET'));
        $form->handleRequest($request);

        $filter = $query->toArray();
        $order  = $request->get('order', array());

        /**
         * @type RecordWorkflow $workflow
         */
        $workflow = $this->get('syseleven.pdns.workflow.records');

        $qb = $workflow->search($filter, $order);

        $limit = $request->get('limit', null);

        if (!is_null($limit) && filter_var($limit, FILTER_VALIDATE_INT)) {
            $offset = $request->get('offset',0);
            $qb->setFirstResult(abs(intval($offset)));
            $qb->setMaxResults(abs($offset));
        }

        $result = $qb->getQuery()->getResult();

        $data = array('status' => 'success', 'data' => $result);

        return $this->returnView($data, 200,array(), array('search'));

    }

    /**
     * Performs a search in the records history. See the documentation for details on the search parameters.
     *
     * @ApiDoc(
     *      description="Searches in the records history table",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsHistorySearchType",
     *      requirements={
     *          {"name" = "_format", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\RecordsHistory",
     *          "groups"="compact"},
     *
     *      parameters={
     *          {"name"="limit", "dataType"="integer", "required"=false, "description"="Number of records to return"},
     *          {"name"="offset", "dataType"="integer", "required"=false, "description"="Record to start with"},
     *          {"name"="order", "dataType"="array", "required"=false, "description"="Sort the result"}
     *      }
     * )
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/history.{_format}", name="syseleven_powerdns_api_search_history")
     */
    public function historyAction(Request $request)
    {
        $query = new RecordsHistoryQuery();
        $form  = $this->createForm(new RecordsHistoryQueryType(), $query, array('method' => 'GET'));
        $form->handleRequest($request);

        $filter = $query->toArray();
        $order  = $request->get('order', array());

        /**
         * @type RecordWorkflow $workflow
         */
        $workflow = $this->get('syseleven.pdns.workflow.records');

        $qb = $workflow->searchHistory($filter, $order);

        $limit = $request->get('limit', null);

        if (!is_null($limit) && filter_var($limit, FILTER_VALIDATE_INT)) {
            $offset = $request->get('offset',0);
            $qb->setFirstResult(abs(intval($offset)));
            $qb->setMaxResults(abs($offset));
        }

        $result = $qb->getQuery()->getResult();

        $data = array('status' => 'success', 'data' => $result);

        return $this->returnView($data, 200,array(), array('compact'));
    }

}
 