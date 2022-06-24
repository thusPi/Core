CustomConstantsProvider = function() {
    // Set up all of the constants from the base provider.
    CustomConstantsProvider.superClass_.constructor.call(this);
    this.NOTCH_WIDTH = 20;
    this.NOTCH_HEIGHT = 10;
    this.CORNER_RADIUS = 100;
    this.TAB_HEIGHT = 8;
};

thusPiRenderer = function(name) {
  thusPiRenderer.superClass_.constructor.call(this, name);
};

thusPiRenderer.prototype.makeConstants_ = function() {
    return new CustomConstantsProvider();
};

Blockly.utils.object.inherits(thusPiRenderer, Blockly.blockRendering.Renderer);

Blockly.blockRendering.register('thuspi_renderer', thusPiRenderer);