$(document).on('thusPi.search_value_change', '.stream-sub-category .input.stream-card-search', function() {
    // Show correct stream card
    let $input                        = $(this);
    let $stream_sub_category          = $input.parents('.stream-sub-category');
    let card                          = $input.getValue();
    let $cards_wrapper                = $stream_sub_category.find('.stream-cards');
    let $selected_card                = $cards_wrapper.find(`.stream-card[data-stream-card="${card}"]`);

    console.log(card);

    $cards_wrapper.find('.stream-card').removeClass('show');

    if($selected_card.length > 0) {
        $selected_card.addClass('show');
    } else {
        $cards_wrapper.find('#stream-card-empty').addClass('show');
    }

    $stream_sub_category.attr('data-set', true);
})

function addStreamSubCategory(category) {
    let $category = $(`.stream-category[data-category="${category}"]`);
    let $and_sub  = $category.find('.stream-sub-category[data-sub-category="and"]').first();

    if($and_sub.length <= 0) {
        return false;
    }

    if($and_sub.attr('data-show') == 'false') {
        // An additional category already exists, no need for cloning.
        $and_sub.attr('data-show', true);
    } else {
        // Clone an existing category.
        $and_sub = $and_sub.clone();
        $and_sub.attr({'data-set': false, 'data-show': true});
        $and_sub.find('.stream-card-content').find('.input[data-type="search"]').clearValue();
        $and_sub.find('.stream-card-content').find('input:not([data-type="search"])').val('');
        $and_sub.find('.stream-card-content').find('.input:not(input):not([data-type="search"])').text('');
        $and_sub.insertBefore($category.find('.category-actions-wrapper'));
        $and_sub.find('.input.stream-card-search').setSearchValue('no_stream_card_selected', '');
    }
}

function printStreamCategories(stream_id) {
    let url = `${c['webroot']}/api/get/stream-properties.php?stream_id=${stream_id}`;
    $.get(url, function(response) {
        try {
            response = JSON.parse(response);
            let properties = response['message'];

            printStreamCategory(properties['trigger'], 'trigger');
            printStreamCategory(properties['condition'], 'condition');
            printStreamCategory(properties['do'], 'do');
            printStreamCategory(properties['else'], 'else');
        } catch(err) {
            console.log(err)
        }
    })
}

function printStreamCategory(cards, category) {
    cards.forEach(function(card, i) {
        if(i == 0) {
            printStreamSubCategory(category, 'main', card);
        } else {
            printStreamSubCategory(category, 'and', card);
        }
    })
}

function printStreamSubCategory(category, sub_category, card) {
    let $category     = $(`.stream-category[data-category="${category}"]`);

    // Add new sub category
    if(sub_category == 'and') {
        addStreamSubCategory(category);
    }
    let $sub_category = $category.find(`.stream-sub-category[data-sub-category="${sub_category}"]`).last();

    $sub_category.find('.input.stream-card-search').setSearchValue(card['name']);
    let $stream_card = $sub_category.find('.stream-card.show');

    // Set search values
    $stream_card.find('.stream-card-content .search-wrapper .input').each(function() {
        let $input = $(this);
        let param  = $input.attr('data-param');
        console.log($input, param, card[param]);
        $input.setSearchValue(card[param]);
    })

    // Set string, integer, float etc. values
    $stream_card.find('.stream-card-content .input:not([data-type="search"])').each(function() {
        let $input = $(this);
        let param  = $input.attr('data-param');
        if($input.get(0).nodeName == 'INPUT') {
            $input.val(card[param]);
        } else {
            $input.text(card[param]);
        }
    })
}