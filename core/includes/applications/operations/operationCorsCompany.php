<?php
//
// operationCorsCompany.php
$res              = [];
$operation_result = [];
$body             = "";
$unp              = checkUNP( $data["Data"]["unp"] );

if ( $unp ) {
    $res = _curlGetCompanyInfiByUNP( $unp );
    if ( $res ) {
        $body = $res;
    }

    $HAS_BODY = true;
    if ( is_array( $body["row"] ) ) {
        $operation_result = $body["row"];
    } else {
     $operation_result = false;
    }

} else {
    $operation_result = false;
}

// operationCorsCompany.php
################################################################################################
/*
REST API. Получение сведений по юридическим лицам
Пример запроса: http://grp.nalog.gov.by/api/grp-public/data?unp=100582333&charset=UTF-8&type=json

Параметры запроса:
unp - уникальный номер плательщика;
charset - кодировка ответа (UTF-8, windows-1251 и т.д.), необязательный параметр;
type - формат ответа (JSON, XML), необязательный параметр;

Возвращается документ, указанного формата, содержащий элементы:
VUNP – УНП плательщика;
VNAIMP – полное наименование плательщика;
VNAIMK – краткое наименование плательщика;
DREG – дата постановки на учет;
NMNS – код инспекции МНС;
VMNS – наименование инспекции МНС;
CKODSOST – код состояния плательщика;
DLIKV – дата изменения состояния плательщика;
VLIKV – основание изменения состояния плательщика;
 */
################################################################################################
