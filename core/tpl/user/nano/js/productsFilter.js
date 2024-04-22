var SetFlagPriceFilterChanged = 0
const is_CardsPage = document.querySelector( 'div#cardsPage' ) !== null
const is_DataTable = document.querySelector( 'table.mydatatable' ) !== null
const is_SearchPage = ( document.querySelector( 'input#tpl_SearchPage' ) !== null && document.querySelector( 'input#tpl_SearchPage' ).value == 1 )
var tpl_categoryID = document.getElementById( 'tpl_categoryID' ).value
var searchstring = document.getElementById( 'searchstring' ).value
const btnConfirm = document.getElementById( 'pageFilterConfirm' )
var js_catsJSON
var js_cats
var js_lenJSON
var js_len

function ApplyFilter( delay = 1200 ) {
    if ( globalTimeout != null ) clearTimeout( globalTimeout )
    if ( is_DataTable ) {
        globalTimeout = setTimeout( DataTableDraw, delay )
    }
    if ( is_CardsPage ) {
        globalTimeout = setTimeout( CardsPageDraw, delay )
    }
}
async function changeViewType( newView_type ) {
    let pathname = window.location.pathname.replace( /_offset_[0-9]+/g, '' )
    let curLoc = window.location.protocol + "//" + window.location.host + pathname
    let myView = document.querySelector( 'input#tpl_view_type' ).value
    let myOffset
    let myLength
    if ( is_CardsPage ) {
        let start = ( document.querySelector( 'li.page-item.active .page-link[data-offset]' ) !== null ) ? document.querySelector( 'li.page-item.active .page-link[data-offset]' ).getAttribute( 'data-offset' ) : 0
        myOffset = start
        myLength = ( document.querySelector( 'ul[data-navigator="card-navigator"][data-card-count]' ) !== null ) ? +document.querySelector( 'ul[data-navigator="card-navigator"][data-card-count]' ).value : 16
    }
    if ( is_DataTable ) {
        let table = $( 'table.mydatatable' ).DataTable()
        let info = table.page.info()
        myOffset = info.start
        myLength = info.length
    }
    let url = '/index.php?change_view_type=' + newView_type
    let ask = {
        'operation': 'change_view_type',
        'view_type': +newView_type,
        'searchstring': searchstring,
        'myView': myView,
        'myOffset': myOffset,
        'myLength': myLength,
        'is_CardsPage': is_CardsPage,
        'is_DataTable': is_DataTable,
        'is_SearchPage': is_SearchPage,
        'tpl_categoryID': tpl_categoryID,
    }
    let ajaxAnswer = await doMyAjax( url, ask, reqType = 'POST' )
    if ( ajaxAnswer && ajaxAnswer !== 0 ) {
        setLocationAjax( ajaxAnswer.toString() )
    }
}

function DataTableDraw() {
    // SetFlagPriceFilterChanged = 1
    globalTimeout = null
    let myTable = $( 'table.mydatatable' )
    if ( myTable && myTable.length ) {
        myTable.DataTable().draw()
    }
}

