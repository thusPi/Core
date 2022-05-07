$(document).on('thuspi.ready', function() {
    if(thusPi.page.current() != 'admin/devices/manage') {
        return false;
    }

    // Load available handlers
    thusPi.api.call('extensions/list-features', {feature: 'devices/handlers'}).then(function(response) {
        const $handlerInput = $('input[name="handler"]');
        let results = [];

        console.log(response);

        $.each(response.data, function(handlerId, handler) {
            console.log(handler);
            $handlerInput.data('input').addResult({
                value: handlerId,
                shownValue: handler
            });
        })
    })
})