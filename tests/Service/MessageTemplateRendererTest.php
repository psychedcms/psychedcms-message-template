<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\Tests\Service;

use Gedmo\Translatable\TranslatableListener;
use PHPUnit\Framework\TestCase;
use PsychedCms\MessageTemplate\Entity\MessageTemplate;
use PsychedCms\MessageTemplate\Exception\MessageTemplateNotFoundException;
use PsychedCms\MessageTemplate\Repository\MessageTemplateRepository;
use PsychedCms\MessageTemplate\Service\MessageTemplateRenderer;

final class MessageTemplateRendererTest extends TestCase
{
    public function testRenderSubstitutesPlaceholders(): void
    {
        $template = (new MessageTemplate())
            ->setKey('notification.set_modified')
            ->setBody('Le concert de {band} passe à {time}{stage_suffix}');

        $renderer = $this->makeRenderer($template);

        $result = $renderer->render('notification.set_modified', [
            'band' => 'Sleep',
            'time' => '21:00',
            'stage_suffix' => ' sur Main',
        ]);

        $this->assertSame('Le concert de Sleep passe à 21:00 sur Main', $result);
    }

    public function testRenderTreatsMissingPlaceholdersAsLiterals(): void
    {
        $template = (new MessageTemplate())
            ->setKey('notification.set_added')
            ->setBody('Nouveau concert : {band} à {time}{stage_suffix}');

        $renderer = $this->makeRenderer($template);

        // Caller forgot stage_suffix → the marker stays in the output.
        $result = $renderer->render('notification.set_added', [
            'band' => 'Yob',
            'time' => '22:00',
        ]);

        $this->assertSame('Nouveau concert : Yob à 22:00{stage_suffix}', $result);
    }

    public function testRenderEmptyStageSuffixForSingleStageEvent(): void
    {
        $template = (new MessageTemplate())
            ->setKey('notification.set_modified')
            ->setBody('Le concert de {band} passe à {time}{stage_suffix}');

        $renderer = $this->makeRenderer($template);

        $result = $renderer->render('notification.set_modified', [
            'band' => 'Boris',
            'time' => '20:30',
            'stage_suffix' => '',
        ]);

        $this->assertSame('Le concert de Boris passe à 20:30', $result);
    }

    public function testRenderThrowsOnUnknownKey(): void
    {
        $renderer = $this->makeRenderer(null);

        $this->expectException(MessageTemplateNotFoundException::class);
        $renderer->render('notification.unknown', []);
    }

    public function testExistsReturnsTrueWhenFound(): void
    {
        $template = (new MessageTemplate())->setKey('foo.bar')->setBody('hello');
        $renderer = $this->makeRenderer($template);

        $this->assertTrue($renderer->exists('foo.bar'));
    }

    public function testExistsReturnsFalseWhenNotFound(): void
    {
        $renderer = $this->makeRenderer(null);

        $this->assertFalse($renderer->exists('foo.bar'));
    }

    private function makeRenderer(?MessageTemplate $template): MessageTemplateRenderer
    {
        $repo = $this->createMock(MessageTemplateRepository::class);
        $repo->method('findByKey')->willReturn($template);

        return new MessageTemplateRenderer(
            $repo,
            $this->createMock(TranslatableListener::class),
        );
    }
}
