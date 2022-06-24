var requestingLogs = false;

document.addEventListener('scroll', function (e) {
    if(thusPi.page.current() != 'admin/log/main') {
        return;
    }

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
}, true);

thusPiAssign('admin.logs', {
    async fetch(top, minimumTime = 0) {
        const logs = await thusPi.api.call('logs-get', {top: top, min_time: minimumTime});

        // Sort logs by timestamp
        logs.data.sort(function(a, b) {
            return a.at < b.at ? 1 : -1;
        })
        
        $.each(logs.data, function(i, message) {
            printLogMessage(message);
        })
    }
})

$(document).on('thuspi.ready', function() {
    if(thusPi.page.current() != 'admin/log/main') {
        return;
    }

    thusPi.admin.logs.fetch(25);

    // let categories_inactive = getInactiveLogCategories();
    // $('.log-items').attr('data-hide-categories', categories_inactive.join(','));
})

function toggleLogCategory(category) {
    setTimeout(function() {
        let categories_inactive = getInactiveLogCategories();
        $('.log-items').attr('data-hide-categories', categories_inactive.join(','));
        
        return saveState('log_categories_inactive', categories_inactive.join(','));
    }, 1);
}

// $(document).on('thuspi.ready', function() {
//     if(thusPi.page.current() != 'admin/log/main') {
//         return;
//     }

//     requestLogs(25, 0);

//     let categories_inactive = getInactiveLogCategories();
//     $('.log-items').attr('data-hide-categories', categories_inactive.join(','));
// })

function getInactiveLogCategories() {
    let categories_inactive = [];

    let $btns_inactive = $('.log-items-category-filter .btn:not(.active)');
    $btns_inactive.each(function() {
        categories_inactive.push($(this).attr('data-category'));
    })

    return categories_inactive;
}

function requestMoreLogs(top = 25, minTime = 0) {
    let $last_log_item = $('.log-item').last();
    let max = parseInt($last_log_item.attr('data-index')) - 1;
    if(max >= 0) {
        requestLogs(max - amount, max);
    }
}

async function refreshLogs(top = 25) {
    console.log(`Refreshing ${top} newest logs.`);
    top      = $('.log-item:not(#log-item-template)').slice(0, amount);
    let minTime  = parseInt($('.log-item:not(#log-item-template)').first().attr('data-at'));
    
    if(newestAtMin > 0) {     
        const requestLogsStatus = requestLogs(top, minTime, 'prepend');

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
    
        setTimeout(function() {
            refreshLogsLoop();
        }, 1500);
    }
}

function requestLogs(top = 25, minTime = 0, printMode = 'prepend') {
    requestingLogs = true;
    thusPi.api.call('logs-get', {'top': top, 'min_time': minTime}).then(function(response) {
        console.log(response);
    }).catch(function() {
        console.error('Something went wrong while getting logs.');
    })
    // return new Promise(function(resolve, reject) {
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

function printLogMessage({at_readable, content, group, title}) {
    const $container = $('.log-items');
    const $logItem   = thusPi.template.get('.log-item');
    const color      = $(`.btn[data-group="${group}"][data-color]`).attr('data-color') ?? '#000000';

    $logItem.find('.log-item-time').text(at_readable);
    $logItem.find('.log-item-title').text(title);
    $logItem.find('.log-item-content').text(content+'.');
    $logItem.attr({
        'data-group':        group,
        'data-page-search': `${at_readable} ${title}: ${content}.`,
        'style':             `--color: ${color}`
    });

    $logItem.appendTo($container);

    // if(animation) {
    //     $log_item.addClass('log-item-new');
    //     setTimeout(function() {
    //         $log_item.removeClass('log-item-new');
    //     }, 300);
    // }

    return true;
}

function sortLogMessages(a, b) {
    return a['index'].toString().localeCompare(b['index'].toString());
}

function sortLogMessagesInverted(b, a) {
    return a['index'].toString().localeCompare(b['index'].toString());
}