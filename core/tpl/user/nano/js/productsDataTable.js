const $productsDataTable = function(BASE, ORDER_ARRAY) {
    var InitialStartFlag = 1
    let dtParams = {}
    dtParams.template = 'products'
    dtParams.currency_code = $('#tpl_currency_code').val()
    dtParams.tableRowNo = 0
    dtParams.MaxRowNumPow = 2
    const dtPageLength = 32
    let BlockOptions = {
        overlayCSS: {
            backgroundColor: '#000',
            opacity: 0.5,
            cursor: "wait"
        },
        fadeOut: 300,
        message: null
    }

    function _parseOffsetFromUrl(url) {
        let uri
        let start = 0
        uri = window.location.href
        uri = uri.replace(".html", '')
        uri = uri.replace(/[&=]+/g, '_')
        let arrr = uri.split('_offset_')
        start = arrr.at(-1)
        start = +start
        return start
    }

    function _isShowAllUrl() {
        let res = 0
        let uri = ''
        let start = 0
        uri = window.location.href
        const regex = /show_all/g
        if (uri.search(regex) > 0) {
            res = 1
        }
        return res
    }

    function _getDataUrl(BASE) {
        let init_url = ''
        let ajax_url = ''
        let start_clause = ''
        let showAll_clause = ''
        if (BASE == "category") {
            let $tpl_categoryID = $('#tpl_categoryID').val()
            init_url = '/index.php?categoryID=' + $tpl_categoryID
        } else {
            init_url = '/index.php?simple_search=yes'
        }
        if (_isShowAllUrl() === 1) {
            start_clause = ''
            showAll_clause = '&show_all=yes'
        } else {
            myOffset = _parseOffsetFromUrl()
            if (myOffset && myOffset > 0) {
                start_clause = '&offset=' + myOffset
                showAll_clause = ''
            }
        }
        if (InitialStartFlag === 1) {
            ajax_url = init_url + start_clause + showAll_clause
        } else {
            ajax_url = init_url
        }
        return ajax_url.toString()
    }

    const $table = $('table.mydatatable').DataTable({
        ajax: {
            url: _getDataUrl(BASE) + '&operation=get_data',
            type: 'POST',
            data: function(d) {
                d.operation = 'draw_DataTable'
                if (InitialStartFlag === 1) {
                    if (_isShowAllUrl() === 1) {
                        d.length = -1
                    } else {
                        myOffset = _parseOffsetFromUrl()
                        if (myOffset && myOffset > 0) {
                            d.start = myOffset
                            d.length = dtPageLength
                        }
                    }
                }
                d.params = FilterParams() ///
            },
        },
        serverSide: true,
        processing: true,
        searchDelay: 600,
        // "deferLoading":16000,
        columns: getColumns(dtParams),
        dom: "<'d-flex flex-column flex-md-row justify-content-center justify-content-md-between'<'p-2 w-100'l><'p-2 w-100'B><'p-2 w-100'f>>" + "<'w-100 text-center text-secondary m-0 p-2 lh-1'i>" + "<'table-responsive-lg'tr>" + "<'d-flex flex-column-reverse flex-md-row justify-content-center justify-content-md-between'<'p-2 w-100'l><'p-2'p>>" + "<'w-100 text-center text-secondary m-0 p-2 lh-1'i>",
        rowId: 'productID',
        orderCellsTop: true,
        ordering: true,
        order: ORDER_ARRAY,
        search: {
            return: true
        },
        responsive: false,
        autoWidth: true,
        language: KatzapskayaMova,
        background: true,
        paging: true,
        pageLength: dtPageLength,
        lengthMenu: [
            [16 / 2, 16, 16 * 2, 16 * 4, 16 * 8, 16 * 16], //
            [16 / 2 + ` тов/стр`, 16 + ` тов/стр`, 16 * 2 + ` тов/стр`, 16 * 4 + ` тов/стр`, 16 * 8 + ` тов/стр`, 16 * 16 + ` тов/стр`] //
        ],
        buttons: [

            //
            {
                className: 'btn btn-outline-secondary bg-light text-primary',
                background: true,
                titleAttr: 'Фильтр товаров',
                text: '<i class="bi bi-funnel-fill"></i>',
                action: function(e, dt, node, config) {
                    if (dt) {
                        el = document.querySelector('[data-action="show-filter"][data-bs-toggle="offcanvas"][data-bs-target="#offcanvasFilter"]')
                        el.click()
                    }
                }
            },
            //
            {
                action: function(e, dt, button, config) {
                    dt.ajax.reload();
                },
                text: '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i>',
                className: 'btn btn-secondary reload-button',
                background: false,
                titleAttr: 'Обновить таблицу',
            },
            //
            "copyHtml5",
            "excelHtml5",
            //
            {
                extend: 'colvis',
                background: true,
                fade: true,
                className: 'btn btn-secondary',
                columns: ':not(.noVis)',
                text: '<i class="bi bi-layout-three-columns"></i>',
                collectionLayout: 'two-column',
                columnText: function(dt, idx, title) {
                    if (idx != 10) {
                        return (idx) + ': ' + title
                    }
                }
            }
            //
        ],
        initComplete: function(settings, json) {
            // console.log(json)
            var api = new $.fn.dataTable.Api(settings)
            if (InitialStartFlag === 1) {
                if (json.data.length <= 512) {
                    $('div.dataTables_length select').append(new Option('Все товары', -1));
                } else {
                    $('div.dataTables_length select').append(new Option('Maximum', 512));
                }
                if (_isShowAllUrl() === 1) {
                    if (json.data.length <= 512) {
                        api.page.len(-1)
                        $table.page(0).draw(false)
                    } else {
                        api.page.len(512)
                        $table.page(0).draw(false)
                    }
                } else {
                    myOffset = _parseOffsetFromUrl()
                    if (myOffset && myOffset > 0) {
                        let dtPageNo = Math.floor(myOffset / (dtPageLength))
                        api.page.len(dtPageLength)
                        $table.page(dtPageNo).draw(false)
                    }
                }
            }
        }
    })
    $table.on('preInit.dt', function(e, settings) {
        var api = new $.fn.dataTable.Api(settings)
    })
    $table.on('init.dt', function(e, settings, json) {
        dtParams.tableRowNo = 0 // нумеруем строки
        InitialStartFlag = 0
    })
    $table.on('length.dt', function(e, settings, len) {
        InitialStartFlag = 0
        settings.ajax.url = _getDataUrl(BASE) + '&operation=get_data'
    })
    $table.on('page.dt', function(e, settings, len) {
        InitialStartFlag = 0
        settings.ajax.url = _getDataUrl(BASE) + '&operation=get_data'
    })
    $table.on('preXhr.dt', function(e, settings, data) {
        $('#offcanvasFilter').block(BlockOptions)
        dtParams.tableRowNo = 0 // нумеруем строки
    })
    $table.on('xhr.dt', function(e, settings, json, xhr) {
        if (json) {
            $('span#json_data_count').text(json.recordsFiltered)
            dtParams.MaxRowNumPow = 1 + Math.floor(Math.log(+json.count_of_data) / Math.LN10) // нумеруем строки
            if (json.cats) {
                createCatsTable(json.cats, json.cats_count, 'div#catsList')
            }
            if (json.priceLimits) {
                setPriceFilterLimits(json.priceLimits)
            }
            if (SetFlagPriceFilterChanged === 0) {
                setPriceFilterValues(json.priceLimits)
            }
        } else {
            dtParams.MaxRowNumPow = 2 // нумеруем строки
        }
        SetFlagPriceFilterChanged = 0
        $('#offcanvasFilter').unblock()
    })
    $('div.dataTables_filter label').addClass('d-flex justify-content-md-between align-items-center text-primary')
    $('div.dataTables_filter input[type="search"]').addClass('ms-3 w-100 border-primary').removeClass('form-control-sm').attr('placeholder', ' Нажмите Enter для подтверждения запроса')
    _setupProductFilter($table, _getDataUrl(BASE) + '&operation=get_data')
    return $table
}