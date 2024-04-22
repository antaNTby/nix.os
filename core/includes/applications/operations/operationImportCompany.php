<?php
// operationImportCompany.php
// jlog($_POST["Data"]);

function companyImportFromJson( $target_file_name, $Table) {

    global $PDO_connect;

    jlog( $target_file_name );

// Read the JSON file
    $json = file_get_contents( $target_file_name );

// Decode the JSON file
    $json_data = json_decode( $json, true );

// Display data
    // print_r($json_data);

    if (
        !isset( $json_data['company_name'] ) || !isset( $json_data['company_unp'] ) || !companCheckUNP( $json_data['company_unp'] )
    ) {

        return false;
    }

    $timestamp = get_microtime();
    $companyID = $json_data["companyID"];

    $dataPDO = [
        // "timestamp"        =>,
         "company_title"    => "Import from JSON `" . $json_data["site_url"] . "` " . $json_data["company_name"] . " id:{$companyID} at {$timestamp}",
        // "companyID"        => $json_data["companyID"],
         "company_name"     => $json_data["company_name"],
        "company_unp"      => $json_data["company_unp"],
        "company_okpo"     => $json_data["company_okpo"],
        "company_adress"   => $json_data["company_adress"],
        "company_bank"     => $json_data["company_bank"],
        "company_contacts" => $json_data["company_contacts"],
        "company_email"    => $json_data["company_email"],
        "company_data"     => $json_data["company_userdata"],
        "company_admin"    => $json_data["company_admindata"],
        "company_director" => $json_data["company_director"],

    ];

    // if ( $dataPDO["company_director"] ) {
    //     $arr_d                          = array();
    //     $arr_d                          = unserialize( $dataPDO["company_director"] );
    //     $dataPDO["director_nominative"] = $arr_d[0];
    //     $dataPDO["director_genitive"]   = $arr_d[1];
    //     $dataPDO["director_reason"]     = $arr_d[2];
    // }

    $bindings     = array();
    $dataPDO_Keys = array_keys( $dataPDO );
    $i_keys       = array( "read_only", "companyID" );

    for ( $ii = 0; $ii < count( $dataPDO_Keys ); $ii++ ) {
        $bindings[$ii]["key"]  = $dataPDO_Keys[$ii];
        $bindings[$ii]["val"]  = $dataPDO["$dataPDO_Keys[$ii]"];
        $bindings[$ii]["type"] = PDO::PARAM_STR;
        if ( in_array( $dataPDO_Keys[$ii], $i_keys ) ) {
            $bindings[$ii]["type"] = PDO::PARAM_INT;
        }
    }


    $sql_query =
    <<<SQL
    INSERT INTO `{$Table}` SET
    `company_title`=:company_title,
    `company_name`=:company_name,
    `company_unp`=:company_unp,
    `company_okpo`=:company_okpo,
    `company_adress`=:company_adress,
    `company_bank`=:company_bank,
    `company_contacts`=:company_contacts,
    `company_email`=:company_email,
    `company_data`=:company_data,
    `company_admin`=:company_admin,
    `company_director`=:company_director,
    `read_only`=0,
    `creation_time`=NOW(),
    `update_time`=NOW();
    SQL;

    $res = array();
    $res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 2 ); //

    if ( (int)$res > 0 ) {
        $lastID = (int)$res;
        jlog($lastID);
        return (int)$lastID;
    } else {
        return false;
    }


    return 55;
}

$filename = $data["Data"]["filename"];
// jlog( $data );

$target_dir       = "uploads/";
$target_file_name = $target_dir . basename( $filename );
$uploadOk         = 1;
$importFileType   = strtolower( pathinfo( $target_file_name, PATHINFO_EXTENSION ) );
$operation_result = "";

// Проверить, существует ли файл
if ( !basename( $filename ) ) {
    $operation_result .= " Файл не существует.";
    $uploadOk = 0;
}
if ( file_exists( $target_file_name ) && basename( $filename ) ) {
    $operation_result .= " Файл с таким именем '{$target_file_name}' уже существует.";
    $uploadOk = 1;
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

    $newCompanyID = companyImportFromJson( $target_file_name, $Table );

    if ( $newCompanyID > 0 ) {
        // $operation_result = "Файл " . basename( $_FILES["fileToUpload"]["name"] ) . " был загружен.";
        $operation_result = $newCompanyID;
    } else {
        $operation_result .= " При импорте из файла произошла ошибка.";
    }
}
// echo $operation_result; http://nixminsk.os/admin.php?dpt=custord&sub=companies&company_detailed=101223447&companyID=2594

$result      = 1;
$DO_RELOAD   = true;
$urlToReload = ADMIN_FILE . "?dpt=custord&sub=companies&company_detailed=1&companyID={$newCompanyID}";

// operationImportCompany.php
