<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_sanitizes_disallowed_html_tags(): void
    {
        $input = '<div>Ignorar</div><p>Texto</p><script>alert("hack")</script>';
        $sanitized = HtmlSanitizer::sanitize($input);

        $this->assertStringContainsString('<p>Texto</p>', $sanitized);
        $this->assertStringNotContainsString('<div>', $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
    }
}
