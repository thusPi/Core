thusPiAssign('message', {
    send(content, type = 'info', duration = 2500) {
        const messageId = 'thuspi-message-' + Date.now();
        const $message = $(`<div id="${messageId}" data-message-type="${type}" class="message animate-in"><div class="message-inner">${content}</div></div>`);
        $message.appendTo('.message-area');

        setTimeout(function() {
            $message.removeClass('animate-in');
            setTimeout(function() {
                $message.addClass('animate-out');
                setTimeout(function() {
                    $message.remove();
                }, 600);
            }, duration);
        }, 10);
    },

    changeContent(messageId, content) {
        const $message = (`#thuspi-message-${messageId}`);

        return $message.html(content);
    },

    info(content) {
        thusPi.message.send(content, 'info');
    },

    error(content = null) {
        content = content || thusPi.locale.translate('generic.error');
        thusPi.message.send(content, 'error');
    },

    warning(content) {
        thusPi.message.send(content, 'warning');
    }
})
