$(document).on('mouseenter mousemove focus', '[data-tooltip]', function(e) {
    if($('#tooltip').length == 0) {
        $('body').append('<div id="tooltip"></div>');
    }

    const $tooltip = $('#tooltip');
    const $elem    = $(this);
    const position = $elem.attr('data-tooltip-position') ?? 'center';
    const text     = $elem.attr('data-tooltip');

    if(text.includes('<a')) {
        $tooltip.attr('data-pointer-events', 'all');
    } else {
        $tooltip.attr('data-pointer-events', 'none');
    }

    $tooltip.attr('data-position', position).html(text);

    $tooltip.addClass('show');
    $tooltip.css({'top': thusPi.data.pointer.y, 'left': thusPi.data.pointer.x})
}).on('mouseleave focusout', '[data-tooltip], #tooltip', function(e) {
    const $tooltip = $('#tooltip');
    $tooltip.removeClass('show');
})

$(document).on('mouseenter', '#tooltip', function() {
    let tooltip = $('#tooltip');
    tooltip.addClass('show');
})