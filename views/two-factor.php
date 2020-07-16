<?php
/**
 * @var array $data
 * @var WP_User[] $user_list
 */

$user_list = $data['user_list']
?><div class="wbcr-content-section wtitan-section-disabled">
    <div class="wt-row">
        <div class="col-md-12 wt-block-gutter important-block manual-block">
            <div class="row">
                <div class="col-md-4">
                    <div class="title">
                        <span class="step"><?php echo sprintf(__('Step %d', 'two-factor-auth'), 1) ?></span>
                        <span class="title"><?php echo __('Get the App', 'tfa') ?></span>
                    </div>
                    <div class="content">
                        <?php echo __('Download 2FA Auth app to your smartphone to start using tokens',
                            'two-factor-auth') ?>
                        <div class="store-links">
                            <a target="_blank"
                               href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                               class="store-link wt-get-it-on-google-play"></a>
                            <a target="_blank" href="https://apps.apple.com/ru/app/google-authenticator/id388497605"
                               class="store-link wt-download-on-appstore"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="title">
                        <span class="step"><?php echo sprintf(__('Step %d', 'tfa'), 2) ?></span>
                        <span class="title"><?php echo __('Scan QR code', 'two-factor-auth') ?></span>
                    </div>
                    <div class="content">
                        <?php echo __('Please, scan the following QR code with your app', 'tfa') ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="title">
                        <span class="step"><?php echo sprintf(__('Step %d', 'tfa'), 3) ?></span>
                        <span class="title"><?php echo __('Enter token', 'tfa') ?></span>
                    </div>
                    <div class="content">
                        <?php echo __('Enter the 6-digit token generated by the app and enable TOTP protection',
                            'tfa') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wt-row">
        <div class="col-md-12 wt-block-gutter wt-register-2fa">
            <div class="row">
                <div class="col-lg-2 col-sm-12 qr-block">
                    <img class="wtfa-qr-code" id="qr-code" src="https://chart.googleapis.com/chart?chs=200x200&chld=M%7C0&cht=qr&chl=Buy%20me%20pls" alt="">
                </div>
                <div class="col-lg-10 col-sm-12 qr-answer-block">
                    <div class="action-description">
                        <h4><?php echo __('Scan QR code', 'two-factor-auth') ?></h4>
                        <div class="text">
                            <p><?php echo __('Please, scan the following QR code with your app',
                                    'two-factor-auth') ?></p>
                        </div>
                        <div class="buttons">
                            <input type="text" id="code">
                            <button id="send-code" class="register_2fa_app button primary"
                                    data-process-message="<?php echo __('Sending...', 'two-factor-auth') ?>">
                                <?php echo __('Send auth code', 'two-factor-auth') ?>
                            </button>
                            <button id="qr-refresh" data-process-message="<?php echo __('Updating...', 'two-factor-auth') ?>">
                                <span class="dashicons dashicons-update"></span>
                                <?php echo __('Refresh', 'two-factor-auth') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ( current_user_can( 'list_users' ) ): ?>
        <div class="tw-row">
            <div class="col-md-12 wt-block-gutter">
                <div class="row">
                    <div class="col-md-12">
                        <p style="font-weight: bold;"><?php echo __('Users', 'two-factor-auth') ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><?php echo __('Username', 'two-factor-auth') ?></td>
                                <td><?php echo __('Two-Factor enabled?', 'two-factor-auth') ?></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $user_list as $u ): ?>
                                <tr>
                                    <td><?php echo $u->user_login ?></td>
                                    <td>No</td>
                                    <td>
                                        <button data-action="change-2fa-state" data-value="on"
                                                data-user-id="<?php echo $u->ID ?>"
                                                class="button"
                                                data-process-message="<?php echo __('Enabling...',
                                                    'two-factor-auth') ?>">
                                            <?php echo __('Enable', 'two-factor-auth') ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>