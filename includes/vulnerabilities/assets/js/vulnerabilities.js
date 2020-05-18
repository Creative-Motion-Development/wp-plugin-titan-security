jQuery(document).ready(function ($) {
    //SHOW PLUGINS
    function ajax_load_vulners(target) {
        var wtitan_target = jQuery(".wtitan-vulner-table-container.wtitan-" + target);
        wtitan_target.addClass('wtitan-vulner-loader');
        jQuery.post(ajaxurl, {
            action: 'wtitan_get_vulners',
            target: target,
            _ajax_nonce: wtvulner.nonce,
        }).done(function (result) {
            wtitan_target.removeClass('wtitan-vulner-loader');
            wtitan_target.html(result);
        });
    }

    ajax_load_vulners('plugin');
    ajax_load_vulners('theme');
    ajax_load_vulners('wp');
    // end SHOW PLUGINS

    //UPDATE PLUGIN BUTTON
    jQuery('.wtitan-vulner-table-container').on('click', '#wtitan-update-plugin-button', function (e) {
        e.preventDefault();
        var btn = $(this);
        var spiner = btn.next('#wtitan-update-spinner-' + btn.data('slug'));
        var iconOk = btn.next('#wtitan-icon-ok-' + btn.data('slug'));

        btn.addClass('wtitan-hide');
        spiner.removeClass('wtitan-hide');
        btn.parents('tr').css("opacity", "0.5");
        jQuery.post(ajaxurl, {
            action: 'update-plugin',
            plugin: $(this).data('plugin'),
            slug: $(this).data('slug'),
            _ajax_nonce: wtvulner.nonce,
        }).done(function (result) {
            console.log(result);
            if (result.success) {
                iconOk.removeClass('wtitan-hide');
            } else {
                //btn.removeClass('wtitan-hide');
                btn.parents('tr').css("opacity", "1");
                btn.before('Plugin update failed. Try to update manually');
            }

            spiner.addClass('wtitan-hide');
        });

    });
    //UPDATE THEME BUTTON
    jQuery('.wtitan-vulner-table-container').on('click', '#wtitan-update-theme-button', function (e) {
        e.preventDefault();
        var btn = $(this);
        var spiner = btn.next('#wtitan-update-spinner-' + btn.data('slug'));
        var iconOk = btn.next('#wtitan-icon-ok-' + btn.data('slug'));

        btn.addClass('wtitan-hide');
        spiner.removeClass('wtitan-hide');
        btn.parents('tr').css("opacity", "0.5");
        jQuery.post(ajaxurl, {
            action: 'update-theme',
            slug: $(this).data('slug'),
            _ajax_nonce: wtvulner.nonce,
        }).done(function (result) {
            console.log(result);
            if (result.success) {
                iconOk.removeClass('wtitan-hide');
            } else {
                //btn.removeClass('wtitan-hide');
                btn.parents('tr').css("opacity", "1");
                btn.before('Theme update failed. Try to update manually');
            }

            spiner.addClass('wtitan-hide');
        });

    });
});