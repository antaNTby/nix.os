$( document ).ready( function() {
    if ( $( 'input#tpl_view_type' ).val() == 0 ) {
        const tpl_categoryID = 1
        const base = 'search'
        const order_array = [
            [ 10, 'asc' ],
            [ 2, 'asc' ]
        ]
        const $searchDT = $productsDataTable( base, order_array )
    } else {
        BindCardsReactions( false, '/index.php?simple_search=yes' )
    }
} )