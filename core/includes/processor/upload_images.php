<?php

$input_name = 'file';

// Разрешенные расширения файлов.
$allow = array('jpg', 'jpeg', 'png', 'gif');

// Директория, куда будут загружаться файлы.
$path = $_SERVER["DOCUMENT_ROOT"] . '/uploads/';

if (isset($_FILES[$input_name])) {
  // Преобразуем массив $_FILES в удобный вид для перебора в foreach.
  $files = array();
  $diff = count($_FILES[$input_name]) - count($_FILES[$input_name], COUNT_RECURSIVE);
  if ($diff == 0) {
    $files = array($_FILES[$input_name]);
  } else {
    foreach($_FILES[$input_name] as $k => $l) {
      foreach($l as $i => $v) {
        $files[$i][$k] = $v;
      }
    }
  }

  foreach ($files as $file) {
    $error = $success = '';

    // Проверим на ошибки загрузки.
    if (!empty($file['error']) || empty($file['tmp_name'])) {
      switch (@$file['error']) {
        case 1:
        case 2: $error = 'Превышен размер загружаемого файла.'; break;
        case 3: $error = 'Файл был получен только частично.'; break;
        case 4: $error = 'Файл не был загружен.'; break;
        case 6: $error = 'Файл не загружен - отсутствует временная директория.'; break;
        case 7: $error = 'Не удалось записать файл на диск.'; break;
        case 8: $error = 'PHP-расширение остановило загрузку файла.'; break;
        case 9: $error = 'Файл не был загружен - директория не существует.'; break;
        case 10: $error = 'Превышен максимально допустимый размер файла.'; break;
        case 11: $error = 'Данный тип файла запрещен.'; break;
        case 12: $error = 'Ошибка при копировании файла.'; break;
        default: $error = 'Файл не был загружен - неизвестная ошибка.'; break;
      }
    } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
      $error = 'Не удалось загрузить файл.';
    } else {
      // Оставляем в имени файла только буквы, цифры и некоторые символы.
      $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
      $name = mb_eregi_replace($pattern, '-', $file['name']);
      $name = mb_ereg_replace('[-]+', '-', $name);

      $parts = pathinfo($name);
      if (empty($name) || empty($parts['extension'])) {
        $error = 'Недопустимое тип файла';
      } elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
        $error = 'Недопустимый тип файла';
      } else {
        // Перемещаем файл в директорию.
        if (move_uploaded_file($file['tmp_name'], $path . $name)) {
          // Далее можно сохранить название файла в БД и т.п.
          $success = 'Файл «' . $name . '» успешно загружен.';
        } else {
          $error = 'Не удалось загрузить файл.';
        }
      }
    }

    // Выводим сообщение о результате загрузки.
    if (!empty($success)) {
      echo '<p class="success">' . $success . '</p>';
    } else {
      echo '<p class="error">' . $error . '</p>';
    }
  }
}
?>