function getAjaxUrl( offset_clause = '' ) {
    let res = ''
    if ( is_DataTable ) {
        if ( is_SearchPage ) {
            res = 'index.php?simple_search=yes&searchstring=' + searchstring.toString() + offset_clause + '&operation=get_data'
        } else {
            res = '/index.php?categoryID=' + tpl_categoryID + offset_clause + '&operation=get_data'
        }
    } else {
        if ( is_SearchPage ) {
            res = 'index.php?simple_search=yes&searchstring=' + searchstring.toString() + offset_clause + '&operation=get_cards'
        } else {
            res = '/index.php?categoryID=' + tpl_categoryID + offset_clause + '&operation=get_cards'
        }
    }
    return res
}
async function CardsPageDraw( forActivePage = 1 ) {
    let ajaxUrl
    ajaxUrl = getAjaxUrl()
    if ( forActivePage && forActivePage === 1 ) {
        let activePage = document.querySelector( 'li.page-item.active .page-link[data-offset]' )
        let start = ( activePage ) ? +activePage.getAttribute( 'data-offset' ) : 0
        let offset_clause = ''
        offset_clause = ( start == 'show_all' ) ? '&show_all=yes' : ( ( start > 0 ) ? '&offset=' + start : '' )
        ajaxUrl = getAjaxUrl( offset_clause )
    }
    let reqData = {
        'operation': 'draw_Cards',
        'tpl_categoryID': tpl_categoryID,
        'categoryID': tpl_categoryID,
        'params': FilterParams()
    }
    let pathname = window.location.pathname.replace( /_offset_[0-9]+/g, '' )
    let curLoc = window.location.protocol + "//" + window.location.host + pathname
    updateURL( curLoc )
    _drawCardsAjax( ajaxUrl, reqData )
}
async function _drawCardsAjax( ajaxUrl, reqData ) {
    let cardsPage_ajax = await doMyAjax( ajaxUrl, reqData, 'POST' )
    if ( cardsPage_ajax.length ) {
        document.getElementById( 'cardsPage' ).innerHTML = cardsPage_ajax
        BindCardsReactions( false, ajaxUrl )
        console.log( document.getElementById( 'catsList' ) )
        if (
            document.getElementById( 'catsList' ) &&
            document.getElementById( 'smarty_cats' ) &&
            (document.getElementById( 'smarty_cats' ).innerHTML.length > 1) &&
            (document.getElementById( 'smarty_cats' ).innerHTML.length > 1)
            ) {
            document.getElementById( "catsList" ).innerHTML = null;
            if (document.getElementById( 'smarty_cats' ) && document.getElementById( 'smarty_cats' ).innerHTML.length > 1) {
                document.getElementById( 'catsList' ).appendChild( document.getElementById( 'smarty_cats' ) )
            }
            document.getElementById( "catsList" ).classList.remove( 'd-none' )
            bindCatsReactions()
        }
    }
}
function BindCardsReactions( table, ajaxUrl ) {
    bindAddToCartReactions()
    bindPaginationReactions()
    if ( is_CardsPage === true ) {
        _setupProductFilter( false, ajaxUrl )
    } else if ( is_DataTable === true ) {
        _setupProductFilter( table, ajaxUrl )
    }
}
const FilterParams = () => {
    let filter = {}
    if ( $( '#selectParentCategory' ).length > 0 && $( '#selectParentCategory option:selected' ) != $( '#selectParentCategory option:first' ) ) {
        filter.selectParentCategory = $( '#selectParentCategory' ).val()
        filter.categoryID = filter.selectParentCategory
    } else {
        filter.selectParentCategory = false
        filter.categoryID = $( '#tpl_categoryID' ).val().toString()
    }
    filter.searchInSubcategories = ( $( '#searchInSubcategories:checkbox:not(:checked)' ).length == 0 )
    filter.cats_include = false
    var cats_include = ''
    if ( $( '#cats_conttrols' ).length ) {
        var $all = $( '#cats_conttrols input[data-categoryID]:checkbox' )
        var $checked = $( '#cats_conttrols input[data-categoryID]:checkbox:checked' )
        var $unchecked = $( '#cats_conttrols input[data-categoryID]:checkbox:not(:checked)' )
        if ( $all.length > 0 && $checked.length > 0 ) {
            cats_include = function() {
                var res = ''
                var myCats = []
                $checked.each( function() {
                    myCats.push( $( this ).attr( 'data-categoryID' ).toString() )
                } )
                res = myCats.join( ',' )
                return res.toString()
            }
        }
    }
    filter.in_stock = $( '#filter_stock' ).val()
    filter.searchstring = $( '#searchstring' ).val().toString()
    filter.stopwords = $( '#stopwords' ).val().toString()
    filter.crosswords = $( '#crosswords' ).val().toString()
    filter.tpl_currency_value = $( '#tpl_currency_value' ).val().toString()
    filter.tpl_CID = $( '#tpl_CID' ).val().toString()
    filter.price_from = $( '#number_price_from' ).val().toString()
    filter.price_to = $( '#number_price_to' ).val().toString()
    filter.SetFlagPriceFilterChanged = SetFlagPriceFilterChanged
    filter.cats_include = cats_include
    // column search
    if ( is_CardsPage ) {
        filter.product_code = $( '#product_code' ).val().toString()
        filter.product_name = $( '#product_name' ).val().toString()
        filter.brief_description = $( '#brief_description' ).val().toString()
    }
    return filter
}

