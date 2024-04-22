<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2023-09-17       #
#          http://nixminsk.os            #
##########################################

/*

-- Adminer 4.8.1 MySQL 5.7.39-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `nano_invoices`;
CREATE TABLE `nano_invoices` (
`invoice_time` timestamp NOT NULL COMMENT 'дата выставления счета',
`invoiceID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ',
`orderID` int(11) DEFAULT NULL COMMENT 'ID заказа',
`module_id` int(11) DEFAULT '100' COMMENT 'ID модуля формирования счета',
`sellerID` int(11) DEFAULT NULL COMMENT 'ID Продавца',
`buyerID` int(11) DEFAULT NULL COMMENT 'ID Покупателя',
`currency_rate` float NOT NULL DEFAULT '1' COMMENT 'Курс без НДС и без наценок, по которому выставляем счет',
`contractID` int(11) DEFAULT NULL COMMENT 'ID текстов договоров',
`purposeID` varchar(255) DEFAULT '0' COMMENT 'Цели покупки - кодЫ',
`fundingID` varchar(255) DEFAULT '0' COMMENT 'Источники финансировния - код',
`DeliveryType` int(2) NOT NULL DEFAULT '0' COMMENT 'Тип доставки - код: 0 -самовывоз, 1-доставка',
`PaymentType` int(2) NOT NULL DEFAULT '0' COMMENT 'Оплата: 0-полная предоплата, 1-оплата по факту поставки',
`actuality_termin` varchar(40) DEFAULT '3' COMMENT 'срок действия счета',
`delivery_termin` varchar(40) DEFAULT '10' COMMENT 'срок_поставки товара',
`payment_termin` varchar(40) DEFAULT '1' COMMENT 'срок полной оплаты, равен actuality_termin, в случае 100% -ой предоплаты',
`payment_prepay` float DEFAULT '100' COMMENT 'Процент предоплаты',
`deliveryFrom` varchar(255) DEFAULT NULL COMMENT 'Адрес погрузки',
`deliveryTo` varchar(255) DEFAULT NULL COMMENT 'Адрес разгрузки',
`requisites` text COMMENT 'Реквизиты',
`director_nominative` text COMMENT 'Именительный падеж Руководитель',
`director_genitive` text COMMENT ' Руководитель Родительный падеж',
`director_reason` text COMMENT 'Действует на основании',
PRIMARY KEY (`invoiceID`),
KEY `orderID` (`orderID`),
KEY `buyerID` (`buyerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `nano_invoices`
AUTO_INCREMENT=5000;

-- 2024-03-27 11:57:12

 */

