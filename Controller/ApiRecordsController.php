<?php
/**
 * powerdns-api
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
     * @param Request $request
     * @param         $domain
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
     *
     * @param int     $record
     * @param         $domain
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
     * Creates a new Record in the backend
     *
     * @param Request $request
     * @param         $domain
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
     * @param Request $request
     * @param         $domain
     * @param         $record
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
     * Deletes the record in the backend.
     *
     * @param         $domain
     * @param         $record
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
 