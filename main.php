<?php
include "./vendor/autoload.php";

function doUpdate() {
    Search\Search::updateProblems();
    Search\Search::updateInvertedIndexes();
}

function doSearch($query) {
    Search\Search::doSearch($query);
}

function listTodo() {
    Search\Search::listTodo();
}
