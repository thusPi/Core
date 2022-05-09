thusPiAssign('icon', {
    fromElement($elem) {
        const icon  = $elem.attr('data-icon');
        const scale = $elem.attr('data-icon-scale');

        // Check if a library was specified
        if(icon.indexOf('.') === -1) {
            return false;
        }

        // Seperate library and icon name
        let [library, name] = icon.split('.');

        // Create resources for element, depending on icon library
        switch(library) {
            case 'far':
                $elem.addClass(`far fa-${name}`);
                break;

            case 'mdi':
                $elem.addClass(`mdi mdi-${name}`);
                break;

            case 'mi':
                $elem.text(name);
                break;

            default:
                return false;
        }

        // Set default classes
        $elem.addClass(`icon-scale-${scale} icon icon-library-${library}`);

        // Remove superfluos attributes
        $elem.removeAttr('data-icon').removeAttr('data-icon-scale');

        return true;
    },

    create(icon, scale = 'md') {
        const $elem = $(`<span data-icon="${icon}" data-icon-scale="${scale}"></span>`);

        this.fromElement($elem);

        return $elem.prop('outerHTML');
    }
})

$(document).on('thuspi.load', function() {
    $('[data-icon]').each(function() {
        thusPi.icon.fromElement($(this));
    })
})

$(window).on('load', function() {
    $('[data-icon]').each(function() {
        thusPi.icon.fromElement($(this));
    }) 
})