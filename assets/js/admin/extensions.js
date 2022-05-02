thusPiAssign('admin.extensions', {
    print_list: (query) => {
        thusPi.api.call('extensions/search', {'query': query}).then(response => {
            const $results = $('.extension-search-results');
            $results.empty();

            $.each(response.data, function(i, extension) {
                const $extension = thusPi.template.get('.extension-brief');

                $extension.find('.extension-name').text(extension.name);
                $extension.find('.extension-description').text(extension.description || extension.name);
                $extension.find('.extension-owner').text(extension.repository.owner.name);
                $extension.find('.extension-stars').text(extension.repository.stars_count);
                $extension.find('.extension-issues').text(extension.repository.open_issues_count);
                $extension.find('.extension-pushed-ago').text(extension.repository.pushed_ago);
                $extension.attr('data-verified', extension.verified);
                $extension.attr('href', `/#/admin/extensions/view/?id=${extension.id}`);
                

                $extension.appendTo($results);
            })
        })
    },

    install: (id) => {
        $('[data-extension-action="install"]').showLoading();
        thusPi.api.call('extensions/install', {'id': id}).then(response => {
            thusPi.message.send(thusPi.locale.translate('admin.extensions.message.installed', [response.data.name]));
            $('[data-extension-action="install"]').hideLoading();
            thusPi.page.reload();
        })
    },

    uninstall: (id) => {
        $('[data-extension-action="uninstall"]').showLoading();
        setTimeout(() => {
            thusPi.api.call('extensions/uninstall', {'id': id}).then(response => {
                thusPi.message.send(thusPi.locale.translate('admin.extensions.message.uninstalled', [response.data.name]));
                $('[data-extension-action="enable"]').hideLoading();
                thusPi.page.reload();
            })
        }, 1500);
    },

    enable: (id) => {
        $('[data-extension-action="enable"]').showLoading();
        setTimeout(() => {
            thusPi.api.call('extensions/toggle', {'id': id, 'action': 'enable'}).then(response => {
                thusPi.message.send(thusPi.locale.translate('admin.extensions.message.enabled', [response.data.name]));
                $('[data-extension-action="enable"]').hideLoading();
                thusPi.page.reload();
            })
        }, 250);
    },

    disable: (id) => {
        $('[data-extension-action="disable"]').showLoading();
        setTimeout(() => {
            thusPi.api.call('extensions/toggle', {'id': id, 'action': 'disable'}).then(response => {
                thusPi.message.send(thusPi.locale.translate('admin.extensions.message.disabled', [response.data.name]));
                $('[data-extension-action="disable"]').hideLoading();
                thusPi.page.reload();
            })
        }, 250);
    }
})