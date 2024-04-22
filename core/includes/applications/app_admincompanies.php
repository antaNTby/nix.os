<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2023-09-17       #
#          http://nixminsk.os            #
##########################################

$Table = COMPANIES_TABLE;
/*
##

company_title
company_name
company_unp
company_okpo
company_adress
company_bank
company_contacts
// company_email
company_userdata
company_admindata
company_director
read_only
companyID
update_time

##
 */

function _curlGetCompanyInfiByUNP( $unp ) {

    // $fake = [
    //     "row" => [
    //         "vunp"=> "100582333",
    //         "vnaimp"=> "Министерство по налогам и сборам Республики Беларусь",
    //         "vnaimk"=> "МНС",
    //         "vpadres"=> "г.Минск,ул.Советская,9",
    //         "dreg"=> "1994-06-30",
    //         "nmns"=> "104",
    //         "vmns"=> "Инспекция МНС по Московскому району г.Минска ",
    //         "ckodsost"=> "1",
    //         "vkods"=> "Действующий",
    //         "dlikv"=> null,
    //         "vlikv"=> null,
    //     ],
    // ];
    // return $fake;

    if ( function_exists( 'curl_init' ) ) {
        @$ch = curl_init();
        $url = "http://grp.nalog.gov.by/api/grp-public/data?unp=";

        curl_setopt( $ch, CURLOPT_URL, $url . $unp );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );

        $result = @curl_exec( $ch );
        $errnum = curl_errno( $ch );
        @curl_close( $ch );
    }

    $result = ( $errnum == "0" ) ? json_decode( $result, true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE ) : $errnum;

    return $result;
}

