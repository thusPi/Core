$(document).on('input', 'input#page-search', function() {
    let $input = $(this);
    pageSearchFilter($input.val());
})

function pageSearchFilter(term) {
    term = term.toLowerCase();

    let results = $('[data-page-search]');
    results.each(function(i, result) {
        result = $(result);
        let match = result.attr('data-page-search').toLowerCase();
        let matches = match.includes(term);
        
        if(matches) {
            result.show();
        } else {
            result.hide()
        }
    })
}