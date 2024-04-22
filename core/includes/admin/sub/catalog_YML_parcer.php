<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-09-06       #
#          http://nixby.pro              #
##########################################

ini_set( 'display_errors', 1 ); // сообщения с ошибками будут показываться
error_reporting( E_ALL );       // E_ALL - отображаем ВСЕ ошибки

$CategoriesTable      = CATEGORIES_TABLE;
$ProductsTable        = PRODUCTS_TABLE;
$GroupsTable          = GROUPS_TABLE__NIXRU;
$GoodsTable = GOODS_TABLE__NIXRU;

$CatalogueFolder        = "data/files/NixRuCatalogueFolder/";
$Catalogue_FileName     = "nix_yml_full_fmdfi232842y12.xml";
$Catalogue_FileName_old = "NIX_YML_FULL.XML";

$offer_filename    = $CatalogueFolder . "x_offer.json";
$pictures_filename = $CatalogueFolder . "x_pictures.json";
$params_filename   = $CatalogueFolder . "x_params.json";

$smarty->assign( "dir_name", $CatalogueFolder );

if ( file_exists( $offer_filename ) ) {
    $smarty->assign( "offer_filename", $offer_filename );
}

if ( file_exists( $pictures_filename ) ) {
    $smarty->assign( "pictures_filename", $pictures_filename );
}

if ( file_exists( $params_filename ) ) {
    $smarty->assign( "params_filename", $params_filename );
}

$Catalogue_FileNameWithPath = "data/files/xmlNixRu/" . $Catalogue_FileName;
if ( file_exists( $Catalogue_FileNameWithPath ) ) {
    $smarty->assign( "Catalogue_FileNameWithPath", $Catalogue_FileNameWithPath );
}

