<?php
namespace WBCR\Titan;


class WordpressVulnerabilities extends Vulnerabilities_API {

	/**
	 * @var string
	 */
	public $api_endpoint = "cms";

	/**
	 * @var array
	 */
	public $vulnerabilities = array();

	/**
	 * WordpressVulnerabilities constructor.
	 */
	public function __construct() {
		parent::__construct();

		global $wp_version;
		$params = array(
			'name' => 'wordpress',
			'version' => $wp_version
		);
		$this->vulnerabilities = $this->request( $params, 'GET');

	}

	/**
	 * Render HTML for displaying a vulnerabilities
	 *
	 * @return string
	 */
	public function render_html_table() {
		$wp_vulners = $this->getVulnerabilities();
		if(!empty( $wp_vulners)) {
			?>
                <table class="table table-striped table-hover table-responsive">
                    <tbody>
                    <tr class="wtitan-vulner-table-first-tr">
                        <td class="wtitan-vulner-table-description">Description</td>
                        <td class="wtitan-vulner-table-slim">Affected version</td>
                        <td class="wtitan-vulner-table-slim">Safe version</td>
                        <td class="wtitan-vulner-table-slim">Actions</td>
                    </tr>
					<?php foreach ( $wp_vulners as $vulner ) {
						if( empty( $vulner['description'] ) ) $vulner['description'] = __('No description of the vulnerability','titan-security');
					    if ( empty( $vulner['safe_version'] ) ) {
							continue;
						}
						?>
                        <tr>
                            <td class="wtitan-vulner-table-description"><?php echo $vulner['description']; ?></td>
                            <td><?php echo $vulner['min_affected_version']; ?></td>
                            <td><?php echo $vulner['safe_version']; ?></td>
                            <td>
								<?php if ( ! empty( $vulner['safe_version'] ) ) : ?>
                                    <a href="<?php echo admin_url( "update-core.php" ) ?>" target="_blank"
                                       class="button button-primary">Fix it</a>
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