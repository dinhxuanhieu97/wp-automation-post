$(document).ready(function ($) {

    var buttons = $('.mona-call-buttons');
    if ( buttons.hasClass('buttonsMobile') ) {
        $('.mona-call-buttons .support-content').hide();
    }

    $('.mona-call-buttons .btn-support').click(function(e) {
        e.stopPropagation();
        $('.mona-call-buttons .support-content').slideToggle();
    });

    $('.mona-call-buttons .support-content').click(function(e) {
        e.stopPropagation();
    });

});