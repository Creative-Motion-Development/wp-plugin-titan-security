<?php
/**
 * @var bool    $scanner_started
 * @var Match[] $matched
 * @var float   $progress
 * @var int     $cleaned
 * @var int     $suspicious
 */

use WBCR\Titan\MalwareScanner\Match;

if($matched === false ) {
	?>
    <div class="wtitan-audit-empty-container">
		<?= sprintf(__('Click %1s to start scanning for malware','titan-security'), '<span class="btn btn-primary wt-nobutton">'.__('Start scan','titan-security').'</span>');?>
    </div>
	<?php

}
else if(!empty($matched)) {
	?>
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
				<?php if ( $match instanceof Match ): ?>
					<tr>
						<td><?php echo $match->getFile()->getPath( true ) ?></td>
						<td><?php echo $match->getSignature()->getType() == 'both' ? 'Server & browser' : $match->getSignature()->getType() ?></td>
						<td><?php echo htmlspecialchars( $match->getMatch() ); ?></td>
						<td></td>
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
}
else {
	?>
	<div class="wtitan-audit-empty-container">
		<?= __('No malware found','titan-security');?>
	</div>
	<?php

}
?>