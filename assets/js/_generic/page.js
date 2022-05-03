thusPiAssign('page', {
    load(page, reload = false) {
        page = trim(trim(trim(page, '/'), '#'), '/');
        window.location.hash = `/${page}`;
        
        if(reload) {
            window.location.reload(true);
        }
    },

    get(url, reload = false) {
        let currentPage = thusPi.page.current();
        let old_animation_duration = parseFloat($('main').attr('data-animation-duration') || 0);
        let reduced_motion = thusPi.users.currentUser.getSetting('reduced_motion') || false;

        url = trim(trim(trim(url, '/'), '#'), '/');

        if(!reduced_motion) {
            thusPi.page.animateOut();
        }

        // Show loading icon when old page has animated out but new page has not been received yet
        setTimeout(function() {
            if(thusPi.page.current() == currentPage) {
                if(thusPi.page.getStatus() != 'error') {
                    thusPi.page.setStatus('animating_loading');
                }
            }
        }, old_animation_duration);

        // Make a request to load the page
        thusPi.api.call('page', {'url': url}).then(function(response) {
            let new_animation_duration = response.data.manifest.animation_duration || 0;

            $('body').attr('data-page', response.data.manifest.name.trim('/'));
            $('main').attr('data-animation-duration', new_animation_duration);
            
            // Wait for the old page to finish animating out
            setTimeout(function() { 
                if(reload) {
                    window.location.hash = `#/${url}`;
                    window.location.reload(true);
                    return true;
                }

                $('main').html(response.data.html);
               
                // Make the new sidenav item active
                $('.sidenav-link').removeClass('active');
                $(`.sidenav-link[data-target="${response.data.manifest.name.split('/')[0]}"]`).addClass('active');
                
                $(document).trigger('thusPi.load');

                thusPi.page.setStatus('animating_in');
                thusPi.page.animateIn(new_animation_duration);
            }, Math.max(old_animation_duration - response.took, 0));
        }).catch(function(err) {
            console.error(err);
            $('main').html('');
            thusPi.page.setStatus('error');
        });
    },

    animateOut() {
        thusPi.page.setStatus('animating_out');
        $('.popup-shield').removeClass('show');
    },

    animateIn(animation_duration) {
        thusPi.page.animationSetup(animation_duration);
        
        thusPi.page.setStatus('animating_in');
        setTimeout(function() {
            $(document).trigger('thusPi.ready');
            console.log('PAGE READY!');
            thusPi.page.setStatus('ready');
        }, animation_duration);
    },

    animationSetup(animation_duration) {
        let transition_elements = {'fade': [], 'slide': []};
        transition_elements = {
            'fade': {
                'default': $('.transition-fade'),
                'order': $('.transition-fade-order'),
                'random': $('.transition-fade-random')
            },
            'slide': $('.transition-slide-top, .transition-slide-right, .transition-slide-bottom, .transition-slide-left')
        }

        transition_elements['fade']['default'].each(function(index, elem) {
            $(elem).css('transition', `${animation_duration}ms opacity`);
        });

        transition_elements['fade']['order'].each(function(index, elem) {
            let total = Math.max($(elem).siblings('.transition-fade-order').length, 1); // Can't be zero or negative
            let transition_delay = Math.round(index / total * animation_duration);
            $(elem).css('transition', `${animation_duration}ms opacity ${transition_delay}ms`);
        });

        transition_elements['fade']['random'].each(function(index, elem) {
            let transition_delay = Math.floor(Math.random() * (animation_duration/2 + 1));
            $(elem).css('transition', `${animation_duration-transition_delay}ms opacity ${transition_delay}ms`);
        });

        transition_elements['slide'].each(function(index, elem) {
            $(elem).css('transition', `${animation_duration}ms transform, ${animation_duration}ms opacity ease-in-out`);
        });
    },

    current(include_query = false) {
        const page  = $('body').attr('data-page');
        const query = window.location.hash.split('?')[1] || '';
        
        return include_query ? page + '?' + query : page;
    },

    getStatus() {
        return $('body').attr('data-status');
    },

    setStatus(status) {
        $('body').attr('data-status', status);
        return thusPi.page;
    },

    reload() {
        console.log(thusPi.page.current(true));
        thusPi.page.get(thusPi.page.current(true));
        return thusPi.page;
    }
})

$(window).on('load', function() {
	let page = window.location.hash.substring(1);

    if(page == '') {
	    thusPi.page.get('home/main');
    } else {
        thusPi.page.get(page);
    }
})

$(window).on('hashchange', function() {
	let page = window.location.hash.substring(1);
	thusPi.page.get(page);
})