$.fn.popupCreate = function() {
    let $popup = this;
    
    $popup.addClass('popup show');
    
    if($('.popup-shield').length == 0) {
        $('body').append('<div class="popup-shield show"></div>');
    } else {
        $('.popup-shield').addClass('show');
    }
}

$.fn.popupDismantle = function() {
    let $popup = this;
    
    $popup.trigger('close').removeClass('popup show');

    if($('.popup.show').length == 0) {
        $('.popup-shield').removeClass('show');
    }
}

$(document).on('click', '.popup-shield', function() {
    $('.popup.show').popupDismantle();
})