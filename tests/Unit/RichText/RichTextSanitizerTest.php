<?php

namespace Tests\Unit\RichText;

use App\Services\RichText\RichTextSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    private RichTextSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new RichTextSanitizer();
    }

    public function test_keeps_formatting_produced_by_the_editor(): void
    {
        $html = '<h2 style="text-align: center">Title</h2>'
            . '<p>Some <strong>bold</strong>, <em>italic</em>, <u>underlined</u> and <s>struck</s> text.</p>'
            . '<ul><li>One</li></ul><ol><li>Two</li></ol><blockquote><p>Quote</p></blockquote>'
            . '<p><span style="color: #D14343">red</span> <code>x = 1</code></p>';

        $clean = $this->sanitizer->sanitize($html);

        $this->assertStringContainsString('<h2 style="text-align: center">Title</h2>', $clean);
        $this->assertStringContainsString('<strong>bold</strong>', $clean);
        $this->assertStringContainsString('<em>italic</em>', $clean);
        $this->assertStringContainsString('<u>underlined</u>', $clean);
        $this->assertStringContainsString('<s>struck</s>', $clean);
        $this->assertStringContainsString('<ul><li>One</li></ul>', $clean);
        $this->assertStringContainsString('<ol><li>Two</li></ol>', $clean);
        $this->assertStringContainsString('<blockquote><p>Quote</p></blockquote>', $clean);
        $this->assertStringContainsString('<span style="color: #d14343">red</span>', $clean);
        $this->assertStringContainsString('<code>x &#61; 1</code>', $clean);
    }

    public static function maliciousInputs(): array
    {
        return [
            'script tag' => ['<p>Hi</p><script>alert(1)</script>', ['<script', 'alert(1)']],
            'event handler' => ['<p onclick="alert(1)">Hi</p>', ['onclick', 'alert']],
            'img onerror' => ['<img src="x" onerror="alert(1)">', ['onerror', 'alert']],
            'javascript link' => ['<a href="javascript:alert(1)">x</a>', ['javascript:']],
            'data link' => ['<a href="data:text/html;base64,PHNjcmlwdD4=">x</a>', ['data:']],
            'data image' => ['<img src="data:image/svg+xml;base64,PHN2Zz4=">', ['data:']],
            'iframe' => ['<iframe src="https://evil.test"></iframe><p>ok</p>', ['<iframe', 'evil.test']],
            'svg' => ['<svg><script>alert(1)</script></svg><p>ok</p>', ['<svg', 'alert']],
            'img with relative src' => ['<img src="/x"><p>ok</p>', ['<img']],
            'style tag' => ['<style>body{display:none}</style><p>ok</p>', ['<style', 'display']],
            'css url' => ['<span style="background:url(https://evil.test/x)">x</span>', ['url(', 'background']],
            'css expression' => ['<span style="color: expression(alert(1))">x</span>', ['expression', 'style=']],
            'form' => ['<form action="https://evil.test"><input name="p"></form><p>ok</p>', ['<form', '<input']],
            'unknown attribute' => ['<p data-x="1" class="evil" id="a">ok</p>', ['data-x', 'class=', 'id=']],
            'broken markup' => ['<p>ok<scr<script>ipt>alert(1)</script>', ['<script', 'alert(1)']],
        ];
    }

    #[DataProvider('maliciousInputs')]
    public function test_removes_dangerous_markup(string $input, array $forbidden): void
    {
        $clean = (string) $this->sanitizer->sanitize($input);

        foreach ($forbidden as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $clean);
        }
    }

    public function test_unknown_elements_are_unwrapped_but_their_text_is_kept(): void
    {
        $this->assertSame('<p>Hello world</p>', $this->sanitizer->sanitize('<div><p>Hello <font>world</font></p></div>'));
        $this->assertSame('<p>ok</p>', $this->sanitizer->sanitize('<title>t</title><marquee>m</marquee><p>ok</p>'));
    }

    public function test_links_are_forced_to_open_safely(): void
    {
        $clean = $this->sanitizer->sanitize('<p><a href="https://example.com" target="_self" rel="opener">site</a></p>');

        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('target="_blank"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow"', $clean);
        $this->assertStringNotContainsString('_self', $clean);
    }

    public function test_plain_text_is_escaped(): void
    {
        $this->assertSame('<p>Fees &lt; 500 &amp; due</p>', $this->sanitizer->sanitize('<p>Fees &lt; 500 &amp; due</p>'));
        $this->assertSame('Fees and dues', $this->sanitizer->sanitize('Fees and dues'));
    }

    public function test_empty_editor_values_become_null(): void
    {
        $this->assertNull($this->sanitizer->sanitize('<p></p>'));
        $this->assertNull($this->sanitizer->sanitize('<p> <br></p>'));
        $this->assertNull($this->sanitizer->sanitize('<script>alert(1)</script>'));
        $this->assertNull($this->sanitizer->sanitize(null));
    }

    public function test_plain_text_measures_visible_text(): void
    {
        $this->assertSame('Hello world & co', RichTextSanitizer::plainText('<p>Hello <strong>world</strong></p><p>&amp; co</p>'));
    }
}
