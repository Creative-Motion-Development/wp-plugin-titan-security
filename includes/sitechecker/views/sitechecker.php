<?php
/* @var array|string|int|float|bool|object $args data
 * @var string $template_name template username
 * @var bool $is_premium
 */
$disabled = $is_premium ? '' : 'disabled';
$pro_class = $is_premium ? '' : 'wt-element-pro';
?>
<div class="wbcr-content-section">
    <!-- ############################### -->
    <div class="wtitan-sitechecker-container wt-sitechecker-block">
        <table>
            <tbody>
            <tr>
                <td>
                    <div class="<?php echo $pro_class; ?>">
                        <button class="btn btn-primary btn-lg wt-sitechecker-button-subscribe"
                                id="subscribe" <?php echo $disabled; ?>>
                            Subscribe
                        </button>
                    </div>
                    <button class="btn btn-secondary btn-lg wt-sitechecker-button-subscribe" style="display: none;"
                            id="unsubscribe">Unsubscribe
                    </button>
                </td>
                <td>
                    <h4>Push notifications in the browser</h4>
                    <p>
                        Subscribing to push notifications in the browser allows you to find out about problems
                        with URLs access in real time.<br>
                        Your browser will receive push notifications if one of the URLS is unavailable.
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- ############################### -->
    <div class="wbcr-factory-page-group-header" style="margin:0">
        <strong>Site Checker</strong>
        <p>Here you can view information about checked URLs and manage them: add, delete</p>
    </div>
    <div class="wtitan-sitechecker-table-container">
		<?php if ( ! $this->plugin->is_premium() ) {
			?>
            <div class="wtitan-sitechecker-pro-container"><?php
			$this->plugin->view->print_template( 'pro-version' );
			?></div><?php
		} else {
			?>
            <table class="table table-striped table-hover table-responsive">
                <tbody>
                <tr class="wtitan-sitechecker-table-first-tr">
                    <td class="wtitan-sitechecker-table-description">URL</td>
                    <td class="wtitan-sitechecker-table-slim">Frequency</td>
                    <td class="wtitan-sitechecker-table-slim">Uptime</td>
                    <td class="wtitan-sitechecker-table-slim">Response time</td>
                    <td class="wtitan-sitechecker-table-slim">Next check</td>
                    <td class="wtitan-sitechecker-table-slim">Actions</td>
                </tr>
				<?php foreach ( $args['urls'] as $url ) { ?>
                    <tr>
                        <td class="wtitan-sitechecker-table-url"><a href="<?php echo $url->url; ?>"
                                                                    target="_blank"><?php echo $url->url; ?></td>
                        <td><?php echo $url->frequency; ?> сек</td>
                        <td><?php echo round( $url->uptime, 1 ); ?>%</td>
                        <td><?php echo round( $url->avg_request_time, 1 ); ?> сек</td>
                        <td><?php echo date( 'd.m.Y H:i', correct_timezone( $url->next_check ) ) ?></td>
                        <td>
                            <button class="btn wt-sitechecker-button-delete" data-id="<?php echo $url->id; ?>">&nbsp;
                            </button>
                            <span class="wt-spinner" id="wt-spinner" style="display: none;"></span>
                        </td>
                    </tr>
				<?php } ?>
                </tbody>
            </table>
			<?php
		}
		?>
    </div>
    <div class="wt-sitechecker-form-add <?php echo $pro_class; ?>">
        <label for="wt-sitechecker-url">Add URL to Site Checker</label>
        <input type="text" name="wt-sitechecker-url" id="wt-sitechecker-url" class="form-control"
               placeholder="URL" <?php echo $disabled; ?>>
        <button class="btn btn-primary wt-sitechecker-button-add"<?php echo $disabled; ?>>ADD</button>
        <span class="wt-spinner" id="wt-spinner" style="display: none;"></span>
    </div>
    <!-- ############################### -->
</div>