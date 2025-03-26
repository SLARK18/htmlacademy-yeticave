<?php
session_start();

require_once("helpers.php");
require_once("functions.php");
require_once("init.php");
require_once("models.php");


$categories = $con->query("SELECT character_code, name_category, id FROM categories");
$categories = $categories->fetchAll();


$sql = get_query_list_lots('2021-07-15');

$goods = $con->query($sql)->fetchAll();

$page_content = include_template("main.php", [
   "categories" => $categories,
   "goods" => $goods
]);
$layout_content = include_template("layout.php", [
   "content" => $page_content,
   "categories" => $categories,
   "title" => "Главная",
]);

print($layout_content);