//products and categories catalog import from MS Excel .CSV files
if ( !strcmp( $sub, "YML_parcer" ) ) {

    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 5, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        ### Import From XML

        $vendorID = 1;

        $status = groupStatusByVendor( $vendorID );
        $smarty->assign( "status", $status );

        if ( file_exists( $Catalogue_FileNameWithPath ) ) {
            $smarty->assign( "file_exists_YML_offers", 1 );
            // инициализация ридера
            include_once "core/classes/class.ConfigXMLReader__offers.php";
            $reader2 = new ConfigXMLOffersReader( $Catalogue_FileNameWithPath );
        }

        if ( isset( $_POST["operation"] ) && isset( $_POST["priceType"] ) && isset( $_GET["parceYML"] ) ) {
            $Result = array();

            $operation = trim( $_POST["operation"] );
            $priceType = trim( $_POST["priceType"] );

            @set_time_limit( 0 );
            # обработка старых продуктов, которые уже есть в базе
            $Result["start_time"] = get_current_time();

            try {
                switch ( $operation ) {

################################################################################################
                    case "parseNixRuCatalogue":
################################################################################################

                        @set_time_limit( 0 );

                        $reader2->cleardbtables();

                        if ( file_exists( "adminer.sql" ) ) {
                            unlink( "adminer.sql" );
                        }

                        # чтобы не тратить память на хранение ненужных элементов, мы их просто выбрасываем на каждой итерации
                        $reader2->onEvent( "afterParseElement", function ( $name, $context ) {
                            $context->clearResult();
                        } );

                        $reader2->onEvent( 'parseoffer', function ( $context ) {
                            $getResult     = $context->getResult();
                            $parsed_result = $getResult;
                            // cash_sql_query( json_encode($parsed_result));
                            // saveOfferToSQL( $parsed_result );

                            $CatalogueFolder = "data/files/NixRuCatalogueFolder/";

                            if ( !is_null( $parsed_result["info"] ) && isset( $parsed_result["info"] ) ) {
                                writeResultToFile( json_encode( $parsed_result["info"] ), "x_info", $CatalogueFolder );
                            }
                            if ( !is_null( $parsed_result["offer"] ) && isset( $parsed_result["offer"] ) ) {
                                writeResultToFile( json_encode( $parsed_result["offer"] ), "x_offer", $CatalogueFolder );
                            }
                            if ( !is_null( $parsed_result["pictures"] ) && isset( $parsed_result["pictures"] ) ) {
                                writeResultToFile( json_encode( $parsed_result["pictures"] ), "x_pictures", $CatalogueFolder );
                            }
                            if ( !is_null( $parsed_result["params"] ) && isset( $parsed_result["params"] ) ) {
                                writeResultToFile( json_encode( $parsed_result["params"] ), "x_params", $CatalogueFolder );
                            }

                        } );

                        // $reader2->onEvent( 'parsevendor', function ( $context ) {
                        //     $getResult     = $context->getResult();
                        //     $parsed_result = $getResult['Offer'];
                        //     saveBMtP( $parsed_result );
                        // } );

                        // $reader2->onEvent( 'parsepicture', function ( $context ) {
                        //     $getResult     = $context->getResult();
                        //     $parsed_result = $getResult['Offer']['pictures'];
                        //     // console($parsed_result);
                        //     // if ( !is_null( $parsed_result ) ) {
                        //         savePictures( $parsed_result );
                        //     // }//
                        // } );

                        # запускаем парсинг
                        $_SESSION["PARCING_ACTIVE"] = true;
                        $reader2->parse();
                        // $Result = $reader->getResult();
                        $_SESSION["PARCING_ACTIVE"] = false;

                        // $reader2->close();
                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = " парсим " . $Catalogue_FileNameWithPath;
                        $Result["CONTINUE"]             = "reloadPage"; //false;//"showInfo";

                        break;

################################################################################################
                    case "parse_x_offer":
################################################################################################
                        $data = array();
                        if ( file_exists( $offer_filename ) ) {
                            // debugfile( $offer_filename, "parse_x_offer" );

                            $json_handle = @fopen( $offer_filename, "r" );

                            if ( isset( $_SESSION["parser"]["json_ftell"] ) ) {
                                fseek( $json_handle, $_SESSION["parser"]["json_ftell"] );
                            } else {
                                $_SESSION["parser"]["json_rowcnt"] = 0;
                            }

                            $data         = array();
                            $EOF          = false;
                            $EMPTY_STRING = false;
                            $sql_chunk    = "";

                            for ( $i = 0; (  ( $i++ < 1000 ) && ( !feof( $json_handle ) ) ); ) {

                                $json_string = (string)fgets( $json_handle ); // читает строку из цсв файла

                                if ( !$json_string ) {
                                    $EMPTY_STRING = true;
                                    continue; //если строки нет то пропускаем
                                }

                                $data              = json_decode( $json_string, true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE );
                                $json_decode_error = json_last_error_msg();
                                if ( $json_decode_error != 'No error' ) {
                                    $EMPTY_STRING = true;
                                    continue; //если строки нет то пропускаем
                                }

                                $_SESSION["parser"]['json_rowcnt']++;

                                $sql_insert = saveOfferToSQL( $data );
                                $sql_chunk .= $sql_insert;
                            }

                            $_SESSION["parser"]['json_ftell'] = ftell( $json_handle ); //ftell — Возвращает текущую позицию указателя чтения/записи файла

                            if ( feof( $json_handle ) ) {
                                $EOF                            = true;
                                $Result["SERVER_RESULT_STRING"] = "УСПЕШНО ЗАВЕРШЕНО" . " rowcnt " . $_SESSION["parser"]["json_rowcnt"];
                                if ( isset( $_SESSION["parser"]['json_ftell'] ) ) {
                                    unset( $_SESSION["parser"]['json_ftell'] );
                                }
                            } else {
                                $EOF                            = false;
                                $Result["SERVER_RESULT_STRING"] = "parse_x_offer " . " rowcnt " . $_SESSION["parser"]["json_rowcnt"] . "  err: " . $json_decode_error;
                            }

                            fclose( $json_handle );

                            if ( strlen( $sql_chunk ) ) {
                                db_multiquery( $sql_chunk );
                            }

                            $Result["ERROR"]    = false;
                            $Result["CONTINUE"] = ( !$EOF ) ? "parse_x_offer" : "reloadPage";

                        } else {
                            $Result["ERROR"]                = true;
                            $Result["SERVER_RESULT_STRING"] = "ERROR Файл отсутствует";
                            $Result["CONTINUE"]             = false;
                        }

                        break;
################################################################################################
                    case "parse_x_pictures":
                    case "parse_x_params":
################################################################################################
                        if ( $operation == "parse_x_pictures" ) {
                            $parsed_filename = $pictures_filename;
                            $chunk_limit     = 1000;
                        } elseif ( $operation == "parse_x_params" ) {
                            $parsed_filename = $params_filename;
                            $chunk_limit     = 300;
                        } else {
                            break;
                        }

                        $data = array();
                        if ( file_exists( $parsed_filename ) ) {

                            $json_handle = @fopen( $parsed_filename, "r" );
                            if ( isset( $_SESSION["parser"]["json_ftell"] ) ) {
                                fseek( $json_handle, $_SESSION["parser"]["json_ftell"] );
                            } else {
                                $_SESSION["parser"]["json_rowcnt"] = 0;
                            }

                            $data         = array();
                            $EOF          = false;
                            $EMPTY_STRING = false;
                            $sql_chunk    = "";

                            for ( $i = 0; (  ( $i++ < $chunk_limit ) && ( !feof( $json_handle ) ) ); ) {

                                $json_string = (string)fgets( $json_handle ); // читает строку из цсв файла

                                if ( !$json_string ) {
                                    $EMPTY_STRING = true;
                                    continue; //если строки нет то пропускаем
                                }

                                $data              = json_decode( $json_string, true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE );
                                $json_decode_error = json_last_error_msg();
                                if ( $json_decode_error != 'No error' ) {
                                    $EMPTY_STRING = true;
                                    continue; //если строки нет то пропускаем
                                }

                                $_SESSION["parser"]['json_rowcnt']++;

                                if ( $operation == "parse_x_pictures" ) {
                                    $sql_insert = savePicturesToSQL( $data );
                                } elseif ( $operation == "parse_x_params" ) {
                                    $sql_insert = saveParamsToSQL( $data );
                                }

                                $sql_chunk .= $sql_insert;
                            }

                            $_SESSION["parser"]['json_ftell'] = ftell( $json_handle ); //ftell — Возвращает текущую позицию указателя чтения/записи файла

                            if ( feof( $json_handle ) ) {
                                $EOF                            = true;
                                $Result["SERVER_RESULT_STRING"] = "УСПЕШНО ЗАВЕРШЕНО" . " rowcnt " . $_SESSION["parser"]["json_rowcnt"];
                                if ( isset( $_SESSION["parser"]['json_ftell'] ) ) {
                                    unset( $_SESSION["parser"]['json_ftell'] );
                                }
                            } else {
                                $EOF                            = false;
                                $Result["SERVER_RESULT_STRING"] = "{$operation} " . "; rowcnt " . $_SESSION["parser"]["json_rowcnt"] . ";  err: " . $json_decode_error;
                            }

                            fclose( $json_handle );

                            if ( strlen( $sql_chunk ) ) {
                                #############  console($sql_chunk);
                                db_multiquery( $sql_chunk );
                            }

                            $Result["ERROR"]    = false;
                            $Result["CONTINUE"] = ( !$EOF ) ? "{$operation}" : "reloadPage";

                        } else {
                            $Result["ERROR"]                = true;
                            $Result["SERVER_RESULT_STRING"] = "ERROR Файл отсутствует";
                            $Result["CONTINUE"]             = false;
                        }

                        break;
################################################################################################
                    case "deleteCatalogue_x_files":
################################################################################################

                        if ( file_exists( $CatalogueFolder . "x_info.json" ) ) {
                            unlink( $CatalogueFolder . "x_info.json" );
                        }

                        if ( file_exists( $offer_filename ) ) {
                            unlink( $offer_filename );
                        }

                        if ( file_exists( $pictures_filename ) ) {
                            unlink( $pictures_filename );
                        }

                        if ( file_exists( $params_filename ) ) {
                            unlink( $params_filename );
                        }
                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = "Очистка {$CatalogueFolder} завершена";
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "deletePictureDuplicates":
################################################################################################
                        $SQL_process = <<<SQL

SELECT COUNT(*) AS repetition,
       img_url,
       offer_id
FROM `PRICE_Pictures`
GROUP BY img_url,
         offer_id HAVING repetition > 1
ORDER BY repetition DESC LIMIT 100;


CREATE TABLE `temp_pictures2` (`photoID` INT (11) NOT NULL, `offer_id` INT (11) NOT NULL, `img_url` TEXT, PRIMARY KEY (`offer_id`,`img_url`(255)));


INSERT
IGNORE INTO `temp_pictures2`
SELECT `photoID`,
       `offer_id`,
       `img_url`
FROM `PRICE_Pictures`
ORDER BY `photoID` DESC;


ALTER TABLE `temp_pictures2` ADD INDEX `photoID` (`photoID`),
                                      ADD INDEX `offer_id` (`offer_id`);


UPDATE `PRICE_Pictures` t1
SET t1.flag = 0;


UPDATE `PRICE_Pictures` t1
RIGHT JOIN `temp_pictures2` t2 ON (t1.photoID= t2.photoID)
SET t1.flag = 1;


DELETE
FROM `PRICE_Pictures`
WHERE `flag` = 0;


DROP TABLE `temp_pictures2`;


SELECT COUNT(*) AS repetition,
       img_url,
       offer_id
FROM `PRICE_Pictures`
GROUP BY img_url,
         offer_id HAVING repetition > 1
ORDER BY repetition DESC LIMIT 100;
SQL;


$Result["SERVER_RESULT_STRING"] ="";
// result
// foreach ( db_mysqli($SQL_process) as $key => $value ) {
// }
    $res=db_mysqli($SQL_process);
    $Result["DB_MYSQLI_RESULTS"] .= json_encode($res);


                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] .= "Очистка deletePictureDuplicates завершена";
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "deleteParamDuplicates":
################################################################################################
$SQL_process = <<<SQL
    SELECT COUNT(*) AS repetition,
       title,
       offer_id
FROM `PRICE_Params`
GROUP BY title,
         offer_id HAVING repetition > 1
ORDER BY repetition DESC LIMIT 100;


CREATE TABLE `temp_parameters` (`paramID` INT (11) NOT NULL, `offer_id` INT (11) NOT NULL, `index` INT (11) NOT NULL, `title` TEXT, `value` TEXT, PRIMARY KEY (offer_id,title(255)));


INSERT
IGNORE INTO `temp_parameters`
SELECT `paramID`,
       `offer_id`,
       `index`,
       `title`,
       `value`
FROM `PRICE_Params`
ORDER BY `paramID` DESC;


ALTER TABLE `temp_parameters` ADD INDEX `paramID` (`paramID`),
                                        ADD INDEX `offer_id` (`offer_id`);


UPDATE `PRICE_Params` t1
SET t1.flag = 0;


UPDATE `PRICE_Params` t1
RIGHT JOIN `temp_parameters` t2 ON (t1.paramID= t2.paramID)
SET t1.flag = 1;


SELECT COUNT(*) AS flag1
FROM `PRICE_Params`
WHERE `flag` = 1;


SELECT COUNT(*) AS flag0
FROM `PRICE_Params`
WHERE `flag` != 1;


SELECT COUNT(*) AS total
FROM `PRICE_Params`;


DELETE
FROM `PRICE_Params`
WHERE `flag` = 0;


DROP TABLE `temp_parameters`;


SELECT COUNT(*) AS repetition,
       title,
       offer_id
FROM `PRICE_Params`
GROUP BY title,
         offer_id HAVING repetition > 1
ORDER BY repetition DESC LIMIT 100;
SQL;


$Result["SERVER_RESULT_STRING"] ="";
// result
// foreach ( db_mysqli($SQL_process) as $key => $value ) {
// }
    $res=db_mysqli($SQL_process);
    $Result["DB_MYSQLI_RESULTS"] .= json_encode($res);


                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] .= "Очистка deleteParamDuplicates завершена";
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    default:
################################################################################################
                        $Result["ERROR"]                = true;
                        $Result["SERVER_RESULT_STRING"] = "ОШИБКА ИМПОРТА";
                        $Result["CONTINUE"]             = false;
                        break;

################################################################################################
                } // switch

                $Result["finish_time"]  = get_current_time();
                $diff                   = -( strtotime( $Result["start_time"] ) - strtotime( $Result["finish_time"] ) );
                $Result["diff_seconds"] = $diff - (int)( $diff / 60 ) * 60;                 // Разница между (секунды)
                $Result["diff_days"]    = ( $diff - (int)( $diff / 86400 ) * 86400 ) / 86400; // Разница между (дни)

                $Result["SERVER_RESULT_STRING"] .= "<br> Начало в: " . $Result["start_time"] . ", ";
                $Result["SERVER_RESULT_STRING"] .= " Завершено в: " . $Result["finish_time"] . ", ";
                $Result["SERVER_RESULT_STRING"] .= " Затрачено " . $Result["diff_seconds"] . " сек.";

                $ajaxResponse = $Result;

                ( $Result["ERROR"] == true ) ? header_status( 500 ) : header_status( 200 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( $ajaxResponse );

            } catch ( Exception $e ) {
                // Возвращаем клиенту ответ с ошибкой
                header_status( 500 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( array(
                    "message"              => $e->getMessage(),
                    "SERVER_RESULT_STRING" => null,
                    "POST"                 => $_POST,
                    "GET"                  => $_GET,
                ) );
            }
            die();
        } //         if ( isset( $_POST["operation"]
    }

    $adminer_link = "adminer.php?username=" . DB_USER . "&amp;db=" . DB_NAME . "&amp;select=" . "PRICE_Offers";

    $smarty->assign( "adminer_link", $adminer_link );
    $smarty->assign( "admin_sub_dpt", "catalog_YML_parcer.tpl.html" );

    if ( isset( $_SESSION["parser"]['json_ftell'] ) ) {
        unset( $_SESSION["parser"]['json_ftell'] );
    }

    if ( isset( $_SESSION["parser"]['json_rowcnt'] ) ) {
        unset( $_SESSION["parser"]['json_rowcnt'] );
    }

}

# ini_set( 'display_errors', 0 ); // теперь сообщений НЕ будет

?>