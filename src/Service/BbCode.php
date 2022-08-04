<?php

namespace App\Service;

class BbCode
{
    private $bbcodes = array( '/\[b\](.*?)\[\/b\]/',
                              '/\[i\](.*?)\[\/i\]/',
                              '/\[u\](.*?)\[\/u\]/',
                              '/\[url=(.*?)\](.*?)\[\/url\]/');

    private $htmlcodes = array( '<b>$1</b>',
                                '<i>$1</i>',
                                '<u>$1</u>',
                                '<a href="$1">$2</a>');
    public function codeToHtml(string $code): string
    {
        $html = $code;
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        foreach($this->bbcodes as $key => $bbcode) {
            $html = preg_replace($bbcode,$this->htmlcodes[$key],$html);
        }
        return $html;
    }

    public function htmlToCode(string $code): string
    {
        return '';
    }



}
