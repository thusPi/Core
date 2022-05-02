var requestingLogs = false;

document.addEventListener('scroll', function (e) {
    if(thusPi.page.current() == 'admin/log/main') {
        let target = e.target;

        if(target.classList.contains('log-items')) {
            let scrollHeight = target.scrollHeight;
            let scrollTop    = target.scrollTop;
            let clientHeight = target.clientHeight;

            if(scrollTop >= scrollHeight*0.5 && requestingLogs === false) {
                requestMoreLogs();
            }

            // Prevent getting stuck
            if(scrollTop >= scrollHeight) {
                target.scrollTop = scrollTop;
            }
        }
    }
}, true);

function toggleLogCategory(category) {
    setTimeout(() => {
        let categories_inactive = getInactiveLogCategories();
        $('.log-items').attr('data-hide-categories', categories_inactive.join(','));
        
        return saveState('log_categories_inactive', categories_inactive.join(','));
    }, 1);
}

$(document).on('thusPi.ready', function() {
    if(thusPi.page.current() == 'admin/log/main') {
        let categories_inactive = getInactiveLogCategories();
        $('.log-items').attr('data-hide-categories', categories_inactive.join(','));
    }
})

function getInactiveLogCategories() {
    let categories_inactive = [];

    let $btns_inactive = $('.log-items-category-filter .btn:not(.active)');
    $btns_inactive.each(function() {
        categories_inactive.push($(this).attr('data-category'));
    })

    return categories_inactive;
}

function requestMoreLogs(amount = 25) {
    let $last_log_item = $('.log-item').last();
    let max = parseInt($last_log_item.attr('data-index')) - 1;
    if(max >= 0) {
        requestLogs(max - amount, max);
    }
}

async function refreshLogs(amount = 25) {
    console.log(`Refreshing ${amount} newest logs.`);
    let topItems    = $('.log-item:not(#log-item-template)').slice(0, amount);
    let newestAtMin = parseInt($('.log-item:not(#log-item-template)').first().attr('data-at'));
    
    if(newestAtMin > 0) {     
        const requestLogsStatus = await requestLogs(undefined, undefined, amount, true, newestAtMin, 'prepend');

        if(requestLogsStatus) {
            throwSuccess('Succesfully refreshed top logs.');
            topItems.remove();
        }

        return false;
    }
}

function refreshLogsLoop() {
    if(thusPi.page.current() == 'admin/log/main') {
        refreshLogs();
    
        setTimeout(() => {
            refreshLogsLoop();
        }, 1500);
    }
}

function requestLogs(min = null, max = null, nLatest = null, print = true, newestAtMin = 0, printMode = 'append') {
    requestingLogs = true;
    thusPi.api.call('logs-get', {'latest': nLatest, 'min': min, 'max': max}).then((response) => {
        console.log(response);
    }).catch(() => {
        console.error('Something went wrong while getting logs.');
    })
    // return new Promise(resolve => {
    //     var url = `/api/get/log.php?min=${min}&max=${max}&latest=${nLatest}&no_indexes=true`;
        
    //     $.get(url, function(response) {
    //         requestingLogs = false;

    //         response = JSON.parse(response);
    //         if(response['success'] == true) {
    //             console.log('Printing message.');

    //             if(printMode == 'append') {
    //                 var messages = response['message'].sort(sortLogMessagesInverted);
    //             } else if(printMode == 'prepend') {
    //                 var messages = response['message'].sort(sortLogMessages);
    //             } else {
    //                 reject('Invalid printMode.');
    //             }

    //             // End function if user wants the raw messages instead
    //             if(!print) {
    //                 resolve(messages);
    //             }

    //             if(response['newest_item']['at'] <= newestAtMin) {
    //                 console.log(`Not printing messages because newestAtMin=${newestAtMin} and newest_item.at=${response['newest_item']['at']}.`)
    //                 return;
    //             }

    //             $.each(messages, function(key, message) {
    //                 printLogMessage(message['title'], message['content'], message['category'], message['at'], message['at_readable'], message['index'], printMode, (message['at'] > newestAtMin ? true : false));
    //             })

    //             pageSearchFilter($('input#page-search').val());

    //             resolve('Logs printed.');
    //         }
    //     })

    //     return;
    // })
}

function printLogMessage(title = '', content = '', category = 'info', at = 0, at_readable = '', index = 0, printMode = 'append', animation = false) {
    let content_escaped = content.replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll(',', ',<wbr />');
    
    $log_item = $('.log-item#log-item-template').clone();
    $log_item.removeAttr('id');
    $log_item.find('.log-item-time').text(at_readable);
    $log_item.find('.log-item-title').text(title+': ');
    $log_item.find('.log-item-content').html(content_escaped);
    $log_item.attr('data-category', category).attr('data-at', at).attr('data-index', index);
    $log_item.attr('data-page-search', `${at_readable} ${title}: ${content}`);
    if(printMode == 'append') {
        $log_item.appendTo('.log-items');
    } else if(printMode == 'prepend') {
        $log_item.prependTo('.log-items');
    }

    if(animation) {
        $log_item.addClass('log-item-new');
        setTimeout(() => {
            $log_item.removeClass('log-item-new');
        }, 300);
    }

    return false;
}

function sortLogMessages(a, b) {
    return a['index'].toString().localeCompare(b['index'].toString());
}

function sortLogMessagesInverted(b, a) {
    return a['index'].toString().localeCompare(b['index'].toString());
}