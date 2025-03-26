<?php 
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: /login.php");
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

  if (isset($_POST['lot-name'])) {
    $field_value = trim($_POST['lot-name']);
    if (empty($field_value)) {
      $invalid_fields_array['lot-name'] = "Поле не может быть пустым.";
    } else {
      $valid_fields_array['lot_name'] = htmlspecialchars($field_value);
    }
  } else {
    $invalid_fields_array['lot-name'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
  }


  if (isset($_POST['lot-category'])) {
    $field_value = $_POST['lot-category'];
    if (in_array($field_value, array_column($categories, 'name_category'))) {
      $valid_fields_array['lot_category'] = getCategoryIDByName($categories, $field_value);
    } elseif ($field_value === "Выберите категорию") {
      $invalid_fields_array['lot-category'] = "Выберите категорию из выпадающего списка.";
    } else {
      $invalid_fields_array['lot-category'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
    }
  } else {
    $invalid_fields_array['lot-category'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
  }


  if (isset($_POST['lot-desc'])) {
    $field_value = trim($_POST['lot-desc']);
    if (empty($field_value)) {
      $invalid_fields_array['lot-desc'] = "Поле не может быть пустым.";
    } else {
      $valid_fields_array['lot_desc'] = htmlspecialchars($field_value);
    }
  } else {
    $invalid_fields_array['lot-desc'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
  }


  if (isset($_POST['lot-rate'])) {
    $field_value = $_POST['lot-rate'];
    if (is_numeric($field_value) && $field_value > 0) {
      $valid_fields_array['lot_rate'] = $field_value;
    } else {
      $invalid_fields_array["lot-rate"] = "Введите число больше нуля.";
    }
  } else {
    $invalid_fields_array['lot-rate'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
  }


  if (isset($_POST['lot-step'])) {
    $field_value = $_POST['lot-step'];
    if (filter_var($field_value, FILTER_VALIDATE_INT) === (int)$field_value && (int)$field_value > 0) {
      $valid_fields_array['lot_step'] = $field_value;
    } else {
      $invalid_fields_array["lot-step"] = "Шаг должен быть целым числом больше нуля.";
    }
  } else {
    $invalid_fields_array['lot-step'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
  }


  if (isset($_POST['lot-date'])) {
    $field_value = $_POST['lot-date'];
    if (is_date_valid($field_value)) {
      $field_value_date = new DateTime($field_value);
      $current_date = new DateTime();

      $diff_dates_in_days = $field_value_date->diff($current_date)->days;

      if ($diff_dates_in_days >= 1 && $field_value_date > $current_date) {
        $valid_fields_array['lot_date'] = $field_value;
      } else {
        $invalid_fields_array['lot-date'] = "Дата окончания должна быть больше текущей даты, хотя бы на один день.";
      }
    } else {
      $invalid_fields_array['lot-date'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
    }
  } else {
    $invalid_fields_array['lot-date'] = "Не наёбывай меня, Тони. Даже не пытайся наебать.";
  }


  if (isset($_FILES['lot-img']) && $_FILES['lot-img']['error'] === UPLOAD_ERR_OK) {
    // Если список ошибок не пуст, то не будем даже обрабатывать картинку.
    if (empty($invalid_fields_array)) {
      $fileTmpPath = $_FILES['lot-img']['tmp_name'];
      $fileName = $_FILES['lot-img']['name'];
      $fileMime = mime_content_type($fileTmpPath);

      // Допустимые MIME-типы
      $allowedMimeTypes = ['image/jpeg', 'image/png'];
      // Допустимые расширения
      $allowedExtensions = ['jpg', 'jpeg', 'png'];

      $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      $destination = 'uploads/' . uniqid() . '.' . $fileExtension;

      if (!in_array($fileMime, $allowedMimeTypes)) {
        $invalid_fields_array['lot-img'] = "Ошибка: Недопустимый MIME-тип файла: $fileMime.";
      } else if (!in_array($fileExtension, $allowedExtensions)) {
        $invalid_fields_array['lot-img'] = "Ошибка: Недопустимое расширение файла: $fileExtension.";
      } else if (!move_uploaded_file($fileTmpPath, $destination)) {
        $invalid_fields_array['lot-img'] = "Ошибка: Файл не загружен на сервер";
      } else {
        $valid_fields_array['lot_img'] = $destination;
      }
    }
  } else {
    $invalid_fields_array['lot-img'] = "Файл не был загружен или произошла ошибка."; //" Код ошибки: " . $_FILES['lot-img']['error'];
  }

  $valid_fields_array['author_id'] = 1;

  if (empty($invalid_fields_array)) {
    try {
      $con->beginTransaction();

      $stmt = $con->prepare("INSERT INTO lots (title, lot_description, img, start_price, date_finish, step, user_id, category_id)
       VALUES (:lot_name, :lot_desc, :lot_img, :lot_rate, :lot_date, :lot_step, :author_id, :lot_category)");
      $stmt->execute($valid_fields_array);
      $new_lot_id = $con->lastInsertId();
      $con->commit();

      header("Location: /lot.php?id=". $new_lot_id);
      exit(); 
    } catch (Exception $e) {
      $con->rollBack();
    }
  }
  
}




$layout_content = include_template('add-lot-layout.php', ['categories' => $categories, 'user_name' => $user_name,
 "errors" => $invalid_fields_array]);

print $layout_content;