function hardResetFilter() {
    $( 'select#filter_stock' ).val( function() {
        let defaultValue = $( this ).attr( 'data-default-value' )
        return +defaultValue
    } )
    $( 'select#selectParentCategory' ).val( function() {
        let defaultValue = $( this ).attr( 'data-default-value' )
        return +defaultValue
    } )
    $( '#product_code' ).val( '' )
    $( '#product_name' ).val( '' )
    $( '#brief_description' ).val( '' )
    let DT = $( 'table.mydatatable' ).DataTable()
    DT.columns().every( function() {
        var that = this
        that.search( '' )
    } )
    $( '#stopwords' ).val( '' )
    $( '#crosswords' ).val( '' )
    clearPriceFilter()
    clearCatsFilter()
    if ( is_DataTable ) {
        if ( globalTimeout != null ) clearTimeout( globalTimeout )
        globalTimeout = setTimeout( DoDraw, 100 )
    }
    if ( is_CardsPage ) {
        if ( globalTimeout != null ) clearTimeout( globalTimeout )
        globalTimeout = setTimeout( CardsPageDraw, 100 )
    }
}

function setPriceFilterLimits( limits ) {
    $( '#number_price_from' ).attr( {
        'min': limits.Min_out,
        'max': limits.Max_out,
    } )
    $( '#number_price_to' ).attr( {
        'min': limits.Min_out,
        'max': limits.Max_out,
    } )
    $( '#price_from' ).attr( {
        'min': limits.Min_out,
        'max': limits.Max_out,
    } )
    $( '#price_to' ).attr( {
        'min': limits.Min_out,
        'max': limits.Max_out,
    } )
    $( 'span[for="number_price_from"]' ).text( limits.Min_out )
    $( 'span[for="number_price_to"]' ).text( limits.Max_out )
}

function setPriceFilterValues( limits ) {
    $( '#price_from' ).val( +limits.price_from_out ) //.attr( 'valueAsNumber', limits.price_from_out )
    $( '#price_to' ).val( +limits.price_to_out ) //.attr( 'valueAsNumber', limits.price_to_out )
    $( '#number_price_from' ).val( +limits.price_from_out ) //.attr( 'valueAsNumber', limits.price_from_out )
    $( '#number_price_to' ).val( +limits.price_to_out ) //.attr( 'valueAsNumber', limits.price_to_out )
}

