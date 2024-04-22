const getColumns = function(params) {
    let tableRowNo = params.tableRowNo
    let MaxRowNumPow = params.MaxRowNumPow
    let currency_code = params.currency_code
    let template = params.template
    let res = {}
    let products = []
    let products_filtered = []
    products = [
        // -------- [0] --------------------------//
        {
            name: 'tableRowNo',
            data: null,
            title: '#',
            type: 'html',
            visible: true,
            searchable: false,
            orderable: false,
            cellType: 'th',
            className: 'w-1 text-end text-nowrap text-secondary text-small align-middle',
            // className: 'text-info text-end text-nowrap opacity-50',
            render: function(data, type, row) {
                // нумеруем строки
                params.tableRowNo++
                    let zerofilled = zeroPad(params.tableRowNo, params.MaxRowNumPow)
                return zerofilled
            },
        },
        // -------- [1] --------------------------//
        {
            name: 'product_code',
            data: 'product_code',
            title: 'Артикул&nbsp;',
            type: 'html-num',
            visible: true,
            searchable: true,
            orderable: true,
            cellType: 'td',
            className: 'w-5 text-end text-nowrap align-middle',
            render: function(data, type, row) {
                return '<span class="text-danger">[' + data + ']</span>'
            },
        },
        // -------- [2] --------------------------//
        {
            name: 'product_name',
            data: 'product_name',
            title: 'Наименование&nbsp;',
            type: 'html',
            visible: true,
            searchable: true,
            orderable: true,
            contentPadding: 'mmmmmmmmmmmm',
            className: 'text-start align-middle',
        },
        // -------- [3] --------------------------//
        {
            name: 'brief_description',
            data: 'brief_description',
            title: 'Описание',
            type: 'html',
            visible: false,
            searchable: true,
            orderable: false,
            contentPadding: 'mmm',
            className: 'text-start align-middle',
        },
        // -------- [4] --------------------------//
        {
            name: 'priceOUT',
            data: 'priceOUT',
            title: 'Цена,&nbsp;' + currency_code + '&nbsp;&nbsp;',
            type: 'num-fmt',
            visible: true,
            searchable: false,
            orderable: true,
            className: 'w-10 text-end text-nowrap align-middle'
        },
        // -------- [5] --------------------------//
        {
            name: 'in_stock_string',
            data: 'in_stock_string',
            type: 'html',
            title: 'Наличие&nbsp;',
            visible: true,
            searchable: false,
            orderable: true,
            className: 'w-10 text-center text-nowrap align-middle',
            contentPadding: 'mm'
        },
        // -------- [6] --------------------------//
         {
            name: 'qnt_to_show',
            data: 'qnt_to_show',
            title: 'Кол-во&nbsp;',
            type: 'html-num',
            visible: false,
            searchable: false,
            orderable: true,
            className: 'w-10 text-end text-nowrap align-middle',
            contentPadding: 'iii',
        },
        // -------- [7] --------------------------//
        {
            name: 'productID',
            data: 'productID',
            title: 'productID',
            type: 'num',
            visible: false,
            searchable: true,
            orderable: true,
            className: 'text-end text-nowrap align-middle'
        },
        // -------- [8] --------------------------//
        {
            name: 'priceUSD',
            data: 'priceUSD',
            title: 'Цена,&nbsp;уе&nbsp;',
            type: 'num-fmt',
            visible: false,
            searchable: false,
            orderable: true,
            className: 'w-10 text-end text-nowrap align-middle utility'
        },
        // -------- [9] --------------------------//
        {
            name: 'makeBuyButton',
            data: null,
            title: 'Купить&nbsp;&nbsp;',
            type: 'html',
            visible: true,
            searchable: false,
            orderable: false,
            className: 'w-10 align-middle',
            // render: function(data, type, full, meta) {
            render: function(data) {
                const btn = makeBuyButton(data)
                return btn
            }
        },
        // -------- [10] --------------------------//
        {
            name: 'sort_order',
            data: 'sort_order',
            title: 'sort_order&nbsp;',
            type: 'num',
            visible: false,
            searchable: false,
            orderable: true,
            className: 'text-end align-middle',
        },
        // -------- [11] --------------------------//
        {
            name: 'category_name',
            data: 'category_name',
            title: 'Категория&nbsp;&nbsp;',
            type: 'html',
            visible: false,
            searchable: true,
            orderable: true,
            contentPadding: 'mmm',
            className: 'text-center align-middle',
            render: function(data, type, row) {
                return '<a href="/category_' + row.categoryID + '.html" class="d-inline-block text-truncate text-decoration-none fw-lighter text-primary fs-7 py-1" title="' + data + '">' + data + '</a>'
            },
        }
    ]
    if (template == "products") {
        res = products
    } else {
        res = null
    }
    // console.log("res", res)
    return res
}