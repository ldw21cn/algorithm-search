<?php
namespace Search\Workers;

use Sunra\PhpSimple\HtmlDomParser;

class LintCode {
    private static $sessionid = "";

    public function __construct() {
        $config = include("./config.php");
        self::$sessionid = $config['lintcode'];
    }

    public function getProblems() {
        $opts = stream_context_create([
            'http' => [
                'method' => "GET",
                'header' => 'Cookie: sessionid='. self::$sessionid .'\r\n'
            ]
        ]);
        
        $questions = [];
        for ($i = 0; $i <= 5; $i++) {
            $content = file_get_contents("http://www.lintcode.com/en/problem/?page=$i", false, $opts);
            $html = HtmlDomParser::str_get_html($content);

            foreach ($html->find(".problem-panel") as $item) {
                // 207 Interval Sum II
                $name = trim($item->find("span", 1)->innertext);
                $id = substr($name, 0, strpos($name, ' '));
                $title = substr($name, strpos($name, ' ') + 1);
                $level = trim($item->find("span", 2)->innertext);
                $done = trim($item->find("span", 3)->innertext) ? true : false;
                $url = "http://www.lintcode.com/en" . trim($item->href);
                $questions[] = [
                    'id' => intval($id),
                    'source' => 'lintcode',
                    'title' => $title,
                    'level' => strtolower($level),
                    'url' => $url,
                    'done' => $done,
                ];
            }
        }

        return $questions;
    }
}