if (isset($_GET["app"]) && $_GET["app"] == "app_invoicesimple") {

    $TableCompanies   = COMPANIES_TABLE;
    $TableOrders      = ORDERS_TABLE;
    $TableInvoices    = NANO_INVOICES_TABLE;
    $TableOLDInvoices = INVOICES_TABLE;

    $data = json_decode(file_get_contents("php://input"), true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE);

    $result = false;

    if (isset($_GET["operation"])) {

        $operation = $_GET["operation"];

        $orderID   = $data["Data"]["orderID"];
        $order     = getNanoOrder($orderID);
        $invoiceID = ($data["Data"]["invoiceID"])
        ? (int) $data["Data"]["invoiceID"]
        : chooseLastInvoices($orderID)[0]["invoiceID"];

        switch ($operation) {

            case "CreateInvoice":

                $orderID       = (int) $data["Data"]["orderID"];
                $currencyValue = $data["Data"]["currencyValue"];
                $companyID     = (int) $data["Data"]["buyerID"];

                $newID = newNanoInvoice(
                    $orderID,
                    $currencyValue,
                    $companyID
                );

                $operation_result = getNanoInvoice($newID);

                break;
            case "LoadInvoice":
                $operation_result = getNanoInvoice($invoiceID);
                break;

            case "SetInvoiceDefault":

                $DeliveryType = 0; // тип доставки - код: 0 -самовывоз, 1-доставка
                $PaymentType  = 0; // Оплата: 0-полная предоплата, 1-оплата по факту поставки, 2 - кредит
                $_time        = get_only_time();
                $invoice_time = get_current_time();
                // $order_time    = get_current_time();
                // $contract_time = get_current_time();

                $actuality_termin = 2;
                $delivery_termin  = 10;
                $payment_termin   = 3;
                $payment_prepay   = 100;

                $dataPDO = [
                    "orderID"             => $data["Data"]["orderID"],
                    "invoiceID"           => $data["Data"]["invoiceID"],
                    "buyerID"             => $data["Data"]["buyerID"],

                    "requisites"          => "",
                    "director_nominative" => "",
                    "director_genitive"   => "",
                    "director_reason"     => "",

                    "purposeID"           => 0,
                    "fundingID"           => 0,

                    "deliveryFrom"        => "",
                    "deliveryTo"          => "",

                    "DeliveryType"        => $DeliveryType,
                    "PaymentType"         => $PaymentType,

                    "invoice_time"        => $invoice_time,

                    "actuality_termin"    => $actuality_termin,
                    "delivery_termin"     => $delivery_termin,
                    "payment_termin"      => $payment_termin,

                    "payment_prepay"      => $payment_prepay,

                ];

                $bindings     = array();
                $dataPDO_Keys = array_keys($dataPDO);
                $i_keys       = array("read_only", "buyerID", "companyID", "orderID", "invoiceID", "purposeID", "fundingID", "DeliveryType", "PaymentType");

                for ($ii = 0; $ii < count($dataPDO_Keys); $ii++) {
                    $bindings[$ii]["key"]  = $dataPDO_Keys[$ii];
                    $bindings[$ii]["val"]  = $dataPDO["$dataPDO_Keys[$ii]"];
                    $bindings[$ii]["type"] = PDO::PARAM_STR;
                    if (in_array($dataPDO_Keys[$ii], $i_keys)) {
                        $bindings[$ii]["type"] = PDO::PARAM_INT;
                    }
                }

                $sql_query = "
                UPDATE `{$TableInvoices}` SET

                `buyerID`          = :buyerID,
                `requisites`          = :requisites,
                `director_nominative` = :director_nominative,
                `director_genitive`   = :director_genitive,
                `director_reason`     = :director_reason,

                `purposeID`           = :purposeID,
                `fundingID`           = :fundingID,

                `deliveryFrom`        = :deliveryFrom,
                `deliveryTo`          = :deliveryTo,

                `DeliveryType`        = :DeliveryType,
                `PaymentType`         = :PaymentType,

                `invoice_time`        = :invoice_time,

                `actuality_termin`    = :actuality_termin,
                `delivery_termin`     = :delivery_termin,
                `payment_termin`      = :payment_termin,

                `payment_prepay`      = :payment_prepay

                WHERE
                `orderID`=:orderID
                AND
                `invoiceID`=:invoiceID;
                ";

                $result = adminSSP::pdo_query_assoc($PDO_connect, $bindings, $sql_query, 1); //

                if ($result) {

                    $operation_result = getNanoInvoice($invoiceID);
                }

                break;

            case "SaveInvoiceAll":

                $DeliveryType = 0; // тип доставки - код: 0 -самовывоз, 1-доставка
                if ($data["Data"]["hosterTransport"] == 1) {
                    $DeliveryType = 1;
                }

                $PaymentType = 0; // Оплата: 0-полная предоплата, 1-оплата по факту поставки, 2 - кредит
                if ($data["Data"]["getThenPay"] == 1) {
                    $PaymentType = 1;
                }

                $_time         = get_only_time();
                $invoice_time  = $data["Data"]["invoiceDate"] . " " . $_time;
                $order_time    = $data["Data"]["orderDate"] . " " . $_time;
                $contract_time = $data["Data"]["contractDate"] . " " . $_time;

                $actuality_termin = $data["Data"]["actuality_termin"];
                $delivery_termin  = $data["Data"]["delivery_termin"];
                $payment_termin   = $data["Data"]["payment_termin"];

                $payment_prepay = $data["Data"]["payment_prepay"];

                $dataPDO = [
                    "orderID"             => $data["Data"]["orderID"],
                    "invoiceID"           => $data["Data"]["invoiceID"],

                    "requisites"          => $data["Data"]["requisites"],
                    "director_nominative" => $data["Data"]["director_nominative"],
                    "director_genitive"   => $data["Data"]["director_genitive"],
                    "director_reason"     => $data["Data"]["director_reason"],

                    "purposeID"           => $data["Data"]["purposeID"],
                    "fundingID"           => $data["Data"]["fundingID"],

                    "deliveryFrom"        => $data["Data"]["deliveryFrom"],
                    "deliveryTo"          => $data["Data"]["deliveryTo"],

                    "DeliveryType"        => $DeliveryType,
                    "PaymentType"         => $PaymentType,

                    "invoice_time"        => $invoice_time,

                    "actuality_termin"    => $actuality_termin,
                    "delivery_termin"     => $delivery_termin,
                    "payment_termin"      => $payment_termin,

                    "payment_prepay"      => $payment_prepay,

                ];

                $bindings     = array();
                $dataPDO_Keys = array_keys($dataPDO);
                $i_keys       = array("read_only", "buyerID", "companyID", "orderID", "invoiceID", "purposeID", "fundingID", "DeliveryType", "PaymentType");

                for ($ii = 0; $ii < count($dataPDO_Keys); $ii++) {
                    $bindings[$ii]["key"]  = $dataPDO_Keys[$ii];
                    $bindings[$ii]["val"]  = $dataPDO["$dataPDO_Keys[$ii]"];
                    $bindings[$ii]["type"] = PDO::PARAM_STR;
                    if (in_array($dataPDO_Keys[$ii], $i_keys)) {
                        $bindings[$ii]["type"] = PDO::PARAM_INT;
                    }
                }

                $sql_query = "
                UPDATE `{$TableInvoices}` SET

                `requisites`          = :requisites,
                `director_nominative` = :director_nominative,
                `director_genitive`   = :director_genitive,
                `director_reason`     = :director_reason,

                `purposeID`           = :purposeID,
                `fundingID`           = :fundingID,

                `deliveryFrom`        = :deliveryFrom,
                `deliveryTo`          = :deliveryTo,

                `DeliveryType`        = :DeliveryType,
                `PaymentType`         = :PaymentType,

                `invoice_time`        = :invoice_time,

                `actuality_termin`    = :actuality_termin,
                `delivery_termin`     = :delivery_termin,
                `payment_termin`      = :payment_termin,

                `payment_prepay`      = :payment_prepay

                WHERE
                `orderID`=:orderID
                AND
                `invoiceID`=:invoiceID;
            ";
                break;

            case "RestoreCompanyFromOrder":

                $sql_query = "SELECT * FROM {$TableOrders} WHERE `orderID`=$orderID;";
                $data      = adminSSP::pdo_query_assoc($PDO_connect, $bindings, $sql_query, 0)[0];

                $buyerID = (int) $data["companyID"];

                $sql_query = "
                UPDATE `{$TableInvoices}` SET
                `buyerID`={$buyerID}
                WHERE
                `orderID`={$orderID}
                AND
                `invoiceID`={$invoiceID};
                ";
                $res       = array();
                $res       = adminSSP::pdo_query_assoc($PDO_connect, $bindings, $sql_query, 1)[0];
                $result    = $res;
                $DO_RELOAD = true;

                $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID={$orderID}&company_selected={$buyerID}";

                break;

            case "UpdateRequisites":
                $dataPDO = [
                    "requisites"          => $data["Data"]["requisites"],
                    "director_nominative" => $data["Data"]["director_nominative"],
                    "director_genitive"   => $data["Data"]["director_genitive"],
                    "director_reason"     => $data["Data"]["director_reason"],
                    "orderID"             => $orderID,
                    "invoiceID"           => $invoiceID,
                    "buyerID"             => $data["Data"]["requisites"],
                ];

                $bindings     = array();
                $dataPDO_Keys = array_keys($dataPDO);
                $i_keys       = array("read_only", "buyerID", "companyID", "orderID", "invoiceID");

                for ($ii = 0; $ii < count($dataPDO_Keys); $ii++) {
                    $bindings[$ii]["key"]  = $dataPDO_Keys[$ii];
                    $bindings[$ii]["val"]  = $dataPDO["$dataPDO_Keys[$ii]"];
                    $bindings[$ii]["type"] = PDO::PARAM_STR;
                    if (in_array($dataPDO_Keys[$ii], $i_keys)) {
                        $bindings[$ii]["type"] = PDO::PARAM_INT;
                    }
                }

                $sql_query = "
                UPDATE `{$TableInvoices}` SET
                `requisites`=:requisites,
                `director_nominative`=:director_nominative,
                `director_genitive`=:director_genitive,
                `director_reason`=:director_reason,
                `buyerID`=:buyerID
                WHERE
                `orderID`=:orderID
                AND
                `invoiceID`=:invoiceID;
                ";

                // directlog($sql_query);
                break;

            case "SetInvoiceDate":
                $newDate = $data["Data"]["date"];

                $sql_query = "UPDATE `{$TableInvoices}` SET `invoice_time` = '" . $newDate . "' WHERE `orderID` = " . (int) $orderID . " AND `invoiceID`=" . (int) $invoiceID . ";";
                break;

            case "SetOrderDate":
                $newDate   = $data["Data"]["date"];
                $sql_query = "UPDATE `{$TableOrders}` SET `order_time` = '" . $newDate . "' WHERE `orderID` = " . (int) $orderID . ";";
                // $sql_query .= "UPDATE `{$TableInvoices}` SET `order_time` = '" . $newDate . "' WHERE `orderID` = " . (int)$orderID . " AND `invoiceID`=" . (int)$invoiceID . ";";
                break;
                // case "SetConractDate":
                //     $newDate   = $data["Data"]["date"];
                //     $sql_query = "UPDATE `{$TableInvoices}` SET `contract_time` = '" . $newDate . "' WHERE `orderID` = " . (int)$orderID . ";";
                //     break;

        } // switch ( $operation )

        $returnDataOperations = [
            "CreateInvoice",
            "LoadInvoice",
            "SetInvoiceDefault",
            "RestoreCompanyFromOrder",
        ];
        $redirectOperations = [
        ];

        $isReturnDataOperation = in_array($operation, $returnDataOperations);
        $isRedirectOperation   = in_array($operation, $redirectOperations);

        if (!$isReturnDataOperation) {

            $result = adminSSP::pdo_query_assoc($PDO_connect, $bindings, $sql_query, 1); //

        }

        if ($isRedirectOperation) {

            echo $urlToReload;

        } else {

            if ($isReturnDataOperation) {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode($operation_result, JSON_INVALID_UTF8_IGNORE);
                die();
            } else {

                if ($result) {
                    echo "SUCCESS";
                } else {
                    directlog("FAILED" . " {$operation} ::  " . $sql_query);
                    echo "FAILED";
                }
            }

        }

    } // isset( $_GET["operation"] )

}
