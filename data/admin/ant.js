/*  сохранение таб в localStorage */
$(function($) {
    if ($('ul.nav[data-localStorage]').length) {
        let index = $('ul.nav[data-localStorage]').attr('data-localStorage').toString();
        let storage = localStorage.getItem('nav-tabs' + '__' + index);
        if (storage && storage !== "#") {
            $('.nav-tabs a[href="' + storage + '"]').tab('show');
        }
        $('ul.nav[data-localStorage] li').on('click', function() {
            let id = $(this).find('a').attr('href');
            localStorage.setItem('nav-tabs' + '__' + index, id);
        });
    }
});
/*  сохранение таб в localStorage */




