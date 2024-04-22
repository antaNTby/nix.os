<?php
//
// operationCloneCompany.php
$sourceID      = (int)$data["Data"]["companyID"];
$formDataArray = $data["Data"]["formData"];
$sql_query     = "SELECT * FROM {$Table} WHERE `companyID`=$sourceID;";
$companyData   = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0];
$dataPDO       = [
    "company_title"    => "Копия " . $companyData["company_name"] . " id:{$sourceID}",
    "company_name"     => $companyData["company_name"],
    "company_unp"      => $companyData["company_unp"],
    "company_okpo"     => $companyData["company_okpo"],
    "company_adress"   => $companyData["company_adress"],
    "company_bank"     => $companyData["company_bank"],
    "company_contacts" => $companyData["company_contacts"],
    "company_email"    => $companyData["company_email"],
    "company_data"     => $companyData["company_userdata"],
    "company_admin"    => $companyData["company_admindata"],
    "company_director" => $companyData["company_director"],

];
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
$sql_query = <<<SQL
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
$res         = array();
$res         = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 2 ); //
$lastID      = $res;
$result      = $res;
$DO_RELOAD   = true;
$urlToReload = ADMIN_FILE . "?dpt=custord&sub=companies&company_detailed={$companyData['company_unp']}&companyID={$result}";

// operationCloneCompany.php
