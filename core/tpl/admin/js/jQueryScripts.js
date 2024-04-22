// jQueryScripts.js
function bindOrderStatusesCheckboxesReactions() {
    var chks_include = ''
    if ($('#status_chks').length) {
        var $all = $('#status_chks input[data-statusID]:checkbox')
        var $checked = $('#status_chks input[data-statusID]:checkbox:checked')
        var $unchecked = $('#status_chks input[data-statusID]:checkbox:not(:checked)')
        if ($all.length > 0 && $checked.length > 0) {
            chks_include = function() {
                var res = ''
                var myChks = []
                $checked.each(function() {
                    myChks.push($(this).attr('data-statusID').toString())
                })
                res = myChks.join(',')
                console.log(res)
                return res.toString()
            }
        }
    }
    $('input#order_search_type1[type="radio"]').on('change', function() {
        $('input#order_search_type2[type="radio"]').prop('checked', false)
        $('#id_checkall').prop('checked', false)
        document.getElementById('id_checkall').indeterminate = false
        $('#status_chks input[data-statusID]:checkbox').prop('checked', false)
    })
    $('input#order_search_type2[type="radio"]').on('change', function() {
        $('input#order_search_type1[type="radio"]').prop('checked', false)
        $('input#orderID_textbox').val("")
    })
    $('input#orderID_textbox').on('keyup', function() {
        $('input#order_search_type1[type="radio"]').prop('checked', true)
        $('input#order_search_type2[type="radio"]').prop('checked', false)
        $('#id_checkall').prop('checked', false)
        $('#status_chks input[data-statusID]:checkbox').prop('checked', false)
    })
    /* выбрать все чекбоксы для статусов заказов*/
    $('#id_checkall').change(function() {
        if ($(this).is(':checked')) {
            $('#status_chks input[data-statusID]:checkbox').prop('checked', true)
            $('#status_chks label[data-statusID]').removeClass('text-muted text-decoration-line-through')
        } else {
            $('#status_chks input[data-statusID]:checkbox').prop('checked', false)
            $('#status_chks label[data-statusID]').addClass('text-muted text-decoration-line-through')
        }
        $('input#orderID_textbox').val("") + $('input#order_search_type1[type="radio"]').prop('checked', false)
        $('input#order_search_type2[type="radio"]').prop('checked', true)
    })
    /* выбрать все чекбоксы для статусов заказов*/
    $('#status_chks input[data-statusID]:checkbox').on('change', function() {
        $('input#orderID_textbox').val("")
        $('input#order_search_type1[type="radio"]').prop('checked', false)
        $('input#order_search_type2[type="radio"]').prop('checked', true)
        if ($('#status_chks input[data-statusID]:checkbox:checked').length > 0 && $('#status_chks input[data-statusID]:checkbox:not(:checked)').length > 0) {
            document.getElementById('id_checkall').indeterminate = true
        } else {
            document.getElementById('id_checkall').indeterminate = false
        }
        let statusID = $(this).attr('data-statusID')
        if ($(this).is(':checked')) {
            $('#status_chks label[data-statusID ="' + statusID + '"]').removeClass('text-muted text-decoration-line-through')
        } else {
            $('#status_chks label[data-statusID ="' + statusID + '"]').addClass('text-muted text-decoration-line-through')
        }
    })
    /* выбрать все чекбоксы для статусов заказов*/
    // ОСНОВНВЯ ТАБЛИЦА
    $('#id_chall').change(function() {
        if ($(this).is(':checked')) {
            $('div.order_chk input[data-orderID]:checkbox').prop('checked', true)
            $('div.order_chk label[data-orderID]').removeClass('text-muted')
        } else {
            $('div.order_chk input[data-orderID]:checkbox').prop('checked', false)
            $('div.order_chk label[data-orderID]').addClass('text-muted')
        }
    })
    $('div.order_chk input[data-orderID]:checkbox').on('change', function() {
        if ($('div.order_chk input[data-orderID]:checkbox:checked').length > 0 && $('div.order_chk input[data-orderID]:checkbox:not(:checked)').length > 0) {
            document.getElementById('id_chall').indeterminate = true
        } else {
            document.getElementById('id_chall').indeterminate = false
        }
        let orderID = $(this).attr('data-orderID')
        if ($(this).is(':checked')) {
            $('div.order_chk label[data-orderID ="' + orderID + '"]').removeClass('text-muted')
        } else {
            $('div.order_chk label[data-orderID ="' + orderID + '"]').addClass('text-muted')
        }
    })
}
bindOrderStatusesCheckboxesReactions()