function createCatsTable( cats, cats_count, parentSelector ) {
    if ( typeof parentSelector == "undefined" ) {
        parentSelector = 'div#catsList'
    }
    let HTML = ''
    let LABEL = ''
    let TABLE = ''
    $( `${parentSelector}` ).html( HTML ).removeClass( 'd-block' ).addClass( 'd-none' )
    if ( cats && cats_count && cats_count <= 50 ) {
        let obj = {}
        obj = cats
        let BODY = ''
        LABEL = '<div class="mb-2 d-flex justify-content-between align-items-end"><input class="form-check-input fs-4" type="checkbox" id="check-all-cats" value="1" checked aria-label="category">'
        LABEL += '<label for="cats_container" class="text-dark">Найдено в категориях:</label><span class="badge bg-primary">' + cats_count + '</span></div>'
        Object.keys( obj ).forEach( function( key ) {
            if ( key > 1 ) {
                let href = '/category_' + key + '.html'
                let link = '<a href="' + href + '" class="text-nowrap text-decoration-none fw-lighter text-primary" data-categoryID="' + key + '">' + obj[ key ] + '</a>'
                let CHECKBOX = '<div><input class="form-check-input me-3" type="checkbox" checked id="btn-check-' + key + '" value="1" aria-label="category" data-categoryID="' + key + '"></div>'
                BODY += '<tr><td title="Показать/Спрятать">' + CHECKBOX + '</td><td title="Перейти в эту категорию">' + link + '</td></tr>'
            }
        } )
        TABLE += '<table class="table table-sm table-hover table-borderless mb-0" id="cats_container">'
        TABLE += '<tbody id="cats_conttrols">'
        TABLE += BODY
        TABLE += '</tbody>'
        TABLE += '</table>'
        HTML += LABEL
        HTML += '<div class="table-responsive bg-white">'
        HTML += TABLE
        HTML += '</div>'
        $( `${parentSelector}` ).html( HTML ).removeClass( 'd-none' ).addClass( 'd-block' )
        bindCatsReactions()
    } else if ( cats_count > 50 ) {
        HTML = '<div class="mb-2 d-flex justify-content-between align-items-end"><label for="cats_container" class="text-dark">Найдено в категориях:</label><span class="badge bg-primary">' + cats_count + '</span></div>'
        HTML += '<div class="form-text text-secondary mt-1 fs-7">Найдено, более чем в 50-ти категориях. Уточните свой запрос для отображения списка</div>'
        $( `${parentSelector}` ).html( HTML ).removeClass( 'd-none' ).addClass( 'd-block' )
    }
}

function clearCatsFilter() {
    $( '#cats_conttrols input[data-categoryID]:checkbox' ).removeAttr( 'checked' )
    $( '#check-all-cats' ).removeAttr( 'checked' )
    $( 'div#catsList' ).html( 'Фильтр сброшен' )
}

function clearPriceFilter( id, value, min, max ) {
    if ( typeof min === "undefined" ) {
        min = 0
    }
    if ( typeof max === "undefined" ) {
        max = 100000
    }
    if ( typeof id === "undefined" ) {
        $( '#number_price_from[type="number"]' ).val( +min )
        $( '#number_price_from[type="number"]' ).attr( 'valueAsNumber', +min )
        $( '#number_price_to[type="number"]' ).val( +max )
        $( '#number_price_to[type="number"]' ).attr( 'valueAsNumber', +max )
    } else {
        if ( typeof value === "undefined" ) {
            value = ( id == 'number_price_from' ) ? 0 : 100000
        }
        if ( id == 'number_price_from' ) {
            $( '#' + id + '[type="number"]' ).val( +value )
            $( '#' + id + '[type="number"]' ).attr( 'valueAsNumber', +min )
        }
        if ( id == 'number_price_to' ) {
            $( '#' + id + '[type="number"]' ).val( +value )
            $( '#' + id + '[type="number"]' ).attr( 'valueAsNumber', +max )
        }
    }
}

