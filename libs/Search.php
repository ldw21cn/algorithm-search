<?php
namespace Search;

use Search\Workers\LeetCode;
use Search\Workers\LintCode;
use Search\Workflows;

class Search {
    private static $problemFile = "./cache/problems.json";
    private static $indexesFile = "./cache/indexes.json";

    public static function doSearch($query) {
        $w = new Workflows();
        $words = explode(" ", $query);
        $problems = self::getProblems();
        $indexes = self::getInvertedIndexes();
        $ids = [];
        foreach ($words as $word) {
            if (array_key_exists($word, $indexes)) {
                if (empty($ids)) {
                    $ids = $indexes[$word];
                } else {
                    $ids = array_intersect($ids, $indexes[$word]);
                }
            }
        }

        if (empty($ids) && empty($ids)) {
            $w->result('itemuid', 'itemarg', 'Can not find titles', 'Some item subtitle', 'icon.png', 'yes', 'autocomplete');
        } else {
            foreach ($ids as $id) {
                $problem = $problems[$id];
                $subtitle = $problem['level'] . " | " . ($problem['done'] ? "DONE": "TODO");
                if ($problem['source'] == 'leetcode') {
                    $w->result('itemuid', $problem['url'], $problem['title'], $subtitle, 'leetcode.ico', 'yes', 'autocomplete' );
                } else {
                    $w->result( 'itemuid', $problem['url'], $problem['title'], $subtitle, 'lintcode.ico', 'yes', 'autocomplete' );
                }
            }
        }
        echo $w->toxml();
    }

    public static function listTodo() {
        $w = new Workflows();
        $problems = self::getProblems();
        foreach ($problems as $problem) {
            if ($problem['done'] == true) {
                continue;
            }
            $subtitle = $problem['level'] . " | " . ($problem['done'] ? "DONE": "TODO");
            if ($problem['source'] == 'leetcode') {
                $w->result('itemuid', $problem['url'], $problem['title'], $subtitle, 'leetcode.ico', 'yes', 'autocomplete' );
            } else {
                $w->result( 'itemuid', $problem['url'], $problem['title'], $subtitle, 'lintcode.ico', 'yes', 'autocomplete' );
            }
        }
        echo $w->toxml();
    }

    public static function updateProblems() {
        $leetcode = new LeetCode();
        $problems = $leetcode->getProblems();
        $lintcode = new LintCode();
        $problems = array_merge($problems, $lintcode->getProblems());
        file_put_contents(self::$problemFile, json_encode($problems, JSON_PRETTY_PRINT));
    }

    public static function getProblems() {
        if (!file_exists(self::$problemFile)) {
            self::updateProblems();
        }
        return json_decode(file_get_contents(self::$problemFile), true);
    }

    public static function updateInvertedIndexes() {
        $problems = self::getProblems();

        $invertedIndexes = [];
        foreach ($problems as $key => $item) {
            $title = strtolower($item['title']);
            preg_replace("/[^A-Za-z]/", ' ', $title);
            $words = explode(' ', strtolower($title));
            foreach ($words as $word) {
                if (array_key_exists($word, $invertedIndexes)) {
                    if (in_array($key, $invertedIndexes[$word])) {
                        continue;
                    } else {
                        $invertedIndexes[$word][] = $key;
                    }
                } else {
                    $invertedIndexes[$word] = [$key];
                }
            }
        }
        file_put_contents(self::$indexesFile, json_encode($invertedIndexes, JSON_PRETTY_PRINT));
    }

    public static function getInvertedIndexes() {
        if (!file_exists(self::$indexesFile)) {
            self::updateInvertedIndexes();
        }
        return json_decode(file_get_contents(self::$indexesFile), true);
    }
}