<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use PsychedCms\MessageTemplate\Entity\MessageTemplate;

#[ORM\Entity]
#[ORM\Table(name: 'message_template_translations')]
#[ORM\UniqueConstraint(name: 'uniq_message_template_trans', columns: ['locale', 'object_id', 'field'])]
class MessageTemplateTranslation extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: MessageTemplate::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', nullable: false, onDelete: 'CASCADE')]
    protected $object;
}
