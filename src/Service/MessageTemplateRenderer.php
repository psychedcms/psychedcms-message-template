<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\Service;

use Gedmo\Translatable\TranslatableListener;
use PsychedCms\MessageTemplate\Entity\MessageTemplate;
use PsychedCms\MessageTemplate\Exception\MessageTemplateNotFoundException;
use PsychedCms\MessageTemplate\Repository\MessageTemplateRepository;

/**
 * Resolves a MessageTemplate body by substituting `{placeholder}` markers with
 * caller-provided values. The package owns no semantic — the caller computes
 * what each placeholder should be (e.g. `{stage_suffix}` will be empty when
 * the festival has only one stage; that decision is made upstream).
 */
final class MessageTemplateRenderer
{
    public function __construct(
        private readonly MessageTemplateRepository $repository,
        private readonly TranslatableListener $translatableListener,
    ) {}

    /**
     * @param array<string, string> $values placeholder name (without braces) → replacement
     */
    public function render(string $key, array $values, ?string $locale = null): string
    {
        $template = $this->loadTemplate($key, $locale);

        $body = $template->getBody();
        foreach ($values as $name => $value) {
            $body = \str_replace('{' . $name . '}', $value, $body);
        }

        return $body;
    }

    public function exists(string $key): bool
    {
        return $this->repository->findByKey($key) !== null;
    }

    private function loadTemplate(string $key, ?string $locale): MessageTemplate
    {
        $previousLocale = null;
        if ($locale !== null) {
            $previousLocale = $this->translatableListener->getListenerLocale();
            $this->translatableListener->setTranslatableLocale($locale);
        }

        try {
            $template = $this->repository->findByKey($key);
            if ($template === null) {
                throw MessageTemplateNotFoundException::withKey($key);
            }

            return $template;
        } finally {
            if ($previousLocale !== null) {
                $this->translatableListener->setTranslatableLocale($previousLocale);
            }
        }
    }
}
