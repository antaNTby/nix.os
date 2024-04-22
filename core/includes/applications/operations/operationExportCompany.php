<?php
//
// operationExportCompany.php
$sourceID = (int)$data["Data"]["ExportID"];

// jlog($sourceID);
$formDataArray = $data["Data"]["formData"];
$sql_query     = "SELECT * FROM {$Table} WHERE `companyID`=$sourceID;";
$data          = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0];
$timestamp     = get_microtime();

$companyData = [
    "timestamp"        => $timestamp,
    "site_url" => SITE_URL,
    "company_title"    => "Export from `" . SITE_URL . "` " . $data["company_name"] . " id:{$sourceID}",
    "companyID"        => $data["companyID"],
    "company_name"     => $data["company_name"],
    "company_unp"      => $data["company_unp"],
    "company_okpo"     => $data["company_okpo"],
    "company_adress"   => $data["company_adress"],
    "company_bank"     => $data["company_bank"],
    "company_contacts" => $data["company_contacts"],
    "company_email"    => $data["company_email"],
    "company_data"     => $data["company_userdata"],
    "company_admin"    => $data["company_admindata"],
    "company_director" => $data["company_director"],

];

if ( !$companyData["company_unp"] && !$companyData["company_name"] && !$companyData["company_adress"] && !$companyData["company_email"] ) {
    $companyData["company_unp"]    = "! ERROR !";
    $companyData["company_name"]   = "КОМПАНИЯ НЕ СУЩЕСТВУЕТ | НЕВЕРНЫЕ РЕКВИЗИТЫ";
    $companyData["company_adress"] = "КОМПАНИЯ НЕ СУЩЕСТВУЕТ | НЕВЕРНЫЕ РЕКВИЗИТЫ";
    $companyData["company_email"]  = "КОМПАНИЯ НЕ СУЩЕСТВУЕТ | НЕВЕРНЫЕ РЕКВИЗИТЫ";

    return $company;
}

if ( $companyData["company_director"] ) {

    $arr_d                              = array();
    $arr_d                              = unserialize( $companyData["company_director"] );
    $companyData["director_nominative"] = $arr_d[0];
    $companyData["director_genitive"]   = $arr_d[1];
    $companyData["director_reason"]     = $arr_d[2];
}

$fn = trim( $companyData["company_unp"] ) . "_" . trim( $companyData["companyID"] ) . "_export.json";
$fp = fopen( "uploads/" . $fn, "w+" );
fwrite( $fp, json_encode( $companyData, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE ) );
fclose( $fp );

// $res = array();
// $res = jlog( $companyData );

$operation_result = $fn;
$DO_RELOAD        = false;
$HAS_BODY = true;
// $urlToReload      = "uploads/" . $fn;

// operationExportCompany.php
