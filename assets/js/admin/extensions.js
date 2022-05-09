thusPiAssign('admin.extensions', {
    install(id) {
        $('[data-extension-action="install"]').showLoading();
        thusPi.api.call('extensions/install', {'id': id}).then(function(response) {
            thusPi.message.send(thusPi.locale.translate('admin.extensions.message.installed', [response.data.name]));
            $('[data-extension-action="install"]').hideLoading();
            thusPi.page.reload();
        }).catch(function(response) {
            $('[data-extension-action="install"]').hideLoading();
            thusPi.message.error(response.data);
        })
    },

    uninstall(id) {
        $('[data-extension-action="uninstall"]').showLoading();
        setTimeout(function() {
            thusPi.api.call('extensions/uninstall', {'id': id}).then(function(response) {
                thusPi.message.send(thusPi.locale.translate('admin.extensions.message.uninstalled', [response.data.name]));
                $('[data-extension-action="uninstall"]').hideLoading();
                thusPi.page.reload();
            }).catch(function(response) {
                $('[data-extension-action="uninstall"]').hideLoading();
                thusPi.message.error();
            })
        }, 1500);
    },

    enable(id) {
        $('[data-extension-action="enable"]').showLoading();
        setTimeout(function() {
            thusPi.api.call('extensions/toggle', {'id': id, 'action': 'enable'}).then(function(response) {
                thusPi.message.send(thusPi.locale.translate('admin.extensions.message.enabled', [response.data.name]));
                $('[data-extension-action="enable"]').hideLoading();
                thusPi.page.reload();
            }).catch(function(response) {
                $('[data-extension-action="enable"]').hideLoading();
                thusPi.message.error();
            })
        }, 250);
    },

    disable(id) {
        $('[data-extension-action="disable"]').showLoading();
        setTimeout(function() {
            thusPi.api.call('extensions/toggle', {'id': id, 'action': 'disable'}).then(function(response) {
                thusPi.message.send(thusPi.locale.translate('admin.extensions.message.disabled', [response.data.name]));
                $('[data-extension-action="disable"]').hideLoading();
                thusPi.page.reload();
            }).catch(function(response) {
                $('[data-extension-action="disable"]').hideLoading();
                thusPi.message.error();
            })
        }, 250);
    }
})

$(document).on('thuspi.ready', function() {
    if(thusPi.page.current() != 'admin/extensions/main') {
        return false;
    }

    // Extension search input
    const $input = $('.extension-search');
    
    // Load extension catalogue
    thusPi.api.call('extensions/catalogue').then(function(response) {
        // Append search results
        $.each(response.data, function(i, extension) {
            $input.data('input').addResult({
                value: extension.id,
                text: extension.name,
                description: `
                    <i class="far fa-user icon icon icon-scale-text text-blue"></i>
                    <span>${extension.repository.owner.name}</span>
                    <span>|</span>
                    <i class="far fa-star icon icon icon-scale-text text-yellow"></i>
                    <span>${extension.repository.stars_count}</span>
                    <span>|</span>
                    <i class="far fa-dot-circle icon icon icon-scale-text text-red d-none d-sm-inline"></i>
                    <span class="d-none d-sm-inline">${extension.repository.open_issues_count}</span>
                    <span class="d-none d-sm-inline">|</span>
                    <i class="far fa-code-commit icon icon icon-scale-text text-green"></i>
                    <span>${extension.repository.pushed_ago}</span>`,
                href: `#/admin/extensions/view/?id=${extension.id}`
            });
        })
    })

    // Load popular extensions
    thusPi.api.call('extensions/catalogue', {category: 'popular'}).then(function(response) {
        // Append search results
        // $.each(response.data, function(i, extension) {
        //     $input.data('input').addResult({
        //         value: extension.id,
        //         shownValue: extension.name,
        //         description: `
        //             <i class="far fa-user icon icon icon-scale-text text-blue"></i>
        //             <span>${extension.repository.owner.name}</span>
        //             <span>|</span>
        //             <i class="far fa-star icon icon icon-scale-text text-yellow"></i>
        //             <span>${extension.repository.stars_count}</span>
        //             <span>|</span>
        //             <i class="far fa-dot-circle icon icon icon-scale-text text-red d-none d-sm-inline"></i>
        //             <span class="d-none d-sm-inline">${extension.repository.open_issues_count}</span>
        //             <span class="d-none d-sm-inline">|</span>
        //             <i class="far fa-code-commit icon icon icon-scale-text text-green"></i>
        //             <span>${extension.repository.pushed_ago}</span>`,
        //         href: '#jeroen'
        //     });
        // })
    })
})