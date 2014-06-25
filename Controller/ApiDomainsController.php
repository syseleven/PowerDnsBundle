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

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations AS Rest;
use SysEleven\PowerDnsBundle\Entity\Domains;
use SysEleven\PowerDnsBundle\Form\DomainsSearchType;
use SysEleven\PowerDnsBundle\Form\DomainsType;
use SysEleven\PowerDnsBundle\Form\RecordsHistoryQueryType;
use SysEleven\PowerDnsBundle\Lib\ApiController;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;
use SysEleven\PowerDnsBundle\Lib\RecordWorkflow;
use SysEleven\PowerDnsBundle\Lib\Tools;
use SysEleven\PowerDnsBundle\Query\DomainsQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SysEleven\PowerDnsBundle\Query\RecordsHistoryQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


/**
 * API Controller for handling domain CRUD operations.
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Controller
 * @Route("/domains")
 */
class ApiDomainsController extends ApiController
{
    /**
     * Returns a list of domains, the list can optionally filtered by $filter
     * and ordered by $order, by default all records found are returned to
     * limit the result use the $limit and $offset parameters.
     * See the documentation for details on the search parameters.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a list of domains",
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
     *
     * @Rest\Get(".{_format}", name="syseleven_powerdns_api_domains")
     */
    public function indexAction(Request $request)
    {
        $query = new DomainsQuery();
        $form  = $this->createForm(new DomainsSearchType(), $query, array('method' => 'GET'));
        $form->handleRequest($request);

        $query = $form->getData();

        $order = $request->get('order', array());

        /**
         * @type DomainWorkflow $workflow
         */
        $workflow = $this->get('syseleven.pdns.workflow.domains');

        $qb = $workflow->search($query->toArray(), $order);

        $limit = $request->get('limit', null);

        if (!is_null($limit) && filter_var($limit, FILTER_VALIDATE_INT)) {
            $offset = $request->get('offset',0);
            $qb->setFirstResult(abs(intval($offset)));
            $qb->setMaxResults(abs($offset));
        }

        $result = $qb->getQuery()->getResult();

        $data = array('status' => 'success', 'data' => $result);


        return $this->returnView($data, 200, array(), array('list'));
    }

    /**
     * Shows the details of the domain specified by $id.
     *
     * @ApiDoc(
     *      description="Shows a single domain",
     *      requirements={
     *          {"name" = "id", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Domains",
     *          "groups"="details"
     *      }
     * )
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Get("/{id}.{_format}", name="syseleven_powerdns_api_domains_show")
     */
    public function showAction($id)
    {
        try {
            /**
            * @type DomainWorkflow $workflow
            */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($id);
            $data = array('status' => 'success', 'data' => $domainObj);

        } catch (NotFoundException $e) {

            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
        }

        return $this->returnView($data, 200, array(), array('details'));
    }

    /**
     * Creates a new domain with the given data.
     *
     * When creating a new domain, a new SOA record will be created also.
     * See the documentation for details on the parameters.
     *
     * @ApiDoc(
     *      description="Creates a new domain",
     *      input="SysEleven\PowerDnsBundle\Form\DomainsType",
     *      requirements={
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Domains",
     *          "groups"="details"
     *      }
     * )
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Post(".{_format}", name="syseleven_powerdns_api_domains_create")
     */
    public function createAction(Request $request)
    {
        try {

            $domainObj = new Domains();
            $form = $this->createForm(new DomainsType(),$domainObj, array('method' => 'POST'));
            $form->handleRequest($request);

            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->create($domainObj);

            return $this->redirect($this->generateUrl('syseleven_powerdns_api_domains_show', array('id' => $domainObj->getId(),'_format' => $request->getRequestFormat()), 201));


        } catch (ValidationException $ve) {

            $data = array('status' => 'error',
                          'errors' => Tools::prepareSymfonyErrorArray($ve->getErrors()));
        }

        return $this->returnView($data, 201, array(), array('details'));
    }

    /**
     * Updates the domain specified by domain, despite the fact that PUT is
     * used it performs a Patch Operation. See the documentation for details
     * on the parameters.
     *
     * @ApiDoc(
     *      description="Updates the given domain object",
     *      input="SysEleven\PowerDnsBundle\Form\DomainsType",
     *      requirements={
     *          {"name" = "id", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Domains",
     *          "groups"="details"
     *      }
     * )
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Put("/{id}.{_format}", name="syseleven_powerdns_api_domains_update")
     */
    public function updateAction(Request $request, $id)
    {
        try {

            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');
            $domainObj = $workflow->get($id);
            $form = $this->createForm(new DomainsType(),$domainObj, array('method' => 'PUT','error_bubbling' => true));
            $form->submit($request, false);

            $domainObj = $workflow->update($domainObj);

            $data = array('status' => 'success', 'data' => $domainObj);

        } catch (NotFoundException $nf) {

            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));

        } catch (ValidationException $ve) {

            $data = array('status' => 'error',
                          'errors' => Tools::prepareSymfonyErrorArray($ve->getErrors()));
        }

        return $this->returnView($data, 200, array(), array('details'));
    }

    /**
     * Displays the record changes of the given domain. By default all record
     * changes are returned, to limit the result use $limit and $offset.
     *
     * @ApiDoc(
     *      description="displays the history of the domain records",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsHistoryQueryType",
     *      requirements={
     *          {"name" = "id", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\RecordsHistory",
     *          "groups"="compact"
     *      },
     *      parameters={
     *          {"name"="limit", "dataType"="integer", "required"=false, "description"="Number of records to return"},
     *          {"name"="offset", "dataType"="integer", "required"=false, "description"="Record to start with"},
     *          {"name"="order", "dataType"="array", "required"=false, "description"="Sort the result"}
     *      }
     * )
     *
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param                                           $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/{id}/history.{_format}", name="syseleven_powerdns_api_domains_history")
     */
    public function historyAction(Request $request, $id)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($id);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');


            $query = new RecordsHistoryQuery();
            $form  = $this->createForm(new RecordsHistoryQueryType(), $query);
            $form->handleRequest($request);

            $query->setDomainId(array($domainObj));

            $filter = $query->toArray();

            $qb = $recordWorkflow->searchHistory($filter);

            $limit = $request->get('limit', null);

            if (!is_null($limit) && filter_var($limit, FILTER_VALIDATE_INT)) {
                $offset = $request->get('offset', 0);
                $qb->setMaxResults(abs($limit));
                $qb->setFirstResult(abs($offset));
            }

            $result = $qb->getQuery()->getResult();


            $data = array('status' => 'success', 'data' => $result);

            return $this->returnView($data, 200, array(), array("compact"));

        } catch (NotFoundException $e) {
            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
            return $this->returnView($data, 200);

        }
    }

    /**
     * Removes the domain from the backend.
     *
     * @ApiDoc(
     *      description="deletes the given domain",
     *      requirements={
     *          {"name" = "id", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      }
     * )
     *
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Rest\Delete("/{id}.{_format}", name="syseleven_powerdns_api_domains_delete")
     */
    public function deleteAction($id)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($id);

            $workflow->delete($domainObj);

            $data = array('status' => 'success', 'data' => array('deleted' => true));

        } catch (NotFoundException $e) {

            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
        }

        return $this->returnView($data, 200);
    }
}
 