<?php
/**
 * Listener for handling Access Denied exceptions.
 */

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AccessDeniedExceptionListener listens for AccessDeniedHttpExceptions and provides a response.
 */
class AccessDeniedExceptionListener
{
    /**
     * @var RequestStack Request stack to get the current request
     */
    private $requestStack;

    /**
     * @var TranslatorInterface Translator for translating messages
     */
    private $translator;

    /**
     * @var SessionInterface Session interface for managing session data
     */
    private $session;

    /**
     * Constructor.
     *
     * @param RequestStack        $requestStack Request stack
     * @param TranslatorInterface $translator   Translator
     * @param SessionInterface    $session      Session interface
     */
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator, SessionInterface $session)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * Handles AccessDeniedHttpException thrown during the kernel execution.
     *
     * @param ExceptionEvent $event The exception event
     */
    public function onKernelException(ExceptionEvent $event): void
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
