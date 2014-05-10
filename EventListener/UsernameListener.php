<?php

namespace SysEleven\PowerDnsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SysEleven\PowerDnsBundle\Lib\DomainWorkflow;
use SysEleven\PowerDnsBundle\Lib\RecordWorkflow;

/**
 * LoggableListener
 *
 * @author Markus Seifert <m.seifert@syseleven.de>
 */
class UsernameListener implements EventSubscriberInterface
{
    private $securityContext;

    /**
     * @var DomainWorkflow
     */
    private $domainWorkflow;

    /**
     * @var RecordWorkflow
     */
    private $recordWorkflow;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {

        $this->container        = $container;
        $this->securityContext  = $container->get('security.context');
        $this->domainWorkflow   = $container->get('syseleven.pdns.workflow.domains');
        $this->recordWorkflow   = $container->get('syseleven.pdns.workflow.records');

    }

    /**
     * Set the username from the security context by listening on core.request
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $this->container->get('request');

        $username = $request->get('_username','unknown');

        if (null === $this->securityContext) {
            $this->domainWorkflow->setUsername($username);
            $this->recordWorkflow->setUsername($username);

            return;
        }

        $token = $this->securityContext->getToken();
        if (null !== $token && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $username = $token->getUsername();

            if(array_key_exists('_username',$_REQUEST) && 0 != strlen($_REQUEST['_username'])) {
                $username = $username.' ('.$_REQUEST['_username'].')';
            }

            $this->domainWorkflow->setUsername($username);
            $this->recordWorkflow->setUsername($username);

            return;
        }
        $this->domainWorkflow->setUsername($username);
        $this->recordWorkflow->setUsername($username);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
