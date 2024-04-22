<?php
##########################################
#        antaNT64pro ShopCMS_UTF        #
#        © antaNT64pro, 2018-09-06       #
#          http://nixby.pro              #
##########################################

ini_set( 'display_errors', 1 ); // сообщения с ошибками будут показываться
error_reporting( E_ALL );       // E_ALL - отображаем ВСЕ ошибки

//products and categories catalog import from MS Excel .CSV files
if ( !strcmp( $sub, "CSV_import" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 5, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        if ( $_POST["delimiter"] == 2 ) {
            $delimiter = ",";
        } elseif ( $_POST["delimiter"] == 3 ) {
            $delimiter = "\t";
        } else {
            $delimiter = ";";
        }
        $delimiterv = $_POST["delimiter"];

        $csv_utf8 = ( $_POST["csv_utf8"] == 1 )?1:0; //кодировка csv файла

### врезка

### врезка конец

        #upload file and show import configurator
        if ( isset( $_POST["proceed"] ) && isset( $_POST["mode"] ) ) {
            if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
            {
                Redirect( ADMIN_FILE . "?dpt=catalog&sub=CSV_import&safemode=yes" );
            }

            $res       = 0;
            $POST_MODE = (int)$_POST["mode"];
            switch ( $POST_MODE ) {
                case 100:
                    #УДАЛЕНИЕ
                    # // reset database content
                    imDeleteAllProducts();
                    $res = 1;
                    $smarty->assign( "CSV_import_result", "ok" );
                    break;
                case 0:
                    # СТАНДАРТНОЕ и AJAX ОБНОВЛЕНИЕ
                    //upload CSV-file
                    include_once "core/includes/admin/sub/catalog_CSV_import__UploadStandartCSV.php";
                    break;
                case 64:
                    //nix.xml - убрал в отделный
                    // include_once "core/includes/admin/sub/catalog_CSV_import__CreateCSV.php";
                    // include_once "core/includes/admin/sub/catalog_CSV_import__nixruXML.php";
                    break;
                default:
                    # code...
                    break;
            }

        }

        # BEGIN загружаем CSV-файл любого размера
        $smarty->assign( "controls", settingCallHtmlFunctions( CONF_CATALOG_CSV_IMPORT ) );

        if ( isset( $_POST["do_CSV_import"] ) && isset( $_POST["filename"] ) && isset( $_POST["update_column"] ) && isset( $_GET["ajax"] ) ) {

            $csv_handle = gzopen( $_POST['filename'], 'r' );
            $csv_colcnt = count( fgetcsv( $csv_handle, 0, $delimiter ) );
            ## fgetcsv - Возвращает индексированный массив с прочтенными полями.  число столбцов - индексов массива прочитанных строк]
// debugfile($csv_colcnt,"csv_colcnt catalog_CSV_import.php:75");
// die("DEBUG;");
/*
            fseek ( resource $handle , int $offset [, int $whence = SEEK_SET ] ) : int
            Устанавливает смещение в файле, на который ссылается handle.
            Новое смещение, измеряемое в байтах от начала файла,
            получается путём прибавления параметра offset к позиции, указанной в параметре whence
 */
            if ( isset( $_SESSION['csv_ftell'] ) ) {
                fseek( $csv_handle, $_SESSION['csv_ftell'] );
            } else {
                $_SESSION['csv_rowcnt']     = 0;
                $_SESSION['csv_parent']     = array( 1 );
                $_SESSION['csv_categoryID'] = 1;
            }

            $data         = array();
            $EOF          = false;
            $EMPTY_STRING = false;

            for ( $i = 0; $i++ < (int)$_POST['settingCONF_CSV_IMPORT_LIMIT']; ) {
                $row = fgetcsv( $csv_handle, 0, $delimiter ); // читает строку из цсв файла
                if ( !$row ) {
                    $EOF = true; //если строки нет то выход
                    break;}
                if ( !$row[0] and !$row[1] and !$row[2] and !$row[3] ) {
                    $EOF          = true;
                    $EMPTY_STRING = true;
                    break;
                }
                if ( count( $row ) == $csv_colcnt ) {
                    $data[]       = $row;
                    $EMPTY_STRING = false;
                }
                $_SESSION['csv_rowcnt']++;
            }

            $_SESSION['csv_ftell'] = ftell( $csv_handle ); //ftell — Возвращает текущую позицию указателя чтения/записи файла
            gzclose( $csv_handle );

            $res                  = imReadImportConfiguratorSettings();
            $db_association       = $res["db_association"];
            $dbcPhotos            = $res["dbcPhotos"];
            $dbc                  = $res["dbc"];
            $updated_extra_option = $res["updated_extra_option"];
            # csv_utf8
            if ( !$csv_utf8 ) {
                $data = iconv_all( $data );
            }
            # end csv_utf8
            foreach ( $data as $row ) {
                imImportRowToDataBase( $row, $dbc, $dbc[$_POST["update_column"]], $dbcPhotos, $updated_extra_option, $_SESSION['csv_parent'], $_SESSION['csv_categoryID'] );
            }

            if ( $EOF ) {
                update_psCount( 1 );
                if ( $EMPTY_STRING ) {
                    echo "ERROR::EMPTY_STRING";
                }
                die( "END");//. " ". $_SESSION['csv_rowcnt']. " rows loaded" );
            }

            $msg_die = (string)$_SESSION['csv_rowcnt'];
            $msg_die .= " <br> <em style='font-size:50%;'>";
            $msg_die .= " [#";
            $msg_die .= (string)trim( $row[$dbc["product_code"]] );
            $msg_die .= "] ";
            $msg_die .= (string)trim( $row[$dbc["name"]] );
            $msg_die .= " $";
            $msg_die .= (string)trim( $row[$dbc["Price"]] );
            $msg_die .= "</em> ";
            die( $msg_die );

        }

        if ( isset( $_SESSION['csv_ftell'] ) ) {
            unset( $_SESSION['csv_ftell'] );
        }

        if ( isset( $_SESSION['csv_rowcnt'] ) ) {
            unset( $_SESSION['csv_rowcnt'] );
        }

        if ( isset( $_SESSION['csv_parent'] ) ) {
            unset( $_SESSION['csv_parent'] );
        }

        if ( isset( $_SESSION['csv_categoryID'] ) ) {
            unset( $_SESSION['csv_categoryID'] );
        }

        $smarty->assign( "settings", settingGetSettings( CONF_CATALOG_CSV_IMPORT ) );
        # END загружаем CSV-файл любого размера

        # начальный вариант импорта стандартного CSV
        #last step of import = fill database with new content
        #configuration finished - update database
        if ( isset( $_POST["do_CSV_import"] ) && isset( $_POST["filename"] ) && isset( $_POST["update_column"] ) ) {
            if ( CONF_BACKEND_SAFEMODE ) {
                Redirect( ADMIN_FILE . "?dpt=catalog&sub=CSV_import&safemode=yes" ); //this action is forbidden when SAFE MODE is ON
            }

            @set_time_limit( 0 );

            //import file content
            if ( preg_match( "/^(.+?)\.csv(\.(bz2|gz))?$/", $_POST["filename"], $matches ) ) {
                if ( isset( $matches[2] ) && $matches[3] == 'gz' ) {
                    $data = myfgetcsvgz( $_POST["filename"], $delimiter );
                } else {
                    $data = myfgetcsv( $_POST["filename"], $delimiter );
                }
            }

            if ( !count( $data ) ) {
                die( ERROR_CANT_READ_FILE );
            }

            $res                  = imReadImportConfiguratorSettings();
            $db_association       = $res["db_association"];
            $dbcPhotos            = $res["dbcPhotos"];
            $dbc                  = $res["dbc"];
            $updated_extra_option = $res["updated_extra_option"];

            //get update column
            $uc = $dbc[$_POST["update_column"]];
            if ( !strcmp( $uc, "not defined" ) ) {
                //not set update column
                $smarty->assign( "CSV_import_result", "update_column_error" );
                //go to the previous step
                $proceed       = 1;
                $file_excel    = "";
                $file_CSV_name = $_POST["filename"];
                $res           = 1;
            } else {
                $begin             = time();
                $parents           = array(); //2 create a category tree
                $parents[0]        = 1;
                $currentCategoryID = 1;
                # csv_utf8
                if ( !$csv_utf8 ) {
                    $data = iconv_all( $data );
                }
                # end csv_utf8
                for ( $i = $_POST["number_of_titles_line"] + 1; $i < count( $data ); $i++ ) {
                    $a = time();
                    imImportRowToDataBase( $data[$i], $dbc, $uc, $dbcPhotos,
                        $updated_extra_option, $parents, $currentCategoryID );
                    $b = time();
                    //echo $data[$i][$dbc["name"]]." - ".($b-$a)."<br>";
                }
                $end = time();

                //update products count value if defined
                if ( CONF_UPDATE_GCV == 1 ) {
                    update_psCount( 1 );
                }

                $smarty->assign( "CSV_import_result", "ok" );

            }
        }

        $smarty->assign( "admin_sub_dpt", "catalog_CSV_import.tpl.html" );
    }
}
ini_set( 'display_errors', 0 ); // теперь сообщений НЕ будет
?>