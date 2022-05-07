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
    <div class="tile-content row">
        <div class="col-12 col-md">
            <h3 class="tile-title">
                <a class="extension-name text-default" href="<?php echo($extension['repository']['url']); ?>" target="_blank" class="text-default pl-1" rel="noopener noreferrer">
                    <?php echo($extension['name']); ?>
                </a>
                <i class="far fa-check-circle fa-sm text-green extension-verified" data-tooltip="<?php echo(\thusPi\Locale\translate('admin.extensions.verified.tooltip')); ?>"></i>
            </h3>
            <span class="extension-description tile-subtitle mb-1"><?php echo($extension['description']); ?></span>
            <span class="d-flex flex-row tile-subtitle">
                <span class="extension-owner-wrapper">
                    <i class="far fa-user text-blue"></i>
                    <a class="extension-owner px-1 text-muted" href="<?php echo($extension['repository']['owner']['url']); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo($extension['repository']['owner']['name']); ?>
                    </a>
                </span>
                <span class="px-1">|</span>
                <span class="extension-stars-wrapper">
                    <i class="far fa-star text-yellow"></i>
                    <a class="extension-stars px-1 text-muted" href="<?php echo($extension['repository']['url']); ?>/stargazers" target="_blank" rel="noopener noreferrer">
                        <?php echo($extension['repository']['stars_count']); ?>
                    </a>
                </span>
                <span class="px-1">|</span>
                <span class="extension-issues-wrapper">
                    <i class="far fa-dot-circle text-red"></i>
                    <a class="extension-issues px-1 text-muted" href="<?php echo($extension['repository']['url']); ?>/issues" target="_blank" rel="noopener noreferrer">
                        <?php echo($extension['repository']['open_issues_count']); ?>
                    </a>
                </span>
                <span class="px-1">|</span>
                <span class="extension-pushed-ago-wrapper">
                    <i class="far fa-code-commit text-green"></i>
                    <a class="extension-pushed-ago px-1 text-muted" href="<?php echo($extension['repository']['url']); ?>" target="_blank" rel="noopener noreferrer">
                        <?php echo($extension['repository']['pushed_ago']); ?>
                    </a>
                </span>
            </span>
        </div>
        <div class="mt-2 col-12 col-md-auto">
            <div class="d-flex flex-row flex-wrap h-100 align-items-end">
                <div class="btn btn-green bg-primary" data-extension-action="install" onclick="thusPi.admin.extensions.install('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.install')); ?></div>
                <div class="btn btn-red bg-primary" data-extension-action="uninstall" onclick="thusPi.admin.extensions.uninstall('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.uninstall')); ?></div>
                <div class="btn btn-yellow bg-primary" data-extension-action="disable" onclick="thusPi.admin.extensions.disable('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.disable')); ?></div>
                <div class="btn btn-green bg-primary" data-extension-action="enable" onclick="thusPi.admin.extensions.enable('<?php echo($extension['id']); ?>');"><?php echo(\thusPi\Locale\translate('generic.action.enable')); ?></div>
            </div>
        </div>
    </div>
</div>