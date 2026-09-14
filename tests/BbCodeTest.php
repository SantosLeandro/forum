<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\BbCode;

class BbCodeTest extends TestCase
{
    public function testBbCodeToHtml(): void
    {
        $bbcode = new BbCode();
        $code = '[b] bold [/b] [i] italic [/i] [u] underlined [/u] [url=http://google.com] google [/url]';
        $expected = '<b> bold </b> <i> italic </i> <u> underlined </u> <a href="http://google.com" rel="nofollow noopener" target="_blank"> google </a>';
        $this->assertSame($expected, $bbcode->codeToHtml($code));
    }

    public function testRawHtmlIsEscaped(): void
    {
        $bbcode = new BbCode();
        $code = '<script>alert(1)</script><img src=x onerror=alert(1)>';
        $expected = '&lt;script&gt;alert(1)&lt;/script&gt;&lt;img src=x onerror=alert(1)&gt;';
        $this->assertSame($expected, $bbcode->codeToHtml($code));
    }

    public function testJavascriptUrlIsRejected(): void
    {
        $bbcode = new BbCode();
        $code = '[url=javascript:alert(1)]clique aqui[/url] [url=//evilsite.com/payload.js]link[/url]';
        $expected = 'clique aqui <a href="//evilsite.com/payload.js" rel="nofollow noopener" target="_blank">link</a>';
        $this->assertSame($expected, $bbcode->codeToHtml($code));
    }

    public function testHtmlToCodeRoundTrip(): void
    {
        $bbcode = new BbCode();
        $code = '[b]bold[/b] [i]italic[/i] <script>alert(1)</script> [url=https://exemplo.com?a=1&b=2]link[/url]';
        $html = $bbcode->codeToHtml($code);
        $this->assertSame($bbcode->codeToHtml($bbcode->htmlToCode($html)), $html);
    }
}