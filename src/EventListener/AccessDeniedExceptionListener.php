<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;


class AccessDeniedExceptionListener
{
    private $requestStack;
    private $translator;
    private $session;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, SessionInterface $session)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedHttpException) {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                return;
            }

            $this->session->getFlashBag()->add(
                'warning',
                $this->translator->trans('message.access_denied')
            );

            $response = new Response(
                $request->getContent(),
                Response::HTTP_FORBIDDEN
            );

            $event->setResponse($response);
        }
    }
}
