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
 * @author   M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Controller
 */
namespace SysEleven\PowerDnsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations AS Rest;
use SysEleven\PowerDnsBundle\Entity\Records;
use SysEleven\PowerDnsBundle\Form\RecordsHistoryQueryType;
use SysEleven\PowerDnsBundle\Form\RecordsSearchType;
use SysEleven\PowerDnsBundle\Form\RecordsType;
use SysEleven\PowerDnsBundle\Lib\ApiController;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException;
use SysEleven\PowerDnsBundle\Lib\Exceptions\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SysEleven\PowerDnsBundle\Lib\RecordWorkflow;
use SysEleven\PowerDnsBundle\Lib\Tools;
use SysEleven\PowerDnsBundle\Query\RecordsHistoryQuery;
use SysEleven\PowerDnsBundle\Query\RecordsQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Provides API calls for listing, displaying, creating, manipulating and
 * deleting the records of the given zone.
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Controller
 * @Route("/domains/{domain}")
 */
class ApiRecordsController extends ApiController
{

    /**
     * Returns a list of records for the given domain.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Returns a list of records for the given domain",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsSearchType",
     *      requirements={
     *          {"name" = "domain", "dataType" = "integer", "description" = "id of the domain to query"},
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
     * @param int     $domain
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/records.{_format}", name="syseleven_powerdns_api_domains_records")
     */
    public function indexAction(Request $request, $domain)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($domain);

            $query = new RecordsQuery();

            $form  = $this->createForm(new RecordsSearchType(), $query);
            $form->handleRequest($request);

            $query->setDomain($domainObj);

            $filter = $query->toArray();

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            $qb = $recordWorkflow->search($filter, array('type' => 'ASC', 'name' => 'ASC'));

            $result = $qb->getQuery()->getResult();

            $data = array('status' => 'success', 'data' => $result);

