<?php
if ( isset( $_GET["app"] ) && $_GET["app"] == "app_image_browser" ) {

    $file   = @$_FILES["file"];
    $error  = $success  = "";
    $result = [];

    $constantName = $_POST["dataName"];
    $settingsID   = (int)$_POST["dataID"];

// Разрешенные расширения файлов.
    $allow = array(
        "bmp",
        "gif",
        "png",
        "jpg",
    );

// Директория, куда будут загружаться файлы.
    $subfolder = trim( $_POST["dataSubfolder"] );

    $PATH_TO_UPLOAD = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . ADMIN_IMAGES_DEFAULT_PATH;
    $PATH_TO_UPLOAD = fix_directory_separator( $PATH_TO_UPLOAD );

    if ( $subfolder != "" ) {
        $PATH_TO_UPLOAD = $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . $subfolder;
    }
    // cls();

########
    ####
    if ( !empty( $file ) && isset( $_POST["upload_image_file"] ) && ( $_GET["operation"] == "upload_image_file" ) ) {
        // Проверим на ошибки загрузки.
        if ( !empty( $file["error"] ) || empty( $file["tmp_name"] ) ) {
            switch ( @$file["error"] ) {
                case 1:
                case 2:$error = "Превышен размер загружаемого файла.";
                    break;
                case 3:$error = "Файл был получен только частично.";
                    break;
                case 4:$error = "Файл не был загружен.";
                    break;
                case 6:$error = "Файл не загружен - отсутствует временная директория.";
                    break;
                case 7:$error = "Не удалось записать файл на диск.";
                    break;
                case 8:$error = "PHP-расширение остановило загрузку файла.";
                    break;
                case 9:$error = "Файл не был загружен - директория не существует.";
                    break;
                case 10:$error = "Превышен максимально допустимый размер файла.";
                    break;
                case 11:$error = "Данный тип файла запрещен.";
                    break;
                case 12:$error = "Ошибка при копировании файла.";
                    break;
                default:$error = "Файл не был загружен - неизвестная ошибка.";
                    break;
            }
            $result["error"] = $error;
        } elseif ( $file["tmp_name"] == "none" || !is_uploaded_file( $file["tmp_name"] ) ) {
            $error           = "38:Не удалось загрузить файл.";
            $result["error"] = $error;
        } else {
            // Оставляем в имени файла только буквы, цифры и некоторые символы.
            $pattern  = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
            $filename = mb_eregi_replace( $pattern, "-", $file["name"] );
            $filename = mb_ereg_replace( "[-]+", "-", $filename );

            // Т.к. есть проблема с кириллицей в названиях файлов (файлы становятся недоступны).
            // Сделаем их транслит:
            $converter = array(

                "а" => "a", "б"  => "b", "в"  => "v", "г"   => "g", "д" => "d", "е" => "e",
                "ё" => "e", "ж"  => "zh", "з" => "z", "и"   => "i", "й" => "y", "к" => "k",
                "л" => "l", "м"  => "m", "н"  => "n", "о"   => "o", "п" => "p", "р" => "r",
                "с" => "s", "т"  => "t", "у"  => "u", "ф"   => "f", "х" => "h", "ц" => "c",
                "ч" => "ch", "ш" => "sh", "щ" => "sch", "ь" => "", "ы"  => "y", "ъ" => "",
                "э" => "e", "ю"  => "yu", "я" => "ya",
                "А" => "A", "Б"  => "B", "В"  => "V", "Г"   => "G", "Д" => "D", "Е" => "E",
                "Ё" => "E", "Ж"  => "Zh", "З" => "Z", "И"   => "I", "Й" => "Y", "К" => "K",
                "Л" => "L", "М"  => "M", "Н"  => "N", "О"   => "O", "П" => "P", "Р" => "R",
                "С" => "S", "Т"  => "T", "У"  => "U", "Ф"   => "F", "Х" => "H", "Ц" => "C",
                "Ч" => "Ch", "Ш" => "Sh", "Щ" => "Sch", "Ь" => "", "Ы"  => "Y", "Ъ" => "",
                "Э" => "E", "Ю"  => "Yu", "Я" => "Ya",

            );
            $filename       = strtr( $filename, $converter );
            $result["name"] = $filename;

            $parts = pathinfo( $filename );
            if ( empty( $filename ) || empty( $parts["extension"] ) ) {
                $error           = "48:Не удалось загрузить файл.";
                $result["error"] = $error;
            } elseif ( !empty( $allow ) && !in_array( strtolower( $parts["extension"] ), $allow ) ) {
                $error           = "Недопустимый тип файла";
                $result["error"] = $error;
            } else {
                $result["extension"] = $parts["extension"];

                // Проверим директорию для загрузки.
                if ( !is_dir( $PATH_TO_UPLOAD ) ) {
                    mkdir( $PATH_TO_UPLOAD, 0777, true );
                }

                // Перемещаем файл в директорию.
                if ( move_uploaded_file( $file["tmp_name"], $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . $filename ) ) {
                    // Далее можно сохранить название файла в БД и т.п.
                    $success                 = "Файл «" . $filename . "» успешно загружен.";
                    $result["path"]          = $PATH_TO_UPLOAD;
                    $result["full_filename"] = $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . $filename;
                    $result["success"]       = $success;

                    if ( isset( $_POST["ajax_save"] ) && $settingsID > 0 ) {
                        $newValue = $subfolder . SLASH . $filename;
                        _setSettingOptionValueByID( $settingsID, $newValue );
                        $result["newValue"] = $newValue;
                    }

                } else {
                    $error           = "56:Не удалось загрузить файл.";
                    $result["error"] = $error;
                }
            }
        }

        $result["constantName"] = $constantName;
        $result["settingsID"]   = $settingsID;
        $result["subfolder"]    = $subfolder;

        // Выводим сообщение о результате загрузки.
        if ( !empty( $success ) ) {
            header_status( 200 );
            // header( "Content-Type: application/json; charset=UTF-8" );
            header( "Content-Type: text/html; charset=UTF-8" );
        } else {
            header_status( 409 );
            header( "Content-Type: application/json; charset=UTF-8" );
        }

        die( json_encode( $result, JSON_INVALID_UTF8_IGNORE ) );
    }

########
    #### isset($_POST["dataID"]) &&
    if ( isset( $_POST["get_images_in_subfolder"] ) && $_GET["operation"] == "get_images_in_folder" ) {

        $result = "";
        $result .= "<h1> Папка <small>" . SLASH . $_POST["dataSubfolder"] . "</small>" . SLASH . "</h1>";
        $result .= "<p class='text-info text-center'> dblclick to select / Двойной_щелчёк для выбора</p>";
        $res_t = "";

        $myImages = array();

        $jpg         = $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . "*.jpg";
        $myImagesJPG = glob( $jpg );

        $png         = $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . "*.png";
        $myImagesPNG = glob( $png );

        $gif         = $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . "*.gif";
        $myImagesGIF = glob( $gif );

        $bmp         = $PATH_TO_UPLOAD . DIRECTORY_SEPARATOR . "*.bmp";
        $myImagesBMP = glob( $bmp );

        $myImages = array_merge( $myImagesJPG, $myImagesPNG, $myImagesGIF, $myImagesBMP );

        if ( $myImages ) {

            foreach ( $myImages as $img_key => $path_to_file ) {
                $imgInfo = array();
                $imgInfo = getImageInfo( $path_to_file );

                $res_t .= <<<HTML
<figure class="m-1 p-2 figure text-center bg-secondary bg-opacity-10 rounded border border-1" ondblclick ="jsSetValue('{$imgInfo["file_rel_path"]}',{$settingsID});return false;">
<img src='{$imgInfo["file_rel_path"]}' class="figure-img img-fluid img-thumbnail bg-white"
alt='{$imgInfo["filename"]}'
title='{$imgInfo["width"]}x{$imgInfo["height"]}px ( {$imgInfo["fileSize"]} ) {$imgInfo["bits"]}bits "{$imgInfo["mime"]}" {$imgInfo["filename"]}.{$imgInfo["extension"]} '
style='max-width:128px;max-height: 128px;'>
<figcaption class="figure-caption text-center text-wrap">{$imgInfo["width"]}x{$imgInfo["height"]}px ( {$imgInfo["fileSize"]} )<br>{$imgInfo["bits"]}bits "{$imgInfo["mime"]}"<br>{$imgInfo["filename"]}.{$imgInfo["extension"]}</figcaption>
</figure>


HTML;

            }

        }

        $res_t .= <<<HTML
<figure class="m-1 p-2 text-center bg-danger bg-opacity-10 rounded border border-1"  ondblclick ="jsClearValue({$settingsID});return false;"  style='max-width:144px;max-height: 200px;'>
<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
</svg>
    <figcaption class="text-center text-danger">Очистить значение</figcaption>
</figure>

HTML;

        $result .= $res_t;

        if ( !empty( $result ) ) {
            header_status( 200 );
            // header( "Content-Type: application/json; charset=UTF-8" );
            header( "Content-Type: text/html; charset=UTF-8" );
        } else {
            header_status( 409 );
            header( "Content-Type: text/html; charset=UTF-8" );
        }

        die( $result );
    }

########
    ####  &&
    if ( isset( $_POST["set_image_as_value"] ) && isset( $_POST["dataID"] ) && isset( $_POST["dataValue"] ) && $_GET["operation"] == "set_image_as_value" ) {
        $result = [];

        if ( isset( $_POST["ajax_save"] ) && $settingsID > 0 ) {
            $fn       = fix_directory_separator( $_POST["dataValue"] );
            $newValue = str_replace( fix_directory_separator( ADMIN_IMAGES_DEFAULT_PATH . DIRECTORY_SEPARATOR ), "", $fn );
            $newValue = str_replace( DIRECTORY_SEPARATOR, SLASH, $newValue );
            _setSettingOptionValueByID( $settingsID, $newValue );
            $result["success"] ="Файл \"$newValue\" установлен как значение константы";
            $result["newValue"] = ADMIN_IMAGES_DEFAULT_PATH.SLASH.$newValue;
            $result["dataID"]  = $settingsID;
        } else {
            $result["error"]    = "Значение не установлено";
            $result["newValue"] = $newValue;
            $result["dataID"]   = $settingsID;
        }

        if ( !empty( $result ) ) {
            header_status( 200 );
            // header( "Content-Type: application/json; charset=UTF-8" );
            header( "Content-Type: application/json; charset=UTF-8" );
        } else {
            header_status( 409 );
            header( "Content-Type: application/json; charset=UTF-8" );
        }
        die( json_encode($result, JSON_INVALID_UTF8_IGNORE ));
    }
########
    ####  &&
    if ( isset( $_POST["set_image_as_value"] ) && isset( $_POST["dataID"] ) && isset( $_POST["dataValue"] ) && ( $_POST["dataValue"] =="0") && $_GET["operation"] == "clear_value" ) {
        $result = [];

        if ( isset( $_POST["ajax_save"] ) && $settingsID > 0 ) {
            $newValue = "";
            _setSettingOptionValueByID( $settingsID, $newValue );
            $result["success"] ="Значение сброшено";
            $result["newValue"] = "";
            $result["dataID"]  = $settingsID;
        } else {
            $result["error"]    = "Значение не установлено";
            $result["newValue"] = $newValue;
            $result["dataID"]   = $settingsID;
        }

        if ( !empty( $result ) ) {
            header_status( 200 );
            // header( "Content-Type: application/json; charset=UTF-8" );
            header( "Content-Type: application/json; charset=UTF-8" );
        } else {
            header_status( 409 );
            header( "Content-Type: application/json; charset=UTF-8" );
        }
        die( json_encode($result, JSON_INVALID_UTF8_IGNORE ));
    }

}
