<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\Exception;

use RuntimeException;

final class MessageTemplateNotFoundException extends RuntimeException
{
    public static function withKey(string $key): self
    {
        return new self(\sprintf('No MessageTemplate found with key "%s"', $key));
    }
}
