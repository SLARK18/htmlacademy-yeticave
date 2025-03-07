<?php
require_once("helpers.php");
require_once("functions.php");
require_once("data.php");
require_once("init.php");
require_once("models.php");


$categories = $con->query("SELECT character_code, name_category, id FROM categories");
$categories = $categories->fetchAll();

$id = intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
if ($id) {
   $sql = get_query_lot ($id);
} else {
   include_once './pages/404.html';
   http_response_code(404);
   die();
}


$lot = $con->query($sql)->fetch();

if(!isset($lot)) {
   include_once './pages/404.html';
   http_response_code(404);
   die();
}

$page_content = include_template("main-lot.php", [
   "categories" => $categories,
   "lot" => $lot
]);
$layout_content = include_template("layout-lot.php", [
   "content" => $page_content,
   "categories" => $categories,
   "title" => "Главная",
   "is_auth" => $is_auth,
   "user_name" => $user_name
]);

print($layout_content);


