thusPiAssign('template', {
    get: (selector) => {
        const $template = $(`${selector}.template`).first();

        if($template.length == 0) {
            return $('</div>');
        }

        return $template.clone().removeClass('template');
    }
})