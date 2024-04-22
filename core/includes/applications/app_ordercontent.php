<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-09-11       #
#          http://nixby.pro              #
##########################################

/*
UPDATE  `utf_ordered_carts`set `itemPriority`=0 where `itemPriority` is null;

ALTER TABLE `UTF_ordered_carts`
CHANGE `itemPriority` `itemPriority` int(11) NOT NULL DEFAULT '0' AFTER `order_aka`;
 */

if (isset($_GET["app"]) && $_GET["app"] == "app_ordercontent") {
    $data        = json_decode(file_get_contents("php://input"), true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE);
    $result      = false;
    $statusID    = (int)$data["Data"]["statusID"];
    $status_name = ostGetOrderStatusName($statusID);

    $allowed_statuses = array(2, 3);

    if (isset($_SESSION["log"])) {
        $admintempname = $_SESSION["log"];
    } else {
        $admintempname = "demo";
    }

    $logName = " <em class='text-muted'>" . SITE_URL . " -> " . $admintempname . "</em>";

    if (isset($_GET["operation"])) {
        $operation = $_GET["operation"];

        if (in_array((int)$statusID, $allowed_statuses) || ($operation == 'AddComment')) {

            $DO_RELOAD = false;

            switch ($operation) {

                case "AddComment":

                    $orderID = (int)$data["Data"]["orderID"];
                    $comment = "<strong>" . $data["Data"]["comment"] . "</strong> &nbsp;&nbsp;&nbsp;" . $logName;
                    if ($comment != "" && $status_name != "") {

                        $sqlOrderStatusChangeLogTable = "INSERT INTO " . ORDER_STATUS_CHANGE_LOG_TABLE .
                        " ( orderID, status_name, status_change_time, status_comment ) " .
                        " VALUES( " . (int)$orderID . ", '" . xToText($status_name) . "', '" .
                        xEscSQL(get_current_time()) . "', '" . xEscSQL(trim($comment)) . "' ) ";

                        $q1 = db_multiquery($sqlOrderStatusChangeLogTable);

                        $result = $q1;
                    }
                    break;

                case "AddFiveItems":

                    $orderID            = (int)$data["Data"]["orderID"];
                    $productComplexName = "Товар";
                    $price              = 1;
                    $quantity           = 1;
                    $tax                = 0;

                    $newItem = array(
                        $orderID,
                        $productComplexName,
                        $price,
                        $quantity,
                        $tax,
                        'шт.',
                        100,
                        1,
                    );
                    $targetTable = ORDERED_CARTS_TABLE;
                    $VALUES      = "";

                    for ($i = 1; $i <= 5; $i++) {
                        // code...

                        $VALUES .= <<<SQL
                        (
                        @maxI+{$i},
                        {$orderID},
                        '{$productComplexName} №{$i}',
                        {$price},
                        {$quantity},
                        {$tax},
                        'шт.',
                        100,
                        1
                        ),
                        SQL;
                    }


                    $HEREDOC_SQL = <<<SQL
                        Lock tables `{$targetTable}` write;
                        SET @maxI = (SELECT MAX(`itemID`) FROM `{$targetTable}`);
                        INSERT INTO `{$targetTable}`
                        (`itemID`, `orderID`, `name`, `Price`, `Quantity`, `tax`, `itemUnit`, `itemPriority`, `enabled`)
                        VALUES
                        {$VALUES}%last%
                        unlock tables;
                    SQL;

                    $SQL=str_replace(",%last%",";",$HEREDOC_SQL);
                    $q1 = db_multiquery( $SQL );
                    $result = $q1;
                    $result = updateOrderAmount( $orderID );
                    $DO_RELOAD   = true;
                    $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID=" . (int)$orderID;

                    break;

                case "AddNewItem":

                    $orderID = (int)$data["Data"]["orderID"];
                    $tempName = trim($data["Data"]["name"]);
                    $productComplexName = xEscSQL( $tempName );
                    $price    = $data["Data"]["Price"] ?? 1;
                    $quantity = $data["Data"]["quantity"] ?? 1;
                    $tax      = $data["Data"]["tax"] ?? 0;

                    $targetTable = ORDERED_CARTS_TABLE;

                    $HEREDOC_SQL = <<<SQL
                        Lock tables `{$targetTable}` write;
                        SET @maxI = (SELECT MAX(`itemID`) FROM `{$targetTable}`);
                        SET @newPriority= (SELECT IFNULL(0,MIN(`itemPriority`)) FROM `{$targetTable}` WHERE `orderID` = $orderID);

                        INSERT INTO `{$targetTable}`
                        (`itemID`, `orderID`, `name`, `Price`, `Quantity`, `tax`, `itemUnit`, `itemPriority`, `enabled`)
                        VALUES
                        (
                        @maxI+1,
                        {$orderID},
                        '{$productComplexName}',
                        {$price},
                        {$quantity},
                        {$tax},
                        'шт.',
                        @newPriority-2,
                        1
                        );
                        unlock tables;

                    SQL;

                    $comment = "Добавлен <strong>{$productComplexName}</strong> в заказ <strong>#{$orderID}</strong>". $logName;

                    $sqlOrderStatusChangeLogTable = "INSERT INTO " . ORDER_STATUS_CHANGE_LOG_TABLE .
                    " ( orderID, status_name, status_change_time, status_comment ) " .
                    " VALUES( " . (int)$orderID . ", '" . xToText( $status_name ) . "', '" .
                    xEscSQL( get_current_time() ) . "', '" . xEscSQL( trim( $comment ) ) . "' ) ;";

                    $sqlOrderStatusChangeLogTable ="";

                    $SQL=$HEREDOC_SQL . $sqlOrderStatusChangeLogTable;

                    $q1 = db_multiquery(  $SQL );
                    $result = $q1;
                    $result = updateOrderAmount( $orderID );
                    $DO_RELOAD   = true;
                    $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID=" . (int)$orderID;

                    break;

                case "ChangeItemProperties":

                    $orderID = (int)$data["Data"]["orderID"];
                    $itemID  = (int)$data["Data"]["itemID"];

                    $sql                  = "";
                    $sqlOrderedCartsTable = "";

                    if ( $data["Data"]["itemName"] != $data["Data"]["oldName"] ) {

                        $sqlOrderedCartsTable .= "UPDATE " . ORDERED_CARTS_TABLE . " " .
                        "SET " .
                        "name='" . xEscSQL( $data["Data"]["itemName"] ) . "' " .
                            "WHERE orderID={$orderID} AND itemID={$itemID}; ";
                    }

                    $sqlOrderedCartsTable .= "UPDATE " . ORDERED_CARTS_TABLE . " " .
                    "SET " .
                    "itemUnit='" . xToText( $data["Data"]["itemUnit"] ) . "', " .
                    "itemPriority=" . (int)$data["Data"]["itemPriority"] . " " .
                        "WHERE orderID={$orderID} AND itemID={$itemID}; ";

                    $targetOrderID = (int)$data["Data"]["targetOrderID"];
                    $isOrderExist = checkOrderExist( $orderID );

                    if ( $isOrderExist && ( $targetOrderID != $orderID ) ) {

                        $sqlOrderedCartsTable .= "UPDATE " . ORDERED_CARTS_TABLE . " " .
                        "SET " .
                        "orderID=" . (int)$targetOrderID . " " .
                            "WHERE orderID={$orderID} AND itemID={$itemID}; ";

                        $comment = "перемещнен itemID:{$itemID} :: <strong>" . $data["Data"]["itemName"] . "</strong> в заказ <strong>#{$targetOrderID}</strong>". $logName;
                        $sqlOrderedCartsTable .= "INSERT INTO " . ORDER_STATUS_CHANGE_LOG_TABLE .
                        " ( orderID, status_name, status_change_time, status_comment ) " .
                        " VALUES( " . (int)$orderID . ", '" . xToText( $status_name ) . "', '" .
                        xEscSQL( get_current_time() ) . "', '" . xEscSQL( trim( $comment ) ) . "' ) ;";
                    }

                    $sql .= $sqlOrderedCartsTable;
                    $q1     = db_multiquery( $sql );
                    $result = $q1;

                    if ( $result && $isOrderExist && ( $targetOrderID != $orderID ) ) {

                        $result      = updateOrderAmount( $orderID );
                        $result      = updateOrderAmount( $targetOrderID );
                        $DO_RELOAD   = true;
                        $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID=" . (int)$targetOrderID;
                    }

                    break;

                case "KillThemAll":

                    $orderID              = (int)$data["Data"]["orderID"];
                    $sql                  = "";
                    $sqlOrderedCartsTable = "DELETE FROM " . ORDERED_CARTS_TABLE . " WHERE orderID={$orderID}; ";
                    $sql .= $sqlOrderedCartsTable;

                    $comment                      = "Удалены все товары из заказа <strong>#{$orderID}</strong>";
                    $sqlOrderStatusChangeLogTable = "INSERT INTO " . ORDER_STATUS_CHANGE_LOG_TABLE .
                    " ( orderID, status_name, status_change_time, status_comment ) " .
                    " VALUES( " . (int)$orderID . ", '" . xToText( $status_name ) . "', '" .
                    xEscSQL( get_current_time() ) . "', '" . xEscSQL( trim( $comment ) ) . "' ) ;";
                    $sql .= $sqlOrderStatusChangeLogTable;

                    $q1     = db_multiquery( $sql );
                    $result = $q1;

                    if ( $result ) {
                        // после удаления пересчитываем заказ и пергружаем страницу
                        $result      = updateOrderAmount( $orderID );
                        $DO_RELOAD   = true;
                        $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID=" . (int)$_GET["orderID"];
                    }

                    break;

                case "DeleteCartItem":

                    $orderID  = (int)$data["Data"]["orderID"];
                    $itemID   = (int)$data["Data"]["itemID"];
                    $name     = $data["Data"]["name"];
                    $Quantity = $data["Data"]["Quantity"];
                    $Price    = $data["Data"]["Price"];

                    $sql                  = "";
                    $sqlOrderedCartsTable = "DELETE FROM " . ORDERED_CARTS_TABLE . " WHERE itemID={$itemID}; ";

                    $sql .= $sqlOrderedCartsTable;

                    $comment                      = "Удален itemID:{$itemID} :: <strong>{$name}</strong> x <strong>{$Quantity}</strong> по цене <strong>{$Price}</strong>". $logName;
                    $sqlOrderStatusChangeLogTable = "INSERT INTO " . ORDER_STATUS_CHANGE_LOG_TABLE .
                    " ( orderID, status_name, status_change_time, status_comment ) " .
                    " VALUES( " . (int)$orderID . ", '" . xToText( $status_name ) . "', '" .
                    xEscSQL( get_current_time() ) . "', '" . xEscSQL( trim( $comment ) ) . "' ) ;";

                    $sql .= $sqlOrderStatusChangeLogTable;

                    $q1     = db_multiquery( $sql );
                    $result = $q1;

                    if ( $result ) {
                        // после удаления пересчитываем заказ и пергружаем страницу
                        $result      = updateOrderAmount( $orderID );
                        $DO_RELOAD   = true;
                        $urlToReload = ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID=" . (int)$_GET["orderID"];
                    }

                    break;

                case "SavePrices":

                    $orderID = (int)$data["Data"]["orderID"];
                    $sql = "";
                    for ( $index = 0; $index < $data["Data"]["rowsCount"]; $index++ ) {
                        $itemID      = $data["Data"]["rows"][$index]["itemID"];
                        $newQuantity = $data["Data"]["rows"][$index]["Quantity"];
                        $newPrice    = $data["Data"]["rows"][$index]["PurePrice"];

                        $sqlOrderedCartsTable = "UPDATE " . ORDERED_CARTS_TABLE . " " .
                            "SET Quantity=" . $newQuantity . ", " .
                            "Price=" . $newPrice . " " .
                            "WHERE orderID={$orderID} AND itemID={$itemID}; ";
                        $sql .= $sqlOrderedCartsTable;
                    }

                    $newCurrencyCode    = $data["Data"]["ISO3"];
                    $newCurrencyValue   = $data["Data"]["currencyValue"];
                    $newOrderDiscount   = -1 * $data["Data"]["discountPercent"];
                    $newShippingCost    = $data["Data"]["discountVal"];
                    $totalCost_WITH_VAT = floatval( $data["Data"]["totalCost_WITH_VAT"] );
                    $newOrderAmount     = $totalCost_WITH_VAT / floatval( $newCurrencyValue );

                    $sqlOrderTable = "UPDATE " . ORDERS_TABLE . " " .
                        "SET currency_code='" . $newCurrencyCode . "', " .
                        "currency_value=" . $newCurrencyValue . ", " .
                        "order_amount=" . $newOrderAmount . ", " .
                        "order_discount=" . $newOrderDiscount . ", " .
                        "shipping_cost=" . $newShippingCost . " " .
                        "WHERE orderID={$orderID}; ";
                    $sql .= $sqlOrderTable;
                    $q = db_multiquery( $sql );

                    $result = $q;

                    break;

                case "SaveSimplePrices":
                    // сохраняем контент с учтёнными скидками, обнуляем скидки таблице заказов, сохраняем уточненный курс валюты
                    $orderID = (int)$data["Data"]["orderID"];
                    $sql = "";
                    for ( $index = 0; $index < $data["Data"]["rowsCount"]; $index++ ) {
                        $itemID      = $data["Data"]["rows"][$index]["itemID"];
                        $newQuantity = $data["Data"]["rows"][$index]["Quantity"];
                        $newPrice    = $data["Data"]["rows"][$index]["Price"]; // здесь будут конечгые цены с учтенными скидками

                        $sqlOrderedCartsTable = "UPDATE " . ORDERED_CARTS_TABLE . " " .
                            "SET Quantity=" . $newQuantity . ", " .
                            "Price=" . $newPrice . " " .
                            "WHERE orderID={$orderID} AND itemID={$itemID}; ";
                        $sql .= $sqlOrderedCartsTable;
                    }

                    $newCurrencyCode  = $data["Data"]["ISO3"];
                    $newCurrencyValue = $data["Data"]["currencyValue"];
                    $newOrderDiscount = 0;
                    $newShippingCost  = 0;

                    $totalCost_WITH_VAT = floatval( $data["Data"]["totalCost_WITH_VAT"] );
                    $newOrderAmount     = $totalCost_WITH_VAT / floatval( $newCurrencyValue );

                    $sqlOrderTable = "UPDATE " . ORDERS_TABLE . " " .
                        "SET currency_code='" . $newCurrencyCode . "', " .
                        "currency_value=" . $newCurrencyValue . ", " .
                        "order_amount=" . $newOrderAmount . ", " .
                        "order_discount=" . $newOrderDiscount . ", " .
                        "shipping_cost=" . $newShippingCost . " " .
                        "WHERE orderID={$orderID}; ";
                    $sql .= $sqlOrderTable;
                    $q = db_multiquery( $sql );

                    $result = $q;

                    break;

                case "NewOrder":

                    break;
            } //switch

        } //  in_array( (int)$statusID, $allowed_statuses ) || ( $operation == 'AddComment' )

        // На основе успешной или не успешной отправки сообщаем SUCCESS или FAILED
        // !!! Больше никакого вывода из данного файла быть не должно
        // Никаких распечаток через echo, print_r и т.п. !!!

        // if ( !$DO_RELOAD ) {
        //     if ( $result ) {
        //         echo "SUCCESS";
        //     } else {
        //         echo "FAILED";
        //     }
        // } else {
        //     echo $urlToReload;
        // }


        if ( $DO_RELOAD ) {

            echo $urlToReload;

        } else {

            if ( $operation == "CorsCompany" ) {
                header( "Content-Type: application/json; charset=utf-8" );
                // jlog($operation_result);
                echo json_encode( $operation_result, JSON_INVALID_UTF8_IGNORE );
            } else {

                if ( $result ) {
                    echo "SUCCESS";
                } else {
                    echo "FAILED";
                }
            }

        }

    }

}

?>
