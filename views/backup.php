<?php
/**
 * @var array $data
 */
?>
<br>
<span class="backup-pro-span" style="margin-left: 15px;">PRO</span>
<div class="wbcr-content-section wtitan-section-disabled">
    <div class="wt-row">
        <div class="col-sm-12">
            <p><?= __( 'Schedule:', 'wbcr-backup-master' ) ?></p>
            <ul class="schedule-buttons">
                <li>
                    <button data-action="scheduler" data-value="off" class="button primary">
                        <?= __( 'Manually', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
                <li>
                    <button data-action="scheduler" data-value="2h" class="button primary">
                        <?= __( 'Every 2 hours', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
                <li>
                    <button data-action="scheduler" data-value="8h" class="button primary">
                        <?= __( 'Every 8 hours', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
                <li>
                    <button data-action="scheduler" data-value="1d" class="button primary" disabled>
                        <?= __( 'Everyday', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-sm-12 old-archives">
                <input type="checkbox" id="remove_old_archive" disabled>
                <label for="remove_old_archive">
                    <?= __( 'Remove old archives (older than 7 days)', 'wbcr-backup-master' ) ?>
                </label>
            </div>
        </div>
    </div>

    <div class="wt-row">
        <div class="col-sm-12">
            <p><?php
                echo __( 'Archiving speed:', 'wbcr-backup-master' ) ?></p>
            <ul class="schedule-buttons">
                <li>
                    <button data-action="set_speed" data-value="1200" class="button primary">
                        <?= __( 'Slow', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
                <li>
                    <button data-action="set_speed" data-value="12000" class="button primary" disabled>
                        <?= __( 'Fast', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
            </ul>
            <label for="files_per_minute"><?= __( 'Custom speed:', 'wbcr-backup-master' ) ?></label>
            <input type="text" id="files_per_minute" value="12000" disabled>
            <?= __( 'files per minute', 'wbcr-backup-master' ) ?>
        </div>
        <div class="col-sm-12">
            <button id="save_archiving_speed" class="button primary backup">
                <?= __( 'Save', 'wbcr-backup-master' ) ?>
            </button>
        </div>
    </div>

    <div class="wt-row">
        <div class="col-sm-12">
            <p><?= __( 'Storage:', 'wbcr-backup-master' ) ?></p>
            <ul class="schedule-buttons" id="schedule-buttons">
                <li>
                    <button class="button primary store" data-action="store" data-store="local">
                        <?= __( 'Local', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
                <li>
                    <button class="button primary store" data-action="store" data-store="ftp">
                        <?= __( 'FTP', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
                <li>
                    <button class="button primary store" data-action="store" data-store="dropbox">
                        <?= __( 'Dropbox', 'wbcr-backup-master' ) ?>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="wt-row">
        <div class="col-sm-12">
            <button id="create_new_backup" class="button primary backup"
                    data-action-message="<?=__( 'Starting...', 'wbcr-backup-master' )?>">
                <?= __( 'Create new backup', 'wbcr-backup-master' ) ?>
            </button>
        </div>
    </div>

    <div class="wt-row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <td><?= __( 'Backup date', 'wbcr-backup-master' ) ?></td>
                    <td><?= __( 'Size', 'wbcr-backup-master' ) ?></td>
                    <td><?= __( 'Current storage', 'wbcr-backup-master' ) ?></td>
                    <td><?= __( 'Actions', 'wbcr-backup-master' ) ?></td>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
