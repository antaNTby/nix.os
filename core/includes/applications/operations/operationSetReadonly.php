<?php
// SetReadonly
$res       = "";
$newValue  = (int)$data["Data"]["newValue"];
$companyID = (int)$data["Data"]["companyID"];

$sql_query       = "UPDATE `{$Table}` set `read_only`=".$newValue." WHERE `companyID`=".$companyID.";";

$res = array();
$res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 1 ); //

$result = $res;
