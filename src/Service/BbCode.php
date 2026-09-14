<?php

namespace App\Service;

class BbCode
{
    public function codeToHtml(string $code): string
    {
        $html = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = preg_replace('/\[b\](.*?)\[\/b\]/is', '<b>$1</b>', $html);
        $html = preg_replace('/\[i\](.*?)\[\/i\]/is', '<i>$1</i>', $html);
        $html = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $html);
        $html = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/is', function (array $matches): string {
            $url = trim($matches[1]);
            $decoded = html_entity_decode($url, ENT_QUOTES, 'UTF-8');

            if (!preg_match('#^(https?:)?//#i', $decoded)) {
                return $matches[2];
            }

            return '<a href="' . $url . '" rel="nofollow noopener" target="_blank">' . $matches[2] . '</a>';
        }, $html);

        return $html;
    }

    public function htmlToCode(string $html): string
    {
        $code = preg_replace('#<b>(.*?)</b>#is', '[b]$1[/b]', $html);
        $code = preg_replace('#<i>(.*?)</i>#is', '[i]$1[/i]', $code);
        $code = preg_replace('#<u>(.*?)</u>#is', '[u]$1[/u]', $code);
        $code = preg_replace_callback('#<a href="([^"]*)"[^>]*>(.*?)</a>#is', function (array $matches): string {
            return '[url=' . html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8') . ']' . $matches[2] . '[/url]';
        }, $code);

        return (string) html_entity_decode($code, ENT_QUOTES, 'UTF-8');
    }
}