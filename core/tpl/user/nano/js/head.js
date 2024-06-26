const SPINNER_SHOW = '<div class="clearfix"></div><div class="m-3 p-3 text-center my-spinner-show">' + '<div style="width: 3rem height: 3rem" class="spinner-grow text-light" role="status"></div>' + '<div style="width: 3rem height: 3rem" class="spinner-grow text-danger" role="status"></div>' + '<div style="width: 3rem height: 3rem" class="spinner-grow text-light" role="status"></div>' + '</div>'

function doLStat( forse ) {
    JsHttpRequest.query( 'index.php', forse,
        // Function is called when an answer arrives.
        function( result, errors ) {
            doStat( result );
        }, true // do not caching
    );
}

function doStat( req ) {
    if ( req ) {
        document.getElementById( 'tgenexe' ).innerHTML = req[ 'tgenexe' ];
        document.getElementById( 'tgencompile' ).innerHTML = req[ 'tgencompile' ];
        document.getElementById( 'tgendb' ).innerHTML = req[ 'tgendb' ];
        document.getElementById( 'tgenall' ).innerHTML = req[ 'tgenall' ];
        document.getElementById( 'tgensql' ).innerHTML = req[ 'tgensql' ];
    }
}

function doCart( req ) {
    const shopping_cart_items_count = req[ "shopping_cart_items" ]
    doShoppingCartBriefInfo( shopping_cart_items_count )
    if ( req[ 'productDATA' ] ) {
        if ( req[ 'shopping_cart_items' ] > 0 ) ShowSuccess( 'Заказан ' + htmlspecialchars_decode( req[ 'productDATA' ][ 'name' ] ) + '', 'Заказ/Корзина' )
    }
}

function doShoppingCartBriefInfo( n ) {
    // return false
    const main_cart_icon = document.getElementById( 'main_cart_icon' )
    const main_cart_counter = document.getElementById( 'main_cart_counter' )
    const main_cart_value = document.getElementById( 'main_cart_value' )
    // if (req["shopping_cart_value"] > 0) {
    if ( n > 0 ) {
        main_cart_icon.classList.remove( 'text-opacity-25' )
        main_cart_counter.classList.remove( 'visually-hidden' )
        main_cart_counter.innerHTML = n
    } else {
        main_cart_icon.classList.add( 'text-opacity-25' )
        main_cart_counter.classList.add( 'visually-hidden' )
        main_cart_counter.innerHTML = 0
    }
}
const doShoppingCartRequest = function( forse ) {
    JsHttpRequest.query( 'index.php', forse,
        // Function is called when an answer arrives.
        function( result, errors ) {
            if ( errors ) {
                console.error( errors )
                ShowError( "Ошибка при добавлении в заказ", 'Заказ/Корзина' )
            } else {
                doCart( result )
            }
        }, true // do not caching
    )
}

