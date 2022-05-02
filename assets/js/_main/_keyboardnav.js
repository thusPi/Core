var keyDown = {};

$(document).on('keypress', function(e) {
    let key = e.key;
    let focus = $(':focus');
    
    if(key == ' ' || key == 'Enter' || key == 'Return') {
        if(focus.hasClass('btn')) {
            focus.trigger('click');
        }
    }
})

// Keyboard buttons
$(document).on('keydown', function(e) {
    keyDown[e.key] = true;
}).on('keyup', function(e) {
    delete keyDown[e.key];
})

// Mouse click
$(document).on('mousedown', function(e) {
    switch (e.which) {
        case 1:
            keyDown['mouseleft'] = true;
            break;
        case 2:
            keyDown['mousemiddle'] = true;
            break;
        case 3:
            keyDown['mouseright'] = true;
            break;
    }
}).on('mouseup', function(e) {
    switch (e.which) {
        case 1:
            delete keyDown['mouseleft'];
            break;
        case 2:
            delete keyDown['mousemiddle'];
            break;
        case 3:
            delete keyDown['mouseright'];
            break;
    }
})