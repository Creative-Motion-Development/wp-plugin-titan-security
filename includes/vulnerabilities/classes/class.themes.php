<?php
namespace WBCR\Titan;


class ThemesVulnerabilities extends Vulnerabilities_API {

	/**
	 * @var string
	 */
	public $api_endpoint = "theme";

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

		$themes = wp_get_themes();
		foreach ( $themes as $key => $theme ) {
		    if(empty($theme['Version'])) continue;
			$params[$key] = (string)$theme['Version'];
		}

		$theme_vulnes = $this->request( $params);
		if( !empty($theme_vulnes) )
		{
			foreach ( $theme_vulnes as $key_t => $theme_vulne ) {
				foreach ( $theme_vulne as $key_v => $vulne ) {
					$theme_vulnes[$key_t][$key_v] = $key_t;
				}
			}
			$this->vulnerabilities = $theme_vulne;
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
		$themes_vulners = $this->getVulnerabilities();
		if(!empty( $themes_vulners)) {
			?>
				<table class="table table-striped table-hover table-responsive">
					<tbody>
					<tr class="wtitan-vulner-table-first-tr">
						<td class="wtitan-vulner-table-name">Name</td>
						<td class="wtitan-vulner-table-description">Description</td>
						<td class="wtitan-vulner-table-slim">Affected version</td>
						<td class="wtitan-vulner-table-slim">Safe version</td>
						<td class="wtitan-vulner-table-slim">Actions</td>
					</tr>
					<?php foreach ( $themes_vulners as $key => $vulner ) {
						if( empty( $vulner['description'] ) ) $vulner['description'] = __('No description of the vulnerability','titan-security');
						?>
						<tr>
							<td><?php echo $vulner['name']; ?></td>
							<td class="wtitan-vulner-table-description"><?php echo $vulner['description']; ?></td>
							<td><?php echo $vulner['min_affected_version']; ?></td>
							<td><?php echo $vulner['safe_version']; ?></td>
							<td>
								<?php if ( ! empty( $vulner['safe_version'] ) ) : ?>
									<a href="<?php echo admin_url( "update-core.php" ) ?>" target="_blank"
									   class="button button-primary" id="wtitan-update-theme-button"
									   data-slug="<?php echo $vulner['slug']; ?>">
										Update
									</a>
                                    <span class="wtitan-spinner wtitan-hide" id="wtitan-update-spinner-<?php echo $vulner['slug']; ?>" ></span>
                                    <span class="wtitan-icon-ok wtitan-hide" id="wtitan-icon-ok-<?php echo $vulner['slug']; ?>" ></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php } ?>
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