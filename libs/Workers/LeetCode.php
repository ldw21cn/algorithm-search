<?php
namespace Search\Workers;

use Sunra\PhpSimple\HtmlDomParser;

class LeetCode {
    private static $sessionid = "";

    public function __construct() {
        $config = include("./config.php");
        self::$sessionid = $config['leetcode'];
    }

    public function getProblems() {
        $opts = stream_context_create([
            'http' => [
                'method' => "GET",
                'header' => 'Cookie: PHPSESSID=' . self::$sessionid . '\r\n'
            ]
        ]);

        $questions = [];
        $content = file_get_contents("http://leetcode.com/problemset/algorithms/", false, $opts);
        $html = HtmlDomParser::str_get_html($content);
        // tbody
        $tbody = $html->find("#problemList", 0)->children(1);
        foreach ($tbody->find("tr") as $tr) {
            $done = $tr->find("td", 0)->find("span", 0)->class;
            if ($done == "ac") {
                $done = true;
            } else {
                $done = false;
            }
            $id = $tr->find("td", 1)->innertext;
            $title = $tr->find("td", 2)->find("a", 0)->innertext;
            $url = 'https://leetcode.com' . $tr->find("td", 2)->find("a", 0)->href;
            $level = $tr->find("td", 4)->innertext;
            $questions[] = [
                'id' => intval($id),
                'source' => "leetcode",
                'title' => $title,
                'url' => $url,
                'level' => strtolower($level),
                'done' => $done,
            ];
        }

        return $questions;
    }
}