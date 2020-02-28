<?php
namespace WBCR\Titan;


class PluginsVulnerabilities extends Vulnerabilities_API {

	/**
	 * @var string
	 */
	public $api_endpoint = "plugin";

	/**
	 * @var array
	 */
	public $vulnerabilities = array();

	/**
	 * PluginsVulnerabilities constructor.
	 *
	 */
	public function __construct() {
		parent::__construct();

		$plugins = get_plugins();
		$plugins_temp = array();
		foreach ( $plugins as $key => $plugin ) {
			$tmp = explode('/', $key);
			if( isset($tmp[0]) && count($tmp) >= 2) $slug = $tmp[0];
			else break;
			$plugins_temp[$slug] = $key;
			$params[$slug] = (string)$plugin['Version'];
		}
		$plugin_vulnes = $this->request( $params);

		if( !empty($plugin_vulnes) ) {
			foreach ( $plugin_vulnes as $key_p => $plugin ) {
				foreach ( $plugin as $key_v => $vulne ) {
					$plugin_vulnes[$key_p][$key_v]['plugin'] = isset($plugins_temp[$key_p]) ? $plugins_temp[$key_p] : '';
				}
			}
			$this->vulnerabilities = $plugin_vulnes;
		}
	}

	/**
	 * @return array
	 */
	public function getVulnerabilities() {
		return $this->vulnerabilities;
	}

	/**
	 * Render HTML for displaying a vulnerabilities
	 *
	 * @return string
	 */
	public function render_html_table() {
		$plugins_vulners = $this->getVulnerabilities();
		if(!empty( $plugins_vulners)) {
			?>
				<table class="table table-striped table-hover table-responsive" width="100%">
					<tbody>
					<tr class="wtitan-vulner-table-first-tr">
						<td class="wtitan-vulner-table-name">Name</td>
						<td class="wtitan-vulner-table-description">Description</td>
						<td class="wtitan-vulner-table-slim">Affected version</td>
						<td class="wtitan-vulner-table-slim">Safe version</td>
						<td class="wtitan-vulner-table-slim">Actions</td>
					</tr>
					<?php
					foreach ( $plugins_vulners as $plugin ) {
						foreach ( $plugin as $vulner ) {
							if ( empty( $vulner['description'] ) ) {
								$vulner['description'] = __( 'No description of the vulnerability', 'titan-security' );
							}
							?>
                            <tr>
                                <td><?php echo $vulner['name']; ?></td>
                                <td class="wtitan-vulner-table-description"><?php echo $vulner['description']; ?></td>
                                <td><?php echo $vulner['min_affected_version']; ?></td>
                                <td><?php echo $vulner['safe_version']; ?></td>
                                <td>
									<?php if ( ! empty( $vulner['safe_version'] ) ) : ?>
                                        <a href="<?php echo admin_url( "update-core.php" ) ?>" target="_blank"
                                           class="button button-primary" id="wtitan-update-plugin-button"
                                           data-plugin="<?php echo $vulner['plugin']; ?>"
                                           data-slug="<?php echo $vulner['slug']; ?>">
                                            Update
                                        </a>
                                        <span class="wtitan-spinner wtitan-hide"
                                              id="wtitan-update-spinner-<?php echo $vulner['slug']; ?>"></span>
                                        <span class="wtitan-icon-ok wtitan-hide"
                                              id="wtitan-icon-ok-<?php echo $vulner['slug']; ?>"></span>
									<?php endif; ?>
                                </td>
                            </tr>
						<?php }
					}?>
					</tbody>
				</table>

			<?php
		}
		else
		{
			?>
            <div class="wtitan-vulner-container">No vulnerabilities</div>
			<?php
		}
		return "";
	}

}