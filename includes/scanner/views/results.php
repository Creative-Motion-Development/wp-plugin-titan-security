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
