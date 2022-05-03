<div class="input-search-wrapper">
    <input class="extension-search mb-2" data-type="search" onchange="thusPi.admin.extensions.print_list(event.target.value);">
</div>
<div class="btn-list extension-search-results"></div>

<a class="btn btn-tertiary bg-secondary extension extension-brief mb-2 template">
    <h3 class="tile-title">
        <span class="extension-name"></span>
        <i class="far fa-check-circle fa-sm text-success extension-verified" data-tooltip="<?php echo(\thusPi\Locale\translate('admin.extensions.verified.tooltip')); ?>"></i>
    </h3>
    <span class="extension-description tile-subtitle mb-1"></span>
    <div class="d-flex flex-row text-muted text-overflow-ellipsis">
        <span class="extension-owner-wrapper">
            <i class="far fa-user text-info pr-1"></i>
            <span class="extension-owner"></span>
        </span>
        <span class="px-1">|</span>
        <span class="extension-stars-wrapper">
            <i class="far fa-star text-warning pr-1"></i>
            <span class="extension-stars"></span>
        </span>
        <span class="px-1 d-none d-sm-inline">|</span>
        <span class="extension-issues-wrapper d-none d-sm-inline">
            <i class="far fa-dot-circle text-danger pr-1"></i>
            <span class="extension-issues"></span>
        </span>
        <span class="px-1">|</span>
        <span class="extension-pushed-ago-wrapper">
            <i class="far fa-code-commit text-success pr-1"></i>
            <span class="extension-pushed-ago"></span>
        </span>
    </div>
</a>