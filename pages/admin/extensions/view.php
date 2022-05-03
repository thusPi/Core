<?php 
    $extension = \thusPi\Extensions\get_from_server($_GET['id'] ?? null);

    if(!isset($extension)) {
        return;
    }
?>
<div 
    class="extension extension-detailed bg-secondary tile transition-fade" 
    data-verified="<?php echo(bool_to_str($extension['verified'])); ?>" 
    data-installed="<?php echo(bool_to_str($extension['installed'])); ?>"
    data-enabled="<?php echo(bool_to_str($extension['enabled'])); ?>">
    <div class="row">
        <div class="col-12 col-md">
            <h3 class="tile-title">
                <a class="extension-name text-default" href="<?php echo($extension['repository']['url']); ?>" target="_blank" class="text-default pl-1" rel="noopener noreferrer">
                    <?php echo($extension['name']); ?>
                </a>
                <i class="far fa-check-circle fa-sm text-success extension-verified" data-tooltip="<?php echo(\thusPi\Locale\translate('admin.extensions.verified.tooltip')); ?>"></i>
            </h3>
            <span class="extension-description tile-subtitle mb-2"><?php echo($extension['description']); ?></span>
            <div class="d-flex flex-row text-muted">
                <span class="extension-owner-wrapper">
                    <i class="far fa-user text-info"></i>
                    <span class="extension-owner px-1"><?php echo($extension['repository']['owner']['name']); ?></span>
                    <a href="<?php echo($extension['repository']['owner']['url']); ?>" target="_blank" class="text-muted" rel="noopener noreferrer">
                        <i class="far fa-external-link fa-sm"></i>
                    </a>
                </span>
                <span class="px-1">|</span>
                <span class="extension-stars-wrapper">
                    <i class="far fa-star text-warning"></i>
                    <span class="extension-stars px-1"><?php echo($extension['repository']['stars_count']); ?></span>
                    <a href="<?php echo($extension['repository']['url']); ?>/stargazers" target="_blank" class="text-muted" rel="noopener noreferrer">
                        <i class="far fa-external-link fa-sm"></i>
                    </a>
                </span>
                <span class="px-1">|</span>
                <span class="extension-issues-wrapper">
                    <i class="far fa-dot-circle text-danger"></i>
                    <span class="extension-issues px-1"><?php echo($extension['repository']['open_issues_count']); ?></span>
                    <a href="<?php echo($extension['repository']['url']); ?>/issues" target="_blank" class="text-muted" rel="noopener noreferrer">
                        <i class="far fa-external-link fa-sm"></i>
                    </a>
                </span>
                <span class="px-1">|</span>
                <span class="extension-pushed-ago-wrapper">
                    <i class="far fa-code-commit text-success"></i>
                    <span class="extension-pushed-ago px-1"><?php echo($extension['repository']['pushed_ago']); ?></span>
                    <a href="<?php echo($extension['repository']['url']); ?>" target="_blank" class="text-muted" rel="noopener noreferrer">
                        <i class="far fa-external-link fa-sm"></i>
                    </a>
                </span>
            </div>
        </div>
        <div class="mt-2 col-12 col-md-auto">
            <div class="d-flex flex-row flex-wrap h-100 align-items-end">
                <div class="btn btn-success bg-primary" data-extension-action="install" onclick="thusPi.admin.extensions.install('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.install')); ?></div>
                <div class="btn btn-danger bg-primary" data-extension-action="uninstall" onclick="thusPi.admin.extensions.uninstall('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.uninstall')); ?></div>
                <div class="btn btn-warning bg-primary" data-extension-action="disable" onclick="thusPi.admin.extensions.disable('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.disable')); ?></div>
                <div class="btn btn-success bg-primary" data-extension-action="enable" onclick="thusPi.admin.extensions.enable('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.enable')); ?></div>
            </div>
        </div>
    </div>
</div>