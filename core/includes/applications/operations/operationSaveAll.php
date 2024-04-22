<?php
//
// operationSaveAll.php
$companyID     = (int)$data["Data"]["companyID"];
$read_only     = (int)$data["Data"]["read_only"];
$formDataArray = $data["Data"]["formData"];

$pm_Names  = array();
$db_Names  = array();
$pm_Values = array();

$director = array();

for ( $ii = 0; $ii < count( $formDataArray ); $ii++ ) {
    // code...
    $pm_Names[$ii]  = $formDataArray[$ii][0];
    $db_Names[$ii]  = str_replace( 'pm_', 'company_', $pm_Names[$ii] );
    $pm_Values[$ii] = $formDataArray[$ii][1];

    if ( $db_Names[$ii] == "company_director_nominative" ) {
        $director[0] = $pm_Values[$ii];
    }
    if ( $db_Names[$ii] == "company_director_genitive" ) {
        $director[1] = $pm_Values[$ii];
    }
    if ( $db_Names[$ii] == "company_director_reason" ) {
        $director[2] = $pm_Values[$ii];
    }

}

array_push( $db_Names, "company_director" );
array_push( $pm_Values, serialize( $director ) );

array_push( $db_Names, "read_only" );
array_push( $pm_Values, (int)$read_only );

array_push( $db_Names, "companyID" );
array_push( $pm_Values, (int)$companyID );

$companyData = array_combine( $db_Names, $pm_Values );

$fields = "`" . implode( "`, `", $db_Names ) . "`";
$values = "'" . implode( "', '", xToText( $pm_Values ) ) . "'";

$dataPDO = [

    'company_title'    => $companyData["company_title"],
    'company_name'     => $companyData["company_name"],
    'company_unp'      => $companyData["company_unp"],
    'company_okpo'     => $companyData["company_okpo"],
    'company_adress'   => $companyData["company_adress"],
    'company_bank'     => $companyData["company_bank"],
    'company_contacts' => $companyData["company_contacts"],
    'company_email'    => $companyData["company_email"],
    'company_data'     => $companyData["company_userdata"],
    'company_admin'    => $companyData["company_admindata"],
    'company_director' => $companyData["company_director"],
    'update_time'      => date( "Y-m-d H:i:s" ),
    'companyID'        => $companyData["companyID"],

];
// 'read_only'        => $companyData["read_only"],

$bindings = array();

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

    UPDATE `{$Table}` SET

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
    `update_time`=:update_time

    WHERE `companyID`=:companyID ;

SQL;
// `read_only`=:read_only,

$res = array();
$res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 1 ); //

$result = $res;
// operationSaveAll.php
//
