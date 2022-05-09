thusPiAssign('streams', {
    setup() {
        this.$container = $('.stream-editor');

        this._setupComponents();
        this._setupVariables();
        this._setupInputs();
    },

    _setupInputs() {
        // Setup all non-search inputs as droppables
        $('.stream-component-parameter:not([data-type="search"]):not(input)').each(function() {
            const $input = $(this);

            // Make parameter editable
            $input.attr('contenteditable', true);

            // Determine which variable type should be accepted by the input (string by default)
            let acceptTypes = $input.attr('data-stream-parameter-accept-type');
            acceptTypes = isSet(acceptTypes)
                ? acceptTypes.split(',')
                : 'string';
            
            acceptTypes = Array.isArray(acceptTypes)
                ? acceptTypes
                : [acceptTypes]

            // Value for 'accept' parameter
            const acceptValue = '.stream-variable[data-stream-variable-type="' + acceptTypes.join('"], .stream-variable[data-stream-variable-type="') + '"]';

            // Make input droppable
            $input.droppable({
                tolerance: 'touch',
                accept: acceptValue
            });
        })

        // Add results to all search inputs (including inputs of variables)
        $('.stream-component-parameter[data-type="search"]').each(function() {
            const $input = $(this);

            // Initialize the input since it is appended after page load
            $input.data('input', new thusPi.ui.input.search($input));

            const valuesIndex = $input.attr('data-stream-input-values-index');
            if(!isSet(valuesIndex)) {
                return true;
            }

            const values = thusPi.data.streams.values[valuesIndex];

            if(!isSet(values)) {
                return true;
            }

            $input.data('input').addResults(values);
        })
    },

    _setupVariables() {
        $.each(thusPi.data.streams.variables, (groupId, variables) => {
            $.each(variables, (variableId, variable) => {
                const $variable        = thusPi.template.get('.stream-variable');
                const $variableContent = $variable.find('.stream-variable-content');

                // Set variable content
                $variableContent.html(variable.content);

                // Set variable icon
                $variable.find('.stream-variable-icon').html(thusPi.icon.create(variable.icon));

                // Set variable group
                $variable.attr('data-stream-variable-group', groupId);

                $.each(variable.parameters, (parameterIndex, parameter) => {
                    // Add a parameter wrapper to the variable content
                    $variableContent.html($variableContent.html().replace('%'+parameterIndex, `<div class="stream-component-parameter-wrapper" data-stream-parameter-index="${parameterIndex}"></div>`));
                    const $parameterWrapper = $variableContent.find(`> .stream-component-parameter-wrapper[data-stream-parameter-index="${parameterIndex}"]`);

                    const $parameter = thusPi.template.get('.stream-component-parameter[data-type="search"]');
                    $parameter.attr('data-stream-input-values-index', parameter.values_index);
                    
                    // Append the parameter to the wrapper
                    $parameter.appendTo($parameterWrapper);
                })

                $variable.appendTo(this.$container.find('.stream-variables-lol'));
            })
        })
        // $('.stream-variable').each(function() {
        //     const $variable = $(this);
            
        //     $variable.draggable({
        //         helper: 'clone',
        //         revert: 'invalid',
        //         revertDuration: 0,
        //         appendTo: 'body',
        //         containment: '.stream-editor',
        //         scroll: false
        //     });
        // })
    },

    _setupComponents() {
        $.each(thusPi.data.streams.components, (i, component) => {
            const $component = thusPi.template.get('.stream-component');
            const $componentContent = $component.find('> .stream-component-content');

            // Set component title
            $component.find('.stream-component-title').text(component.title);

            // Set component content
            $componentContent.html(component.content);

            // Loop the parameters
            if(isSet(component.parameters)) {
                $.each(component.parameters, function(parameterIndex, parameter) {
                    // Add a parameter wrapper to the component content
                    $componentContent.html($componentContent.html().replace('%'+parameterIndex, `<div class="stream-component-parameter-wrapper" data-stream-parameter-index="${parameterIndex}"></div>`));
                    const $parameterWrapper = $componentContent.find(`> .stream-component-parameter-wrapper[data-stream-parameter-index="${parameterIndex}"]`);

                    let $parameter;
                    if(isSet(parameter.values_index)) {
                        // Parameter is a search input
                        $parameter = thusPi.template.get('.stream-component-parameter[data-type="search"]');
                        $parameter.attr('data-stream-input-values-index', parameter.values_index);
                    } else {
                        // Parameter is a normal input
                        $parameter = thusPi.template.get('.stream-component-parameter:not([data-type])');
                    }

                    // Append the parameter to the wrapper
                    $parameter.appendTo($parameterWrapper);
                })
            }

            $component.appendTo(this.$container.find('.stream-components-lol'));
        })
    }
})

$(document).on('thuspi.ready', function() {
    if(thusPi.page.current() != 'streams/manage') {
        return;
    }

    thusPi.streams.setup();
})