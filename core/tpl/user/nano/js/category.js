$( document ).ready( async function() {
    const tpl_categoryID = $( '#tpl_categoryID' ).val()
    if ( $( 'input#tpl_view_type' ).val() == 0 ) {
        const base = 'category'
        const order_array = [
            [ 10, 'asc' ],
            [ 2, 'asc' ]
        ]
        const $categoryDT = $productsDataTable( base, order_array )
    } else {
        BindCardsReactions( false, window.location )
    }
} )