function _generateClausesForCompaniesData( $params ) {
    $where_clause = "";
    if ( is_array( $params ) && count( $params ) ) {
        _deletePercentSymbol( $params );
        $isFullTextSearch    = $params["isFullTextSearch"];
        $searchstring_clause = "";
        if ( isset( $params["searchstring"] ) and $params["searchstring"] != "" ) {
            $id_clause           = array();
            $title_clause        = array();
            $name_clause         = array();
            $unp_clause          = array();
            $okpo_clause         = array();
            $bank_clause         = array();
            $adress_clause       = array();
            $contacts_clause     = array();
            $director_clause     = array();
            $time_clause         = array();
            $concatedData_clause = array();
            foreach ( explode( " ", trim( $params["searchstring"] ) ) as $word ) {
                if ( strlen( $word ) >= 1 ) {
                    $id_clause[]       = "`companyID` LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $title_clause[]    = "lower(`company_title`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $name_clause[]     = "lower(`company_name`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $unp_clause[]      = "`company_unp` LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $okpo_clause[]     = "`company_okpo` LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $bank_clause[]     = "lower(`company_bank`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $adress_clause[]   = "lower(`company_adress`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $contacts_clause[] = "lower(`company_contacts`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $director_clause[] = "lower(`company_director`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $time_clause0[]    = "lower(`creation_time`) LIKE '%" . ( strtolower( $word ) ) . "%'";
                    $time_clause[]     = "lower(`update_time`) LIKE '%" . ( strtolower( $word ) ) . "%'";

                    $concatedData_clause[] = " CONCAT_WS(' ',
                    lower(`company_title`),
                    lower(`company_name`),
                    `company_unp`,
                    `company_okpo`,
                    lower(`company_bank`),
                    lower(`company_adress`),
                    lower(`company_contacts`),
                    lower(`company_director`),
                    lower(`creation_time`),
                    lower(`update_time`)
                        ) LIKE '%" . ( strtolower( $word ) ) . "%'";
                }
            }
            $searchstring_clause = "(" . implode( " and ", $id_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $title_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $name_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $unp_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $okpo_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $bank_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $adress_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $contacts_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $director_clause ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $time_clause0 ) . ")";
            $searchstring_clause .= " OR " . "(" . implode( " and ", $time_clause ) . ")";
            $concated_clause = "(" . implode( " and ", $concatedData_clause ) . ")"; // ищем по всему тексту, получаемому слиянием всех полей

        }
        switch ( $isFullTextSearch ) {
            case "0":
                // ищем по каждому полу отдельно, в котором содержатся все нужные слова в любом порядке
                if ( $searchstring_clause ) {
                    $where_clause .= ( $where_clause == "" ) ? "    ( {$searchstring_clause} )" : "  AND  ( {$searchstring_clause} )";
                }
                break;
            case "1":
                // ищем по всему тексту, получаемому слиянием всех полей
                if ( $concated_clause ) {
                    $where_clause .= ( $where_clause == "" ) ? "    ( {$concated_clause} )" : "  AND  ( {$concated_clause} )";
                }
                break;

            default:
                if ( $searchstring_clause ) {
                    $where_clause .= ( $where_clause == "" ) ? "    ( {$searchstring_clause} )" : "  AND  ( {$searchstring_clause} )";
                }
                break;
        }
    } //is_array( $params ) && count( $params )
    return RN( $where_clause );
}

if ( isset( $_GET["file_upload"] ) && $_GET["app"] == "app_admincompanies" ) {
// загрузка файла
    $target_dir       = "uploads/";
    $target_file      = $target_dir . basename( $_FILES["fileToUpload"]["name"] );
    $uploadOk         = 1;
    $importFileType   = strtolower( pathinfo( $target_file, PATHINFO_EXTENSION ) );
    $operation_result = "";

// Проверить, существует ли файл
    if ( !basename( $_FILES["fileToUpload"]["name"])) {
        $operation_result .= " Файл не существует." ;
        $uploadOk = 0;
    }
    if ( file_exists( $target_file ) && basename( $_FILES["fileToUpload"]["name"])) {
        $operation_result .= " Файл с таким именем '{$target_file}' уже существует." ;
        $uploadOk = 0;
    }
// Проверить размер файла
    if ( $_FILES["fileToUpload"]["size"] > 5000000 ) {
        $operation_result = " Ваш файл слишком большой.";
        $uploadOk         = 0;
    }
// Разрешить определенные форматы файлов
    if ( $importFileType != "txt" && $importFileType != "json" ) {
        $operation_result .= " Только .txt и .json файлы разрешены.";
        $uploadOk = 0;
    }
// Проверить, не установлен ли $uploadOk в 0 по ошибке
    if ( $uploadOk == 0 ) {
        $operation_result .= " Загрузка отменена.";
// если все в порядке, попробуйте загрузить файл
    } else {
        if ( move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file ) ) {
            // $operation_result = "Файл " . basename( $_FILES["fileToUpload"]["name"] ) . " был загружен.";
            $operation_result = "Файл загружен.";
        } else {
            $operation_result .= " При загрузке файла произошла ошибка.";
        }
    }
     echo $operation_result;
}

if ( isset( $_GET["app"] ) && $_GET["app"] == "app_admincompanies" && !isset( $_GET["file_upload"] ) ) {

    $data = json_decode( file_get_contents( "php://input" ), true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE );

    $result = false;

    if ( isset( $_GET["operation"] ) ) {

        $operation = $_GET["operation"];
        $DO_RELOAD = false;
        $HAS_BODY  = false;

        $TableCompanies   = COMPANIES_TABLE;
        $TableOrders      = ORDERS_TABLE;
        $TableInvoices    = NANO_INVOICES_TABLE;
        $TableOLDInvoices = INVOICES_TABLE;

        switch ( $operation ) {

            case "SetReadonly":
                include_once "operations/operationSetReadOnly.php";
                break;
            case "SaveAll":
                include_once "operations/operationSaveAll.php";
                break;
            case "CloneCompany":
                include_once "operations/operationCloneCompany.php";
                break;
            case "ExportCompany":
                include_once "operations/operationExportCompany.php";
                break;
            case "ImportCompany":
                include_once "operations/operationImportCompany.php";
                break;
            case "UniteCompanies":
                include_once "operations/operationUniteCompanies.php";
                break;
            case "KillCompany":
                $TableCompanies = COMPANIES_TABLE;
                $KillID         = $data["Data"]["KillID"];
                $sql_query .= "DELETE FROM `{$TableCompanies}` WHERE `read_only`=0 AND `companyID`=" . (int)$KillID . ";";
                $res = array();
                $res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 1 ); //

                $result      = $res;
                $DO_RELOAD   = true;
                $urlToReload = ADMIN_FILE . "?dpt=custord&sub=companies";
                break;
            case "TerminateCompany":
                $TableCompanies = COMPANIES_TABLE;
                $KillID         = $data["Data"]["KillID"];
                $sql_query .= "DELETE FROM `{$TableCompanies}` WHERE `companyID`=" . (int)$KillID . ";";
                $res         = array();
                $res         = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 1 ); //
                $result      = $res;
                $DO_RELOAD   = true;
                $urlToReload = ADMIN_FILE . "?dpt=custord&sub=companies";
                break;
            case "AddCompany":
                include_once "operations/operationAddCompany.php";
                break;
            case "CorsCompany":
                include_once "operations/operationCorsCompany.php";
                break;
            case "LinkCompany":
                $companyID   = (int)$data["Data"]["companyID"];
                $toOrderID   = (int)$data["Data"]["toOrderID"];
                $toInvoiceID = (int)$data["Data"]["toInvoiceID"];

                $sql_query = "UPDATE " . ORDERS_TABLE . " SET `companyID`=" . (int)$companyID . " WHERE `orderID`=" . (int)$toOrderID . "; ";
                $sql_query .= "UPDATE " . NANO_INVOICES_TABLE . " SET `buyerID`=" . (int)$companyID . " WHERE `orderID`=" . (int)$toOrderID . " AND `invoiceID`=" . (int)$toInvoiceID . "; ";

                $res         = array();
                $res         = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 1 ); //
                $result      = 1;
                $DO_RELOAD   = true;
                $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID={$toOrderID}&company_selected={$companyID}";
                break;

        } // switch ( $operation )

        if ( $DO_RELOAD ) {

            echo $urlToReload;

        } else {

            if ( $operation == "CorsCompany" ) {
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( $operation_result, JSON_INVALID_UTF8_IGNORE );
            } elseif ( $operation == "ExportCompany" ) {
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( $operation_result, JSON_INVALID_UTF8_IGNORE );
            } else {

                if ( $result ) {
                    echo "SUCCESS";
                } else {
                    echo "FAILED";
                }
            }

        }

    } // isset( $_GET["operation"] )

    if ( isset( $_GET["DataTable"] ) && ( $_GET["DataTable"] == "getShortCompaniesData" ) ) {
        $res = array();

        $whereAll    = null;
        $whereResult = null;

        if ( isset( $_POST["DATA"]["params"] ) && count( $_POST["DATA"]["params"] ) ) {
            $params      = $_POST["DATA"]["params"];
            $whereResult = _generateClausesForCompaniesData( $params );
        }

        $primaryKey = "companyID";

        $DT_columns = array(
            array( "db" => "companyID", "dt" => "id" ),
            array( "db" => "company_title", "dt" => "title" ),
            array( "db" => "company_name", "dt" => "name" ),
            array( "db" => "company_unp", "dt" => "unp" ),
            array( "db" => "company_adress", "dt" => "adress" ),
            array( "db" => "company_bank", "dt" => "bank" ),
            array( "db" => "company_contacts", "dt" => "contacts" ),
            array( "db" => "read_only", "dt" => "read_only" ),
            array( "db" => "creation_time", "dt" => "creation_time" ),
            array( "db" => "update_time", "dt" => "update_time" ),
            array( "db" => "company_director", "dt" => "director_serialized" ),
        );

        $res = array();
        $res = adminSSP::complex( $_POST, $PDO_connect, $Table, $primaryKey, $DT_columns, $whereResult, $whereAll ); //

        for ( $ii = 0; $ii < count( $res["data"] ); $ii++ ) {

            $arr_d = array();
            $arr_d = unserialize( $res["data"][$ii]["director_serialized"] );

            $res["data"][$ii]["director_nominative"] = $arr_d[0];
            $res["data"][$ii]["director_genitive"]   = $arr_d[1];
            $res["data"][$ii]["director_reason"]     = $arr_d[2];
            // code...
            $res["data"][$ii]["requisites"] = <<<HTML
                    <strong>{$res["data"][$ii]["name"]} / УНП {$res["data"][$ii]["unp"]}</strong><br>
                    {$res["data"][$ii]["adress"]}<br>
                    {$res["data"][$ii]["bank"]}<br>
                    <em>{$res["data"][$ii]["contacts"]}</em>.<br>
                    {$res["data"][$ii]["director_nominative"]}
                    HTML;

                }

        $operation_result = $res;
        header( "Content-Type: application/json; charset=utf-8" );
        die( json_encode( $operation_result ) );
     }

}




?>