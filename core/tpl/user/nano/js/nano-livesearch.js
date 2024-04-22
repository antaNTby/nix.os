$( function() {
    let BlockOptions = {
        overlayCSS: {
            // backgroundColor: '#46b7f9',
            // opacity: 0.25,
            backgroundColor: '#000',
            opacity: 0.5,
            // "@modal-backdrop-bg": "#000",
            // "@modal-backdrop-opacity": ".5",
            cursor: "wait"
        },
        fadeOut: 300,
        message: null
    }
    let cID = 4
    let select_limit = 16
    let default_autocomplete_url = '/nano_livesearch.php?autocomplete=1' + '&livesearch=products&limit=' + select_limit + '&cID=' + cID
    let this_url = window.location

    function liFormat( item, term ) {
        let itemType = item.type
        let product_code = item.product_code
        let li_text = item.li_text
        let li_text2 = item.li_text2
        let category_name = item.category_name
        let result = ''
        switch ( itemType ) {
            case 0:
                result = ''
                result += '<a class="link-dark fw-lighter text-decoration-none" href ="' + item.url_to_product + '">'
                if ( product_code ) {
                    result += '<span class="text-nowrap text-danger fw-normal">[' + highlight( product_code, term ) + ']</span>' + ' '
                }
                result += ' ' + highlight( li_text, term ) + ' '
                result += '</a>'
                if ( li_text2 ) {
                    result += ' ' + li_text2 + ' '
                }
                if ( category_name ) {
                    result += '<a class="link-primary fw-normal fs-8 text-decoration-none float-end border border-primary px-1 rounded-2" href ="' + item.url_to_category + '">' + category_name + '</a>'
                }
                break
            case 1:
                result = ''
                if ( +item.count > 0 ) {
                    result += result + 'Всего найдено: ' + item.count + '. ' + ' Показано: ' + Math.min( item.limit, item.count ) + '. '
                    if ( ( +item.count > +item.limit ) ) {
                        result += '<a class="link-primary fw-normal text-decoration-none float-end" href ="' + item.url_to_redirect + '">' + 'Показать результат поиска'
                    } else {
                        result += '<a class="link-primary fw-normal text-decoration-none float-end" href ="' + item.url_to_redirect + '">' + 'Показать результат поиска'
                    }
                    result += '</a>'
                } else if ( item.count == 0 ) {
                    result = '<span class="fw-bold mx-1 text-center">'
                    result += item.li_text
                    result += '</span>'
                }
                break
            default:
                result = "---"
                break
        }
        return result
    }

    function highlight( string, term ) {
        let highlight_css = '<mark style="padding:0.01em">$1</mark>';
        if ( string ) {
            let aterm = term.trim().split( ' ' );
            for ( let key in aterm ) string = string.trim().replace( new RegExp( "(?![^&;]+;)(?!<[^<>]*)(" + aterm[ key ].replace( /([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1" ) + ")(?![^<>]*>)(?![^&;]+;)", "gi" ), highlight_css );
        }
        return string;
    }
    $( '#searchstring' ).on( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB && $( this ).autocomplete( "instance" ).menu.active ) {
            event.preventDefault(); //исправляем нажатие ВВОД
        }
    } ).autocomplete( {
        source: default_autocomplete_url,
        delay: 1200,
        minLength: 3,
        position: {
            "my": "left top",
            "at": "left bottom+4",
            "collision": "none",
        },
        appendTo: "#liveSearchResult",
        search: function( event, ui ) {
            $( 'html' ).block( BlockOptions )
        },
        focus: function( event, ui ) {
            return false
        },
        select: function( event, ui ) {
            let itemType = ui.item.type
            if ( itemType === 0 ) {
                $( '#searchstring' ).removeAttr( 'disabled' )
                let text_selected = ( ui.item.li_text ).toString()
                $( '#searchstring' ).val( htmlspecialchars_decode( text_selected ) )
            } else if ( itemType === 1 ) {
                $( '#searchstring' ).val( htmlspecialchars_decode( $( '#searchstring' ).val() ) )
            }
            return false
        },
        close: function( event, ui ) {
            $( 'html' ).unblock()
        },
        classes: {
            "ui-autocomplete": "list-group list-group-flush fs-9"
        }
    } )
    $( '#searchstring' ).data( 'ui-autocomplete' )._renderMenu = function( ul, items ) {
        var that = this
        $.each( items, function( index, item ) {
            that._renderItem( ul, item )
        } )
        $( ul ).
        find( 'li:even' ).
        addClass( 'list-group-item-light' )
    }
    $( '#searchstring' ).data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
        li_content = liFormat( item, $( '#searchstring' ).val() )
        result = $( '<li>' ).
        addClass( 'list-group-item item-mini' ).
        data( 'ui-autocomplete-item', item ).
        append( li_content ).
        appendTo( ul )
        return result
    }
    $( '#searchstring' ).data( 'ui-autocomplete' )._resizeMenu = function() {
        let uibodyWidth = $( 'body' )[ 0 ].clientWidth
        let myFormWidth = $( '#formpoisk' )[ 0 ].clientWidth
        newWidth = myFormWidth
        if ( newWidth < 576 ) {
            newWidth = 576
        }
        this.menu.element.outerWidth( newWidth );
        let $ul = $( this.menu.element[ 0 ] )
        $ul.find( '*.ui-menu-item-wrapper' ).removeClass( 'ui-menu-item-wrapper' );
    }
} )