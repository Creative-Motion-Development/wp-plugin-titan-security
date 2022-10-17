<?php
/**
 * @var bool $scanner_started
 * @var Result[] $matched
 * @var float $progress
 * @var int $cleaned
 * @var int $suspicious
 */

use WBCR\Titan\MalwareScanner\Result;

if ( $matched === false ) {
	?>
    <div class="wtitan-audit-empty-container">
		<?php echo sprintf( __( 'Click %1s to start scanning for malware', 'titan-security' ), '<span class="btn btn-primary wt-nobutton">' . __( 'Start scan', 'titan-security' ) . '</span>' ); ?>
    </div>
	<?php

} else if ( ! empty( $matched ) ) {
	?>
    <div class="wtitan-scanner-vulner-table-container wtitan-scanner-results">
        <table class="table table-striped table-hover table-responsive" width="100%">
            <thead>
            <tr>
                <td class="wtitan-vulner-table-severity"></td>
                <td class="wtitan-vulner-table-name" style="text-align: left;">Path</td>
                <td class="wtitan-vulner-table-slim">Type</td>
                <td class="wtitan-vulner-table-slim">Match</td>
            </tr>

            </thead>
            <tbody>
			<?php foreach ( $matched as $file_path => $match ): ?>
				<?php if ( $match instanceof Result ): ?>
                    <tr>
						<?php switch ( $match->getSignature()->getSever() ) {
							case \WBCR\Titan\MalwareScanner\Signature::SEVER_CRITICAL: ?>
                                <td class="wt-severity-high"></td>
								<?php break;

							case \WBCR\Titan\MalwareScanner\Signature::SEVERITY_SUSPICIOUS: ?>
                                <td class="wt-severity-medium"></td>
								<?php break;

							case \WBCR\Titan\MalwareScanner\Signature::SEVER_WARNING: ?>
                                <td class="wt-severity-medium"></td>
								<?php break;

							case \WBCR\Titan\MalwareScanner\Signature::SEVER_INTO: ?>
                                <td class="wt-severity-low"></td>
								<?php break;
						} ?>
                        <td style="text-align: left;"><?php echo $match->getFile()->getPath( true ) ?></td>
                        <td><?php echo $match->getSignature()->getTitle() ?></td>
                        <td><code><?php echo htmlspecialchars( $match->getMatch() ); ?></code></td>
                    </tr>
				<?php elseif ( $match instanceof \WBCR\Titan\Client\Entity\CmsCheckItem ): ?>
                    <tr>
                        <td><?php echo $match->path ?></td>
                        <td>Corrupted file</td>
                        <td><?php echo $match->action ?></td>
                        <td></td>
                    </tr>
				<?php else: ?>
                    <tr>
                        <td><?php echo $file_path ?></td>
                        <td><?php var_dump( $match ) ?></td>
                        <td>null</td>
                        <td></td>
                    </tr>
				<?php endif; ?>
			<?php endforeach; ?>
            </tbody>
        </table>
    </div>
	<?php
} else {
	?>
    <div class="wtitan-audit-empty-container">
		<?php _e( 'No malware found', 'titan-security' ); ?>
    </div>
	<?php

}
?>