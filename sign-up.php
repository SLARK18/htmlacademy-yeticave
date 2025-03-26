<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: /index.php");
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
        } else if (is_valid_email($field_value)) {
            $stmt = $con->prepare("SELECT COUNT(*) AS email_exists FROM users WHERE email = :email");
            $stmt->execute([':email' => $field_value]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['email_exists'] === 0) {
                $valid_fields_array['user_email'] = $field_value;
            } else {
                $invalid_fields_array['user_email'] = "Похоже, что такой email уже зарегистрирован.";
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
        } else if (strpos($field_value, ' ')) {
            $invalid_fields_array['user_password'] = "Пароль не должен содержать пробелы.";
        } else if (strlen($field_value) >= 8) {
            $valid_fields_array['user_password'] = password_hash($field_value, PASSWORD_DEFAULT);
        } else {
            $invalid_fields_array['user_password'] = "Пароль должен быть длиннее 8 символов.";
        }
    } else {
        $invalid_fields_array['user_password'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
    }

    if (isset($_POST['name'])) {
        $field_value = trim($_POST['name']);

        if (empty($field_value)) {
            $invalid_fields_array['user_name'] = "Введите имя.";
        } else if (is_valid_name($field_value)) {
            $valid_fields_array['user_name'] = $field_value;
        } else {
            $invalid_fields_array['user_name'] = "Имя должно содержать только буквы, цифры и знаки нижнего подчеркивания и быть длиннее 2 символов.";
        }
    } else {
        $invalid_fields_array['user_name'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
    }

    if (isset($_POST['message'])) {
        $field_value = trim($_POST['message']);

        if (empty($field_value)) {
            $invalid_fields_array['contacts'] = "Напишите как с вами связаться.";
        } else if (strlen($field_value) >= 5) {
            $valid_fields_array['contacts'] = $field_value;
        } else {
            $invalid_fields_array['contacts'] = "Контакты должны быть длиннее 5 символов.";
        }
    } else {
        $invalid_fields_array['contacts'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
    }
    
    if (empty($invalid_fields_array)) {
        try {
            $con->beginTransaction();

            $stmt = $con->prepare("INSERT INTO users (email, user_name, user_password, contacts)
                VALUES (:user_email, :user_name, :user_password, :contacts)");
            $stmt->execute($valid_fields_array);
            $con->commit();

            header("Location: /login.php");
            exit(); 
        } catch (Exception $e) {
            $con->rollBack();
        }
    }
}  
$layout_content = include_template('sign-up-layout.php', ['categories' => $categories, "errors" => $invalid_fields_array]);

print $layout_content;
