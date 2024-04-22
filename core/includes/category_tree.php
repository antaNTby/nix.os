<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

// category navigation form

if ( isset( $categoryID ) && $categoryID>1) {
    $out = catGetCategoryCompactCList( $categoryID );
} else {
    $out = catGetCategoryCompactCList( 1 );
}

$smarty->assign( "categories_tree_count", count( $out ) );
$smarty->assign( "categories_tree", $out );

?>