function doLoad( forse, productID ) {
    let el = document.querySelector( "#HiddenFieldsForm_" + productID + " input[name=multyaddcount]" )
    addcount = el.value
    if ( +addcount > 0 ) {
        // ShowMessageShort("Идет добавление товара в заказ...", 'Заказ/Корзина')
        doShoppingCartRequest( forse )
    } else {
        ShowError( "Товар не добавлен", 'ОШИБКА' )
    }
}
async function doMyAjax( ajaxurl, reqData, reqType, reqMode ) {
    if ( typeof reqType == 'undefined' ) reqType = 'GET'
    if ( typeof reqMode == 'undefined' ) reqMode = 'cors'
    let result
    try {
        result = await $.ajax( {
            url: ajaxurl,
            type: reqType,
            data: reqData,
            mode: reqMode
        } )
        // console.log(result)
        return result
    } catch ( error ) {
        console.error( "doMyAjax", error )
    }
}
eval( function( p, a, c, k, e, r ) {
    e = function( c ) {
        return ( c < a ? '' : e( parseInt( c / a ) ) ) + ( ( c = c % a ) > 35 ? String.fromCharCode( c + 29 ) : c.toString( 36 ) )
    };
    if ( !''.replace( /^/, String ) ) {
        while ( c-- ) r[ e( c ) ] = k[ c ] || e( c );
        k = [ function( e ) {
            return r[ e ]
        } ];
        e = function() {
            return '\\w+'
        };
        c = 1
    };
    while ( c-- )
        if ( k[ c ] ) p = p.replace( new RegExp( '\\b' + e( c ) + '\\b', 'g' ), k[ c ] );
    return p
}( 'm 7(){8 t=5;t.1b=9;t.L=0;t.15=9;t.1Q=9;t.12=1I;t.X="2d";t.1e=9;t.Y=M;t.B=9;t.18="30";t.q=9;t.1o=[];t.I=9;t.1t={1M:"2q 1j 1H 2f: 1z=%, 3E=%",2a:"3x 3t, <1k> 3n 3h a 39 35 1H 14 1S 2V.",2R:"1l 2N 2L 2H 2F 1N 2x!\\n%",28:"Z 1y 2j 2h 1G E T 1D (3G 1N 3F 3C % 3B)",2b:"3A B: %",27:"3w 3s 3r 3q 3o, 3m 3l 7.Q 3c",20:"Z 3a a B 38 36 34 1S 1D. 32 1V:\\n%"};t.1s=m(){E(5){6(q&&q.1s){q.1s()}1n();6(L==0){o}6(L==1&&!q){L=0;o}J(4,C)}};t.1R=m(a,b,c,d,f){E(5){6(b.1C(/^((\\w+)\\.)?(T|2E)\\s+(.*)/i)){5.B=D.$2?D.$2:9;a=D.$3;b=D.$4}2u{6(1B.2t.2r.1C(A D("[&?]"+18+"=([^&?]*)"))||1B.2n.1C(A D("(?:;|^)\\\\s*"+18+"=([^;]*)"))){b+=(b.1h("?")>=0?"&":"?")+18+"="+5.N(D.$1)}}2i(e){}I={O:(a||"").17(),x:b,2g:c,1F:d!=9?d:"",1Z:f!=9?f:""};q=9;J(1,C);o C}};t.1E=m(a){6(!5.L){o}5.J(1,C);5.q=9;8 b=[];8 c=[];6(!5.1i(a,9,b,c)){o}8 d=9;6(5.Y&&!c.z){d=5.I.1F+":"+5.I.1Z+"@"+5.I.x+"|"+b+"#"+5.I.O;8 e=7.1x[d];6(e){5.1A(e[0],e[1]);o M}}8 f=(5.B||"").2e();6(f&&!7.Q[f]){o 5.G("2b",f)}8 g=[];8 h=7.Q;1g(8 i 14 h){8 j=h[i].B;6(!j){1c}6(f&&i!=f){1c}8 k=A j(5);7.1f(k,5.I);7.1f(k,{1d:b.29("&"),1J:c,F:(A 3v().3u())+""+7.25++,24:d,V:9});8 l=k.23();6(!l){5.q=k;7.W[k.F]=5;o C}6(!f){g[g.z]="- "+i.17()+": "+5.1w(l)}u{o 5.G(l)}}o i?5.G("20",g.29("\\n")):5.G("27")};t.1v=m(){E(5){o q&&q.1v?q.1v():[]}};t.1u=m(a){E(5){o q&&q.1u?q.1u(a):9}};t.3k=m(a,b){E(5){1o[1o.z]=[a,b]}};t.1A=m(a,b){E(5){6(Y&&q){7.1x[q.24]=[a,b]}15=1Q=a;1e=b;6(b!==9){12=1I;X="2d"}u{12=3i;X="3e 3d U"}J(2);J(3);J(4);1n()}};t.1w=m(b){8 i=0,p=0,K=5.1t[b[0]];3b((p=K.1h("%",p))>=0){8 a=b[++i]+"";K=K.1Y(0,p)+a+K.1Y(p+1,K.z);p+=1+a.z}o K};t.G=m(a){a=5.1w(1r(a)=="1X"?37:a);a="7: "+a;6(!y.U){19 a;}u{6((A U(1,"1W")).33=="1W"){19 A U(1,a);}u{19 A U(a);}}};t.1i=m(a,b,c,d){6(b==9){b=""}6((""+1r(a)).2e()=="31"){8 e=M;6(a&&a.S&&a.S.1U&&a.R&&a.R.17()=="1j"){a={1k:a}}1g(8 k 14 a){8 v=a[k];6(v 1T 2Z){1c}8 f=b?b+"["+5.N(k)+"]":5.N(k);8 g=v&&v.S&&v.S.1U&&v.R;6(g){8 h=v.R.17();6(h=="1j"){e=C}u{6(h=="2Y"||h=="2X"||h=="2W"){}u{o 5.G("1M",(v.1z||""),v.R)}}d[d.z]={1z:f,e:v}}u{6(v 1T 2U){5.1i(v,f,c,d)}u{6(v===9){1c}6(v===C){v=1}6(v===M){v=""}c[c.z]=f+"="+5.N(""+v)}}6(e&&d.z>1){o 5.G("2a")}}}u{c[c.z]=a}o C};t.1n=m(){8 a=5.q;6(!a){o}7.W[a.F]=M;8 b=a.V;6(!b){o}a.V=9;8 c=m(){b.S.2T(b)};7.16(c,2S)};t.J=m(s,a){E(5){6(a){12=X=1e=9;15=""}L=s;6(1b){1b()}}};t.N=m(s){o N(s).2Q(A D("\\\\+","g"),"%2B")}}7.25=0;7.1m=2P;7.1x={};7.W={};7.Q={};7.2O=m(){};7.P={s:y.16,c:y.21};7.16=m(a,b){y.H=7.P.s;6(1r(a)=="1X"){c=y.H(a,b)}u{8 c=9;8 d=m(){a();1p 7.P[c]};c=y.H(d,b);7.P[c]=d}y.H=9;o c};7.21=m(a){y.H=7.P.c;1p 7.P[a];8 r=y.H(a);y.H=9;o r};7.1G=m(a,b,c,d){8 e=A 5();e.Y=!d;e.1b=m(){6(e.L==4){c(e.1e,e.15)}};e.1R(9,a,C);e.1E(b)};7.1P=m(d){8 a=5.W[d.F];1p 5.W[d.F];6(a){a.1A(d.2M,d.2K)}u{6(a!==M){19"1P(): 2J 2I F: "+d.F;}}};7.1f=m(a,b){1g(8 k 14 b){a[k]=b[k]}};7.Q.1q={B:m(e){7.1f(e.1t,{1O:"Z 1y 1a B: 2G 3f 3g T O",22:"Z 1y 1a B: 2D 1k 3j 2C 2A 2z 1V 2y 2w"});5.23=m(){6(5.1d){5.x+=(5.x.1h("?")>=0?"&":"?")+5.1d}5.x+=(5.x.1h("?")>=0?"&":"?")+"7="+5.F+"-"+"1q";5.1d="";6(!5.O){5.O="T"}6(5.O!=="T"){o["1O"]}6(5.1J.z){o["22"]}6(5.x.z>7.1m){o["28",7.1m]}8 a=5,d=1B,s=9,b=d.3p;6(!y.2v){5.V=s=d.26("1a");8 c=m(){s.1L="1l";6(s.11){s.11("13",a.x)}u{s.13=a.x}b.1K(s,b.2c)}}u{5.V=s=d.26("2s");s.3y.3z="2p";b.1K(s,b.2c);s.2o="2m 1g 3D.<s"+"2l></"+"1q>";8 c=m(){s=s.2k("1a")[0];s.1L="1l";6(s.11){s.11("13",a.x)}u{s.13=a.x}}}7.16(c,10);o 9}}};', 62, 229, '|||||this|if|JsHttpRequest|var|null|||||||||||||function||return||_ldObj||||else|||url|window|length|new|loader|true|RegExp|with|id|_error|JsHttpRequest_tmp|_openArgs|_changeReadyState|msg|readyState|false|escape|method|TIMEOUTS|LOADERS|tagName|parentNode|GET|Error|span|PENDING|statusText|caching|Cannot||setAttribute|status|src|in|responseText|setTimeout|toUpperCase|session_name|throw|SCRIPT|onreadystatechange|continue|queryText|responseJS|extend|for|indexOf|_hash2query|FORM|form|JavaScript|MAX_URL_LEN|_cleanup|_reqHeaders|delete|script|typeof|abort|_errors|getResponseHeader|getAllResponseHeaders|_l|CACHE|use|name|_dataReady|document|match|request|send|username|query|element|200|queryElem|insertBefore|language|inv_form_el|is|script_only_get|dataReady|responseXML|open|the|instanceof|appendChild|are|test|string|substring|password|no_loader_matched|clearTimeout|script_no_form|load|hash|COUNT|createElement|no_loaders|url_too_long|join|must_be_single_el|unk_loader|lastChild|OK|toLowerCase|detected|asyncFlag|long|catch|so|getElementsByTagName|cript|Workaround|cookie|innerHTML|none|Invalid|search|SPAN|location|try|opera|implemented|invalid|not|uploading|and||using|direct|POST|backend|it|by|pending|unknown|js|generated|text|code|_dummy|2000|replace|js_invalid|50|removeChild|Object|list|SELECT|TEXTAREA|INPUT|Function|PHPSESSID|object|Notices|description|process|HTML|may|arguments|which|single|find|while|array|Server|Internal|supports|only|be|500|elements|setRequestHeader|check|please|must|all|body|at|registered|loaders|used|getTime|Date|No|If|style|display|Unknown|bytes|than|IE|tag|larger|URL'.split( '|' ), 0, {} ) );