<?php
/**
 * @var bool    $scanner_started
 * @var Match[] $matched
 * @var float   $progress
 * @var int     $cleaned
 * @var int     $suspicious
 */

use WBCR\Titan\MalwareScanner\Match;
?>
<div class="wbcr-content-section">
    <div class="wt-scanner-container wt-scanner-block-scan">
        <table>
            <tr>
                <td>
                    <h4><?php echo __('Malware scan','titan-security'); ?></h4>
                    <div class="wrio-statistic-buttons-wrap">
		                <?php if ( $scanner_started ): ?>
                            <button type="button" id="scan" data-action="stop_scan" class="wt-malware-scan-button">
                                <span class="text"><?php echo __( 'Stop scanning', 'titan-security' ) ?></span>
                            </button>
		                <?php else: ?>
                            <button type="button" id="scan" data-action="start_scan" class="wt-malware-scan-button">
				                <?php echo __( 'Scan', 'titan-security' ) ?>
                            </button>
		                <?php endif; ?>
                        <div class="wt-scan-icon-loader" data-status="" style="display: none"></div>
                    </div>
                </td>
                <td>
                    <h4><?php echo __('Description','titan-security'); ?></h4>
                    <p><?php echo __('Scanning all files of your site for malware. At each launch, site scanning starts from the beginning','titan-security'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <div class="wio-columns wio-page-statistic">
            <div>
                <div class="wio-chart-container wio-overview-chart-container">
                    <canvas id="wtitan-scan-chart" width="180" height="180"
                            data-cleaned="<?php echo $cleaned ?>" data-suspicious="<?php echo $suspicious ?>"
                            style="display: block;">
                    </canvas>
                    <div id="wt-total-percent-chart" class="wio-chart-percent">
                        <?php echo round( $progress, 1 ) ?><span>%</span>
                    </div>
                    <p class="wio-global-optim-phrase wio-clear">
                        Scanned <span class="wio-total-percent" id="wt-total-percent">
                                <?php echo round( $progress, 1 ) ?>%
                                </span>
                        of your website's files
                    </p>
                </div>
                <div style="margin-left:200px;">
                    <div id="wio-overview-chart-legend">
                        <ul class="wio-doughnut-legend">
                            <li>
                                <span style="background-color:#5d05b7"></span>
                                Cleaned -
                                <span class="wio-num" id="wtitan-cleaned-num"><?php echo $cleaned ?></span>
                            </li>
                            <li>
                                <span style="background-color:#f1b1b6"></span>
                                Suspicious -
                                <span class="wio-num" id="wtitan-suspicious-num"><?php echo $suspicious ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="wbcr-titan-content">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">Path</th>
                                <th scope="col">Type</th>
                                <th scope="col">Match</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $matched as $file_path => $match ): ?>
                               <?php if ($match instanceof Match): ?>
                                    <tr>
                                        <td><?php echo $match->getFile() ?></td>
                                        <td>Match</td>
                                        <td><?php echo $match->getMatch() ?></td>
                                        <td></td>
                                    </tr>
                                <?php elseif ($match instanceof \WBCR\Titan\Client\Entity\CmsCheckItem): ?>
                                    <tr>
                                        <td><?php echo $match->path ?></td>
                                        <td>Corrupted file</td>
                                        <td><?php echo $match->action ?></td>
                                        <td></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo $file_path ?></td>
                                        <td>null</td>
                                        <td>null</td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>

                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>