function _setupProductFilter( myTable, base_url ) {
    // bindings
    // column search
    $( '[data-column][type="text"]' ).on( 'keyup change', function() {
        let name = $( this ).attr( 'data-column' ).toString() + ':name'
        if ( myTable && is_DataTable && ( myTable.column( name ).search() !== this.value ) ) {
            myTable.column( name ).search( this.value )
            ApplyFilter( 1200 )
        } else
        if ( !myTable && is_CardsPage ) {
            ApplyFilter( 1200 )
        }
    } )
    $( 'a[type="button"][data-action="reset-column-filter"]' ).on( 'click', function() {
        let forName = $( this ).parent().attr( 'for' )
        let value = $( '.column-filter[name="' + forName + '"]' ).val()
        if ( forName ) {
            clearPriceFilter()
            clearCatsFilter()
            $( '.column-filter[name="' + forName + '"]' ).val( '' )
            if ( myTable && is_DataTable ) {
                myTable.column( forName + ':name' ).search( '' )
            }
            ApplyFilter( 600 )
        }
    } )
    // SEARCHSTRING
    $( '#searchstring' ).on( 'keyup', function( event ) {
        let elementID = event.target.id
        let x = event.key
        if ( x == 'Enter' ) {
            document.getElementById( 'formpoisk' ).submit()
        } else
        if ( x == 'Escape' ) {
            return event.target.value = ''
        }
        return false
    } )
    // stopwords
    $( '#stopwords' ).on( 'keyup change', function() {
        ApplyFilter( 600 )
    } )
    // crosswords
    $( '#crosswords' ).on( 'keyup change', function() {
        ApplyFilter( 600 )
    } )
    // instock
    $( '#filter_stock' ).on( 'change', function() {
        clearPriceFilter()
        clearCatsFilter()
        ApplyFilter( 300 )
    } )
    // selectParentCategory
    $( '#selectParentCategory' ).on( 'change', function() {
        clearPriceFilter()
        clearCatsFilter()
        ApplyFilter( 300 )
    } )
    // Price
    $( 'input[type="number"][data-column="Price"]' ).on( 'change', function() {
        let name = this.getAttribute( 'name' ).replace( 'number_', '' )
        $el = $( 'input[type="range"][data-column="Price"][name="' + name + '"]' )
        $el.val( Math.round( this.valueAsNumber ) ).change()
        if ( this.valueAsNumber > 0 ) {
            SetFlagPriceFilterChanged = 1
            ApplyFilter( 1500 )
        }
    } )
    $( 'input[type="range"][data-column="Price"]' ).on( 'change', function() {
        let name = this.getAttribute( 'name' )
        $el = $( 'input[type="number"][data-column="Price"][name="number_' + name + '"]' )
        $el.val( Math.round( this.valueAsNumber ) )
        if ( this.valueAsNumber > 0 ) {
            SetFlagPriceFilterChanged = 1
            ApplyFilter( 300 )
        }
    } )
    $( 'a[type="button"][data-action="reset-range"]' ).on( 'click', function() {
        sender = this
        id = $( sender ).parent().next( 'input[type="number"]' ).attr( 'id' )
        value = $( sender ).parent().next( 'input[type="number"]' ).val()
        min = $( sender ).parent().next( 'input[type="number"]' ).attr( 'min' )
        max = $( sender ).parent().next( 'input[type="number"]' ).attr( 'max' )
        clearPriceFilter( id, value, min, max )
        ApplyFilter( 600 )
    } )
    $( '[data-action="reset-filter"]' ).on( 'click', function() {
        hardResetFilter()
        if ( typeof base_url != "undefined" ) {
            ApplyFilter( 600 )
        }
    } )
    $( 'a[type="button"][data-action="reset-select"]' ).on( 'click', function() {
        defaultValue = $( this ).parent().next( 'select' ).attr( 'data-default-value' )
        selectID = $( this ).parent().next( 'select' ).attr( 'id' )
        currentValue = $( this ).parent().next( 'select' ).val()
        if ( +defaultValue != +currentValue && selectID ) {
            $( 'select#' + selectID + '' ).val( +defaultValue )
            ApplyFilter( 600 )
        }
    } )
    $( 'a[type="button"][data-action="reset-text-filter"]' ).on( 'click', function() {
        id = $( this ).parent().next( '.text-filter' ).attr( 'id' )
        value = $( this ).parent().next( '.text-filter' ).val()
        if ( value && id ) {
            $( '#' + id + '.text-filter' ).val( '' )
            ApplyFilter( 600 )
        }
    } )
    btnConfirm.addEventListener( 'click', function( event ) {
        if ( is_CardsPage ) {
            event.preventDefault()
            CardsPageDraw()
        } else {
            DataTableDraw()
        }
    }, false )
}