            return $this->returnView($data, 200, array(), array('list'));


        } catch (NotFoundException $e) {

            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
            return $this->returnView($data, 200, array());
        }
    }

    /**
     * Returns the details of the given record, Note: the records of a domain
     * are also returned in the detail view of the domain.
     *
     * @ApiDoc(
     *      description="Shows the details of the record specified by domain and record",
     *      requirements={
     *          {"name" = "domain", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "record", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the record"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Domains",
     *          "groups"="details"
     *      }
     * )
     *
     * @param int     $domain
     * @param int     $record
     *
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/records/{record}.{_format}", name="syseleven_powerdns_api_domains_records_show")
     */
    public function showAction($domain, $record)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($domain);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            /**
             * @type Records $recordObj
             */
            $recordObj = $recordWorkflow->get($record);

            if ($recordObj->getDomain() != $domainObj) {
                throw new NotFoundException('Cannot find record with id: '.$record.' in domain: '.$domain);
            }

            $data = array('status' => 'success', 'data' => $recordObj);

            return $this->returnView($data, 200, array(), array('details'));


        } catch (NotFoundException $e) {

            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
            return $this->returnView($data, 200, array(), array('list'));
        }
    }


    /**
     * Creates a new record in the given domain. Note when creating a new
     * record the serial of the domain will automatically updated.
     *
     * @ApiDoc(
     *      description="Creates a new record",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsType",
     *      requirements={
     *          {"name" = "domain", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Records",
     *          "groups"="details"
     *      }
     * )
     *
     * @param Request $request
     * @param int     $domain
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Post("/records.{_format}", name="syseleven_powerdns_api_domains_records_create")
     */
    public function createAction(Request $request, $domain)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($domain);

            $recordObj = new Records();
            $recordObj->setDomain($domainObj);
            $form = $this->createForm(new RecordsType(), $recordObj, array('method' => 'POST'));
            $form->remove('domain'); // Remove domain from form
            $form->handleRequest($request, true);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            $force = $request->get('force',false);

            /**
             * @type Records $recordObj
             */
            $recordObj = $recordWorkflow->create($recordObj, array(), $force);

            return $this->redirect($this->generateUrl('syseleven_powerdns_api_domains_records_show', array('domain' => $domainObj->getId(), 'record' => $recordObj->getId(),'_format' => $request->getRequestFormat()), 201));


        } catch (NotFoundException $e) {
            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
            return $this->returnView($data, 200);

        } catch (ValidationException $ve) {
            $data = array('status' => 'error', 'errors' => Tools::prepareSymfonyErrorArray($ve->getErrors()));
            return $this->returnView($data, 200);
        }
    }

    /**
     * Updates the given record with the submitted data.
     *
     * @ApiDoc(
     *      description="Updates the given record",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsType",
     *      requirements={
     *          {"name" = "domain", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "record", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the record to update"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      },
     *
     *      output={
     *          "class"="SysEleven\PowerDnsBundle\Entity\Records",
     *          "groups"="details"
     *      }
     * )
     *
     * @param Request $request
     * @param int     $domain
     * @param int     $record
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Rest\Put("/records/{record}.{_format}", name="syseleven_powerdns_api_domains_records_update")
     * @throws \SysEleven\PowerDnsBundle\Lib\Exceptions\NotFoundException
     */
    public function updateAction(Request $request, $domain, $record)
    {

        try {
            /**
            * @type DomainWorkflow $workflow
            */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($domain);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            /**
             * @type Records $recordObj
             */
            $recordObj = $recordWorkflow->get($record);

            if ($recordObj->getDomain() != $domainObj) {
                throw new NotFoundException('Cannot find record with id: '.$record.' in domain: '.$domain);
            }

            $form = $this->createForm(new RecordsType(), $recordObj, array('method' => 'PUT'));
            $form->remove('domain');
            $form->submit($request, true);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            $force = $request->get('force',false);

            /**
             * @type Records $recordObj
             */
            $recordObj = $recordWorkflow->update($recordObj, array(), $force);

            $data = array('status' => 'success', 'data' => $recordObj);
            return $this->returnView($data, 200, array(), array('details'));
        } catch (NotFoundException $e) {
            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
            return $this->returnView($data, 200);
        } catch (ValidationException $ve) {
            $data = array('status' => 'error', 'errors' => Tools::prepareSymfonyErrorArray($ve->getErrors()));
            return $this->returnView($data, 200);
        }
    }

    /**
     * Deletes the record in the backend.
     *
     * @ApiDoc(
     *      description="deletes the given record",
     *      requirements={
     *          {"name" = "domain", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "record", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the record to delete"},
     *          {"name" = "_format", "dataType" = "string", "pattern" = "(json|xml)", "description" = "Output Format"}
     *      }
     * )
     *
     * @param int $domain
     * @param int $record
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Delete("/records/{record}.{_format}", name="syseleven_powerdns_api_domains_records_delete")
     */
    public function deleteAction($domain, $record)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($domain);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            /**
             * @type Records $recordObj
             */
            $recordObj = $recordWorkflow->get($record);

            if ($recordObj->getDomain() != $domainObj) {
                throw new NotFoundException('Cannot find record with id: '.$record.' in domain: '.$domain);
            }

            $recordWorkflow->delete($recordObj);


            $data = array('status' => 'success', 'data' => array('id' => 'deleted'));

            return $this->returnView($data, 200);

        } catch (NotFoundException $e) {
            $data = array('status' => 'error', 'errors' => array('id' => 'Not found'));
            return $this->returnView($data, 200);

        }
    }

    /**
     * Returns the history of the given record
     *
     * @ApiDoc(
     *      description="displays the changes of the given record",
     *      input="SysEleven\PowerDnsBundle\Form\RecordsHistoryQueryType",
     *      requirements={
     *          {"name" = "domain", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the domain"},
     *          {"name" = "record", "dataType" = "integer", "requirement" = "\d+", "description" = "Id of the record"},
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
     * @param Request $request
     * @param         $domain
     * @param         $record
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Rest\Get("/records/{record}/history.{_format}", name="syseleven_powerdns_api_domains_records_history")
     */
    public function historyAction(Request $request, $domain, $record)
    {
        try {
            /**
             * @type DomainWorkflow $workflow
             */
            $workflow = $this->get('syseleven.pdns.workflow.domains');

            $domainObj = $workflow->get($domain);

            /**
             * @type RecordWorkflow $recordWorkflow
             */
            $recordWorkflow = $this->get('syseleven.pdns.workflow.records');

            /**
             * @type Records $recordObj
             */
            $recordObj = $recordWorkflow->get($record);

            if ($recordObj->getDomain() != $domainObj) {
                throw new NotFoundException('Cannot find record with id: '.$record.' in domain: '.$domain);
            }

            $query = new RecordsHistoryQuery();
            $form  = $this->createForm(new RecordsHistoryQueryType(), $query);
            $form->handleRequest($request);

            $query->setDomainId(array($domainObj));
            $query->setRecordId(array($recordObj));

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
}
 