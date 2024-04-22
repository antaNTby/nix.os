"use strict"
const phpProductID = document.getElementById( 'ProductID' ).value
const phpCategoryID = document.getElementById( 'CategoryID' ).value
const buttonAddToCart = document.querySelector( '[name="buttonAddToCart"]' )
let productID
let categoryID

function bindHtmlProductReactions() {
    if ( buttonAddToCart ) {
        let productID = buttonAddToCart.getAttribute( 'data-productID' )
        let categoryID = buttonAddToCart.getAttribute( 'data-categoryID' )
        let is_fake_clause = buttonAddToCart.getAttribute( 'data-is_fake_clause' )
        let el = document.querySelector( "form#HiddenFieldsForm_" + productID + " input[name=multyaddcount]" )
        buttonAddToCart.addEventListener( 'click', function( event ) {
            let addcount = el.value
            if ( +addcount > 0 ) {
                let forse = 'do=cart' + '&xcart=yes' +is_fake_clause + '&addproduct=' + productID + '&multyaddcount=' + addcount
                return doLoad( forse, productID )
            }
        } )
    }
}
$( document ).ready( function() {
    let allGalleries = document.querySelectorAll( '.light-gallery' );
    allGalleries.forEach( item => lightGallery( item, {
        // licenseKey: '0000-0000-antaNT64',
        licenseKey: '0000-0000-000-0000',
        plugins: [
            lgThumbnail,
            // lgFullscreen,
            lgRotate,
            lgZoom,
        ],
        thumbnail: true,
        mousewheel: true,
        download: false,
        speed: 1000,
        showCloseIcon: true,
        // mode: 'lg-slide-skew-cross',
        // mode: 'lg-rotate',
        // mode: 'lg-lollipop',
        mode: 'lg-rotate-rev',


        showZoomInOutIcons: true,
        zoom :true,
        scale:2.5,
        enableZoomAfter:400,

        actualSize: false,
        // actualSizeIcons: {
        //     zoomIn: 'lg-actual-size',
        //     zoomOut: 'lg-zoom-out'
        // },

        // actualSizeIcons: {
        //     zoomIn: 'lg-zoom-in',
        //     zoomOut: 'lg-zoom-out',
        // },


        container :"#gallery-container",
        // height:"40%",
        // width:"40%",
        showMaximizeIcon:true,
        showMinimazeIcon:true,
    } ) )
    if ( allGalleries.length > 0 ) {
        document.querySelector( '.startGalleryBtn[role="button"]' ).addEventListener( 'click', function() {
            var startGalleryEvent = new CustomEvent( 'click', {
                bubbles: true,
                cancelable: true
            } );
            document.querySelector( '.firstGalleryItem' ).dispatchEvent( startGalleryEvent );
        } )
    }
    bindHtmlProductReactions()
} )