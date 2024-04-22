<?php

function contrUpdateField( $contractID, $fieldDB, $new_value ) {
    $contractID=(int)$contractID;

    if ( $fieldDB != 'read_only' ) {
        $update_time_field = ", `update_time` = NOW()";
    } else { $update_time_field = "";}

    if (  ( $fieldDB != "dogovor_body" ) || ( $fieldDB != "schet_body" ) || ( $fieldDB != "warranty_body" ) ) {

        db_query( "UPDATE `" . CONTRACTS_TABLE .
            "` SET     `$fieldDB` = '" . xToText( $new_value ) . "'" . $update_time_field . " WHERE `contractID` =" . (int)$contractID );
    } else {
        db_query( "UPDATE `" . CONTRACTS_TABLE .
            "` SET     `$fieldDB` = '" . xEscSQL( $new_value ) . "'" . $update_time_field . " WHERE `contractID` =" . (int)$contractID );
    }
    return (int)$contractID;
}

function contrGetAllContracts() {

    $q    = db_query( "SELECT contractID, contract_title, dogovor_body, schet_body, warranty_body, read_only, update_time FROM " . CONTRACTS_TABLE );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }

    return $data;
}

function contrGetContract( $contractID ) {

    $q   = db_query( "SELECT * FROM `" . CONTRACTS_TABLE . "` WHERE `contractID` =" . (int)$contractID );
    $row = db_fetch_assoc( $q );
    return $row;

}

function contrUpdateContract( $contractID ) {

    return false;

}

function contrDeleteContract( $contractID ) {
    db_query( "DELETE FROM `" . CONTRACTS_TABLE . "` WHERE `contractID` = " . (int)$contractID );
}

function contrDuplicateContract( $contractID ) {

    $old = contrGetContract( $contractID );
    $contractID=(int)$contractID;
    $newname = "Copy of $contractID " . $old["contract_title"];
    $newD    = $old["dogovor_body"];
    $newS    = $old["schet_body"];
    $newW    = $old["warranty_body"];
// $newID= SELECT LAST_INSERT_ID();

    $sql = "
INSERT INTO `" . CONTRACTS_TABLE . "` ( `contractID`, `contract_title`, `dogovor_body`, `schet_body`, `warranty_body`, `read_only`, `update_time` )
SELECT LAST_INSERT_ID(), '$newname', '$newD', '$newS', '$newW', '0', NOW()
FROM `" . CONTRACTS_TABLE . "`
WHERE `contractID` = $contractID
LIMIT 1;
";
    db_query( $sql );
    return $contractID;
}

function contrLoadTexts( $contractID ) {
    $contractID=(int)$contractID;
    $sql = "SELECT `dogovor_body`, `schet_body`, `warranty_body` FROM `" . CONTRACTS_TABLE . "` WHERE `contractID`= '{$contractID}'";
    $q   = db_query( $sql );
    $row = db_fetch_assoc( $q );
    return $row;
}

function contrLoadContractText( $contractID, $type ) {
    $contractID=(int)$contractID;
    $sql = "SELECT `$type` FROM `" . CONTRACTS_TABLE . "` WHERE `contractID`= '$contractID' LIMIT 1;";
    $q   = db_query( $sql );
    $row = db_fetch_row( $q );
    return $row;
}

?>