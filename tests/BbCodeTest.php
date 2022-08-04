<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\BbCode;

class BbCodeTest extends TestCase
{
    public function testBbCodeToHtml(): void
    {
        $bbcode = new BbCode();
        $code = '<script> console.log </script> [b] bold [/b] [i] italic [/i] [u] underlined [/u] [url=http://google.com] google [/url]';
        echo $bbcode->codeToHtml($code);
        $this->expectOutputString(' <b> bold </b> <i> italic </i> <u> underlined </u> <a href="http://google.com"> google </a>');
        $this->assertTrue(true);
    }
}
