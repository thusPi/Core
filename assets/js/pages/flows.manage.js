// thusPiAssign('flows', { setup() { console.log('Setup!'); this.$container = $('.flow-editor'); this.variableDraggableConfig = { helper: 'clone', revert: true, revertDuration: 0, appendTo: 'body', scroll: true, scrollSpeed: 15, start: function(e, ui) { const $helper = ui.helper; // If the draggable is not in a parameter, return if(!$helper.parent().hasClass('flow-component-parameter-wrapper')) { return; } const $parameterWrapper = $helper.parent(); // Change the attribute on the parameter wrapper $parameterWrapper.attr('data-flow-parameter-has-variable', 'false'); // Enable the droppable // $parameterWrapper.droppable('option', 'disabled', false); }, stop: function(e, ui) { const $helper = ui.helper; // If the draggable is not in a parameter, return if(!$helper.parent().hasClass('flow-component-parameter-wrapper')) { return; } const $parameterWrapper = $helper.parent(); // Change the attribute on the parameter wrapper $parameterWrapper.attr('data-flow-parameter-has-variable', 'true'); // Disable the droppable // $parameterWrapper.droppable('option', 'disabled', true); } } this.parameterWrapperDroppableConfig = { tolerance: 'pointer', greedy: true, drop: (e, ui) => { const $draggable        = ui.helper.clone(false); const $parameterWrapper = $(e.target); const parameterType     = $parameterWrapper.attr('data-flow-parameter-type'); const variableType      = $draggable.attr('data-flow-variable-type'); // Return false if the variable type and parameter don't match if(parameterType == 'string' && variableType != 'string' && variableType != 'number' && parameterType != variableType) { console.log('parametertype', parameterType, 'vartype', variableType); thusPi.message.send('type doesnt match'); return false; } // Return false if parameter already has a variable if($parameterWrapper.attr('data-flow-parameter-has-variable') == 'true') { return false; } // Remove draggable inline styles (from jQuery) $draggable.removeAttr('style'); // Make draggable draggable let variableDraggableConfig = {...this.variableDraggableConfig, helper: 'original'}; $draggable.draggable(variableDraggableConfig); // Remove the original helper // ui.helper.remove(); // Make all parameter wrappers droppable $parameterWrapper.siblings('.flow-component-parameter-wrapper').each((i, parameterWrapper) => { $(parameterWrapper).droppable(this.parameterWrapperDroppableConfig); console.log($(parameterWrapper).data('ui-droppable')); }) $parameterWrapper.attr('data-flow-parameter-has-variable', 'true'); $parameterWrapper.append($draggable); return true; } } this._setupComponents(); this._setupVariables(); this._setupInputs(); }, _setupInputs() { // Make all non-search inputs writable $('.flow-component-parameter:not([data-type="search"]):not(input)').each(function() { const $input = $(this); $input.attr('contenteditable', true); }) // Add results to all search inputs (including inputs of variables) $('.flow-component-parameter[data-type="search"]').each(function() { const $input = $(this); // Initialize the input since it is appended after page load $input.data('input', new thusPi.ui.input.search($input)); const valuesIndex = $input.attr('data-flow-input-values-index'); if(!isSet(valuesIndex)) { return true; } const values = thusPi.data.flows.values[valuesIndex]; if(!isSet(values)) { return true; } $input.data('input').addResults(values); }) }, _setupVariables() { $.each(thusPi.data.flows.variables, (groupId, variables) => { $.each(variables, (variableId, variable) => { const $variable        = thusPi.template.get('.flow-variable'); const $variableContent = $variable.find('.flow-variable-content'); // Set variable content $variableContent.html(variable.content); // Set variable icon $variable.find('.flow-variable-icon').html(thusPi.icon.create(variable.icon, 'xs')); // Set variable type $variable.attr('data-flow-variable-type', variable.type); // Setup parameters this._setupParameters(variable.parameters, $variableContent); // Make variable draggable $variable.draggable(this.variableDraggableConfig); $variable.appendTo(this.$container.find('.flow-variables-lol')); }) }) }, _setupParameters(parameters, $container) { let containerHTML = $container.html(); // Add wrappers $.each(parameters, function(parameterIndex, parameter) { // Replace the placeholder with a parameter wrapper containerHTML = containerHTML.replace('%'+parameterIndex, `<div class="flow-component-parameter-wrapper" data-flow-parameter-index="${parameterIndex}" data-flow-parameter-type="${parameter.type}"></div>`); }) // Update the container content $container.html(containerHTML); // Add the parameters $.each(parameters, (parameterIndex, parameter) => { // Add a parameter wrapper to the container const $parameterWrapper = $container.find(`.flow-component-parameter-wrapper[data-flow-parameter-index="${parameterIndex}"]`); let $parameter; if(isSet(parameter.values_index)) { $parameter = thusPi.template.get('.flow-component-parameter[data-type="search"]'); $parameter.attr('data-flow-input-values-index', parameter.values_index); } else { // Parameter is a normal input $parameter = thusPi.template.get('.flow-component-parameter:not([data-type])'); } // Make parameter wrapper droppable $parameterWrapper.droppable(this.parameterWrapperDroppableConfig) // Append the parameter to the wrapper $parameter.appendTo($parameterWrapper); }) return true; }, _setupComponents() { $.each(thusPi.data.flows.components, (i, component) => { const $component = thusPi.template.get('.flow-component'); const $componentContent = $component.find('> .flow-component-content'); // Set component title $component.find('.flow-component-title').text(component.title); // Set component content $componentContent.html(component.content); // Setup the parameters this._setupParameters(component.parameters, $componentContent); $component.appendTo(this.$container.find('.flow-components-lol')); }) } }) $(document).on('thuspi.ready', function() { if(thusPi.page.current() != 'flows/manage') { return; } thusPi.flows.setup(); })
thusPiAssign('flows.editor', {
    setup() {
        console.log('Setup!');
        this.blockly._setup();
        
        if(isSet(thusPi.data.flows.blocks)) {
            this.blockly._registerBlocks(thusPi.data.flows.blocks);
        }
    },

    blockly: {
        _setup() {
            this.workspace = Blockly.inject('thuspi-flow-editor', {
                toolbox: this._getToolbox(),
                renderer: this._getRenderer(),
                theme: this._getTheme(),
                trashcan: true
            }).addChangeListener(this._workspaceToCode());
        },

        _workspaceToCode() {
            const code = Blockly.JavaScript.workspaceToCode(this.workspace);
            console.log('PHP code:', code);
        },

        _getRenderer() {
            return 'thuspi_renderer';
        },

        _getTheme() {
            return {
                'base': Blockly.Themes.Classic,
                'componentStyles': {
                    'workspaceBackgroundColour': getCSSVariable('--secondary'),
                    'toolboxBackgroundColour': 'blue',
                    'toolboxForegroundColour': 'red',
                    'flyoutBackgroundColour': 'cyan',
                    'flyoutForegroundColour': 'green',
                    'flyoutOpacity': 1,
                    'scrollbarColour': 'orange',
                    'insertionMarkerColour': 'yellow',
                    'insertionMarkerOpacity': 0.3,
                    'scrollbarOpacity': 0.4,
                    'cursorColour': 'pink',
                },
            }
        },
        
        _getToolbox() {
            return {
                "contents": [
                    {
                        "kind": "category",
                        "name": "Core",
                        "contents": [
                            {"kind": "block", "type": "controls_if"},
                            {"kind": "block", "type": "logic_compare"},
                            {"kind": "block", "type": "devices_numeric_value"}
                        ]
                    }
                ]
            }
        },

        _registerBlocks(blocks) {
            let convertedBlocks = [];

            $.each(blocks, function(blockId, block) {
                block.id = blockId;
                const convertedBlock = thusPi.flows.editor.blockly.blockConverter._convert(block);
                
                if(convertedBlock === false) {
                    return true;
                }

                convertedBlocks.push(convertedBlock);
            })
            Blockly.common.defineBlocksWithJsonArray(convertedBlocks);
        },

        blockConverter: {
            _convert(block) {
                let convertedBlock = [];

                if(!isSet(block.id)) {
                    return false;
                }

                convertedBlock = {
                    type: block.id,
                    message: this._convertContent(block.content)
                }

                if(isSet(block.parameters)) {
                    $.each(block.parameters, function(i, parameter) {
                        const blockArg = thusPi.flows.editor.blockly.blockConverter._convertParameter(parameter);

                        if(blockArg === false) {
                            return true;
                        }

                        convertedBlock[`args${i}`] = blockArg;
                    })
                }
            },

            // Increments all placeholders (from %0, %1) by one (to %1, %2)
            _convertContent(blockContent) {

                // Return if there are no placeholders
                if(blockContent.indexOf('%') < 0) {
                    return blockContent;
                }

                const blockMessage = blockContent.replace(/(%\d+)+/g, function(match) {
                    return '%'+(parseInt(match.substring(1))+1);
                });

                return blockMessage;
            },

            _convertParameter(parameter) {
                let arg = {
                    name: parameter.name,
                    type: 'input_value',
                    check: this._convertParameterType(parameter?.type)
                }

                // Parameter is an option dropdown
                if(isSet(parameter.options_id)) {
                    arg.type = 'field_dropdown';
                    arg.options = [];

                    // Get available options
                    const options = thusPi.data.flows.options[parameter.options_id] || undefined;

                    if(!isSet(options)) {
                        return false;
                    }

                    $.each(options, function(i, option) {
                        if(!isSet(option.value)) {
                            return true;
                        }

                        arg.options.push([option.text || option.value, option.value]);
                    })
                }

                return arg;
            },

            _convertParameterType(type = null) {
                switch(type) {
                    case 'number':  return 'Number';
                    case 'boolean': return 'Boolean';
                    case 'array':   return 'Array';
                    case 'color':   return 'Colour';
                    default:        return 'String';
                }
            }
        }
    }
})

$(document).on('thuspi.ready', function(e, data) {
    if(data.page != 'flows/manage') {
        return;
    }

    thusPi.flows.editor.setup();

    console.log('page flows ready!');
})