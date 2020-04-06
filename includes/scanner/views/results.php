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
<div class="wbcr-titan-content">
	<table class="table">
		<thead>
		<tr>
			<th scope="col">Path</th>

			<th scope="col">Match</th>
			<th scope="col"></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $matched as $file_path => $match_array ): ?>
			<?php foreach ( $match_array as $match ): ?>
				<?php if ($match instanceof Match): ?>
					<tr>
						<td><?php echo $match->getFile()->getPath() ?></td>

						<td><?php echo htmlspecialchars( $match->getMatch()); ?></td>
						<td></td>
					</tr>
				<?php elseif ($match instanceof \WBCR\Titan\Client\Entity\CmsCheckItem): ?>
					<tr>
						<td><?php echo $match->path ?></td>

						<td><?php echo $match->action ?></td>
						<td></td>
					</tr>
				<?php else: ?>
					<tr>
						<td><?php echo $file_path ?></td>

						<td>null</td>
						<td></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
