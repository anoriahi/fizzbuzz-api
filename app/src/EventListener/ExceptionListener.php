<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Convertit les erreurs de l'API en Problem Details.
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -64)]
final readonly class ExceptionListener
{
    public function __construct(
        #[Autowire(param: '%kernel.debug%')] private bool $debug,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        $exception = $event->getThrowable();
        $previous = $exception->getPrevious();

        $isHttp = $exception instanceof HttpExceptionInterface;
        $status = $isHttp ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $headers = $isHttp ? $exception->getHeaders() : [];
        $extensions = [];

        $extra = $exception instanceof ExtraAttributesException ? $exception : $previous;

        if ($extra instanceof ExtraAttributesException) {
            $status = Response::HTTP_BAD_REQUEST;
            $headers = [];
            $title = $this->translator->trans('parameter.unknown', [], 'errors');
            $detail = $extra->getMessage();
            $extensions['unknownParameters'] = $extra->getExtraAttributes();
        } elseif ($previous instanceof ValidationFailedException) {
            $title = $this->translator->trans('validation.failed', [], 'errors');
            $detail = $this->translator->trans('parameter.invalid', [], 'errors');

            $extensions['violations'] = array_map(
                static fn (ConstraintViolationInterface $violation): array => [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ],
                iterator_to_array($previous->getViolations()),
            );
        } else {
            // Erreur HTTP générique : about:blank impose le libellé HTTP standard comme title
            $title = $this->translator->trans(Response::$statusTexts[$status] ?? 'Unknown Error');
            // Pas de détail interne en prod pour les 5xx
            $detail = $status < 500 || $this->debug ? $exception->getMessage() : '';
            if ($this->debug && $status >= 500) {
                $extensions['exception'] = $exception::class;
            }
        }

        $problem = [
            'title' => $title,
            'status' => $status,
            ...('' !== $detail ? ['detail' => $detail] : []),
            ...$extensions,
            'instance' => $request->getPathInfo(),
        ];

        $event->setResponse(new JsonResponse(
            $problem,
            $status,
            ['Content-Type' => 'application/problem+json'] + $headers,
        ));
    }
}
