<?php
require_once("helpers.php");
require_once("functions.php");
require_once("data.php");
require_once("init.php");
require_once("models.php");


if (!$con) {
   $error = mysqli_connect_error();
} else {
   $sql = "SELECT character_code, name_category FROM categories";
   $result = mysqli_query($con, $sql);
   if ($result) {
      $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
   } else {
      $error = mysqli_error($con);
   }
}
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if ($id) {
   $sql = get_query_lot ($id);
} else {
   http_response_code(404);
   die();
}


$res = mysqli_query($con, $sql);
if ($res) {
   $lot = mysqli_fetch_assoc($res);
} else {
   $error = mysqli_error($con);
}

if(!$lot) {
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