function bindCatsReactions() {
    $( '#check-all-cats' ).click( function() {
        if ( $( this ).is( ':checked' ) ) {
            $( '#cats_conttrols input[data-categoryID]:checkbox' ).prop( 'checked', true )
            $( '#cats_conttrols a[data-categoryID]' ).removeClass( 'text-muted text-decoration-line-through' )
        } else {
            $( '#cats_conttrols input[data-categoryID]:checkbox' ).prop( 'checked', false )
            $( '#cats_conttrols a[data-categoryID]' ).addClass( 'text-muted text-decoration-line-through' )
        }
    } )
    $( '#cats_conttrols input[data-categoryID]:checkbox' ).on( 'change', function() {
        if ( $( '#cats_conttrols input[data-categoryID]:checkbox:checked' ).length > 0 && $( '#cats_conttrols input[data-categoryID]:checkbox:not(:checked)' ).length > 0 ) {
            document.getElementById( 'check-all-cats' ).indeterminate = true
        } else {
            document.getElementById( 'check-all-cats' ).indeterminate = false
        }
        let categoryID = $( this ).attr( 'data-categoryID' )
        if ( $( this ).is( ':checked' ) ) {
            $( '#cats_conttrols a[data-categoryID ="' + categoryID + '"]' ).removeClass( 'text-muted text-decoration-line-through' )
        } else {
            $( '#cats_conttrols a[data-categoryID ="' + categoryID + '"]' ).addClass( 'text-muted text-decoration-line-through' )
        }
    } )
}

function bindPaginationReactions() {
    Array.prototype.forEach.call( document.querySelectorAll( `a.page-link.link-ajax[data-offset][data-href]` ), function( el ) {
        let start = +el.getAttribute( 'data-offset' )
        el.addEventListener( 'click', function( event ) {
            let start = this.getAttribute( 'data-offset' )
            let newUrl = this.getAttribute( 'data-href' ).toString()
            let offset_clause = ''
            offset_clause = ( start == 'show_all' ) ? '&show_all=yes' : ( ( +start > 0 ) ? '&offset=' + start : '' )
            let ajaxurl
            if ( is_DataTable ) {
                if ( is_SearchPage ) {
                    ajaxurl = newUrl + '&operation=get_data'
                } else {
                    ajaxurl = '/index.php?categoryID=' + tpl_categoryID + offset_clause + '&operation=get_data'
                }
            } else {
                if ( is_SearchPage ) {
                    ajaxurl = newUrl + '&operation=get_cards'
                } else {
                    ajaxurl = '/index.php?categoryID=' + tpl_categoryID + offset_clause + '&operation=get_cards'
                }
            }
            updateURL( newUrl )
            let reqData = {
                'operation': 'draw_Cards',
                'tpl_categoryID': tpl_categoryID,
                'categoryID': tpl_categoryID,
                'params': FilterParams()
            }
            _drawCardsAjax( ajaxurl, reqData )
        }, false )
    } )
}

function bindAddToCartReactions() {
    Array.prototype.forEach.call( document.querySelectorAll( `button[name="buttonAddToCart"]` ), function( el ) {
        let productID = el.getAttribute( 'data-productID' )
        let categoryID = el.getAttribute( 'data-categoryID' )
        let is_fake_clause = el.getAttribute( 'data-is_fake_clause' )
        let input_element = document.querySelector( "form#HiddenFieldsForm_" + productID + " input[name=multyaddcount]" )
        el.addEventListener( 'click', function( event ) {
            let addcount = input_element.value
            if ( +addcount > 0 ) {
                let forse = 'do=cart' + '&xcart=yes' + is_fake_clause + '&addproduct=' + productID + '&multyaddcount=' + addcount
                return doLoad( forse, productID )
            }
        }, false )
    } )
}