<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

// shopping cart brief info

//calculate shopping cart value
$k   = 0;
$cnt = 0;

$resCart = cartGetCartContent();
$k       = $resCart["total_price"];

// если хотим видеть в корзине сумму сразу со скидкой
// $resDiscount = dscCalculateDiscount($resCart["total_price"], isset ( $_SESSION["log"] ) ? $_SESSION["log"] : "");
// $k = $resCart["total_price"] - $resDiscount["discount_standart_unit"];

foreach ($resCart["cart_content"] as $rc) {
    $cnt += $rc["quantity"];
}

$smarty->assign("shopping_cart_value", $k);
$smarty->assign("shopping_cart_value_shown", show_price($k));
$smarty->assign("shopping_cart_items", $cnt);

?>