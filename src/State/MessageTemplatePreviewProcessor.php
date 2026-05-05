<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use PsychedCms\MessageTemplate\Service\MessageTemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * POST /message-templates/{key}/preview — utility endpoint for the admin UI.
 *
 * Reads `key` from the URI variables and a `placeholders` map from the
 * request body. Returns `{ "body": "<resolved>" }` after the renderer
 * substitutes the placeholders.
 *
 * @implements ProcessorInterface<null, JsonResponse>
 */
final class MessageTemplatePreviewProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageTemplateRenderer $renderer,
        private readonly RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $key = $uriVariables['key'] ?? null;
        if (!\is_string($key) || $key === '') {
            throw new NotFoundHttpException('MessageTemplate key missing');
        }

        if (!$this->renderer->exists($key)) {
            throw new NotFoundHttpException(\sprintf('MessageTemplate "%s" not found', $key));
        }

        $request = $this->requestStack->getCurrentRequest();
        $payload = $request instanceof Request ? \json_decode((string) $request->getContent(), true) : null;
        if (!\is_array($payload)) {
            $payload = [];
        }

        /** @var mixed $rawValues */
        $rawValues = $payload['placeholders'] ?? [];
        $values = [];
        if (\is_array($rawValues)) {
            foreach ($rawValues as $name => $value) {
                if (\is_string($name) && (\is_string($value) || \is_int($value) || \is_float($value))) {
                    $values[$name] = (string) $value;
                }
            }
        }

        $body = $this->renderer->render($key, $values, $request?->getLocale());

        return new JsonResponse(['body' => $body]);
    }
}
