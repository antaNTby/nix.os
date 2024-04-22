<?php
//
// operationUniteCompanies.php
$TableCompanies   = COMPANIES_TABLE;
$TableOrders      = ORDERS_TABLE;
$TableInvoices    = NANO_INVOICES_TABLE;
$TableOLDInvoices = INVOICES_TABLE;
$result      = false;


$companySample = $data["Data"]["companySample"];
$tempCompanies = json_decode( $data["Data"]["companiesToKill"] );
$_unp          = (int)$_GET["company_detailed"];

//убираем самусебя
// unset(   $companiesToKill[ array_search($companySample,$companiesToKill) ]   );
$companiesToKill = array_diff( $tempCompanies, [$companySample] );
sort( $companiesToKill, SORT_NUMERIC );
$goodCompanyID = (int)$companySample;

if($companiesToKill && $goodCompanyID)
{
$sql_query     = "";

$sql_query .= "UPDATE `{$TableOrders}` SET `companyID`={$goodCompanyID}  WHERE `companyID` IN (" . implode( ",", $companiesToKill ) . ");";

$sql_query .= "UPDATE `{$TableInvoices}` SET `sellerID`={$goodCompanyID}  WHERE `sellerID` IN (" . implode( ",", $companiesToKill ) . ");";
$sql_query .= "UPDATE `{$TableInvoices}` SET `buyerID`={$goodCompanyID} WHERE `buyerID` IN (" . implode( ",", $companiesToKill ) . ");";

$sql_query .= "UPDATE `{$TableOLDInvoices}` SET `sellerID`={$goodCompanyID}  WHERE `sellerID` IN (" . implode( ",", $companiesToKill ) . ");";
$sql_query .= "UPDATE `{$TableOLDInvoices}` SET `buyerID`={$goodCompanyID} WHERE `buyerID` IN (" . implode( ",", $companiesToKill ) . ");";

$sql_query .= "DELETE FROM `{$TableCompanies}`  WHERE `read_only`=0 AND `companyID` IN (" . implode( ",", $companiesToKill ) . ");";

$res = array();
$res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 1 ); //

$result      = $res;
$DO_RELOAD   = true;
$urlToReload = ADMIN_FILE . "?dpt=custord&sub=companies&company_detailed={$_unp}&companyID={$goodCompanyID}";
}

// $badCompanies  = [];
// foreach ( $companiesToKill as $key => $badValue ) {
//     $badCompanies[] = (int)$badValue;
//     $sql_query .= "UPDATE `{$TableOrders}` SET `companyID`={$goodCompanyID}  WHERE `companyID`={$badValue};";
//     $sql_query .= "UPDATE `{$TableInvoices}` SET `sellerID`={$goodCompanyID}  WHERE `sellerID`={$badValue};";
//     $sql_query .= "UPDATE `{$TableInvoices}` SET `buyerID`={$goodCompanyID} WHERE `buyerID`={$badValue};";
//     $sql_query .= "UPDATE `{$TableOLDInvoices}` SET `sellerID`={$goodCompanyID}  WHERE `sellerID`={$badValue};";
//     $sql_query .= "UPDATE `{$TableOLDInvoices}` SET `buyerID`={$goodCompanyID} WHERE `buyerID`={$badValue};";
//     $sql_query .= "\r\n--\r\n";
//     $sql_query .= "DELETE FROM `{$TableCompanies}`  WHERE `read_only`=0 AND `companyID`={$badValue};";
//     $sql_query .= "\r\n--\r\n";

// }






// operationUniteCompanies.php