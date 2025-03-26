<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: /index.php", true, 308);
    exit();
}
require_once("helpers.php");
require_once("functions.php");
require_once("data.php");
require_once("init.php");
require_once("models.php");

$categories = $con->query("SELECT character_code, name_category, id FROM categories");
$categories = $categories->fetchAll();

$invalid_fields_array = [];
$valid_fields_array = [];
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['email'])) {
        $field_value = trim($_POST['email']);

        if (empty($field_value)) {
            $invalid_fields_array['user_email'] = "Введите email.";
        } else if (filter_var($field_value, FILTER_VALIDATE_EMAIL)) {
            $stmt = $con->prepare("SELECT COUNT(*) AS email_exists FROM users WHERE email = :email");
            $stmt->execute([':email' => $field_value]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['email_exists'] > 0) {
                $valid_fields_array['user_email'] = $field_value;
            } else {
                $invalid_fields_array['user_email'] = "Похоже, что такого email нет в базе данных.";
            }
        } else {
            $invalid_fields_array['user_email'] = "Введите корректный email.";
        }
    } else {
        $invalid_fields_array['user_email'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
    }

    if (isset($_POST['password'])) {
        $field_value = trim($_POST['password']);

        if (empty($field_value)) {
            $invalid_fields_array['user_password'] = "Введите пароль.";
        } else {
            $valid_fields_array['user_password'] = $field_value;
        }
    } else {
        $invalid_fields_array['user_password'] = "XSS!";
    }

    if (count($invalid_fields_array) === 0) {
        $stmt = $con->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $valid_fields_array['user_email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($valid_fields_array['user_password'], $user['user_password'])) {
            $_SESSION['user'] = $user;
            unset($_SESSION['user']['user_password']);
            session_regenerate_id();
            header("Location: http://localhost:3000/index.php");
            exit();
        } else {
            error_log("Login failed for email: " . $valid_fields_array['user_email'] . 
                  " | Provided password hash: " . password_hash($valid_fields_array['user_password'], PASSWORD_DEFAULT) . 
                  " | Stored hash: " . ($user ? $user['password'] : 'no user found'));
            $invalid_fields_array['user_password'] = "Вы ввели неверный пароль.";
        }
    }
}

$layout_content = include_template('login-layout.php', ['categories' => $categories, 'errors' => $invalid_fields_array]);

print $layout_content;
