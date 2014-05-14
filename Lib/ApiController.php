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
 * @package SysEleven\PowerDnsBundle\Lib
 */
namespace SysEleven\PowerDnsBundle\Lib;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\Serializer\SerializationContext;

/**
 * Class ApiController
 *
 * @author M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Lib
 */
class ApiController extends FOSRestController
{

    /**
     * Creates a new view and sets the serialization context
     */
    protected function view($data = null, $statusCode = null, array $headers = array(), array $serializerGroups = array())
    {
        $view = parent::view($data, $statusCode, $headers);

        $view->setSerializationContext(SerializationContext::create());
        if (0 != count($serializerGroups)) {
            $view->getSerializationContext()->setGroups($serializerGroups);
        }

        return $view;
    }

    /**
     * Creates and handles a new view with the given data
     *
     * @param null  $data
     * @param null  $statusCode
     * @param array $headers
     * @param array $serializerGroups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function returnView($data = null, $statusCode = null, array $headers = array(), array $serializerGroups = array())
    {
        $view = $this->view($data, $statusCode, $headers, $serializerGroups);

        return $this->handleView($view);
    }

}
 