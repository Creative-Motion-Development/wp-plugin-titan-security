<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var \WBCR\Titan\Views $this
 *
 * @var array $data
 */
?>
<style>
    .wt-container
    {
        margin: 15px 15px 15px 15px;
        vertical-align: middle;
    }

    .wt-block-nolicense
    {
        text-align: center;
        padding: 20px;
        vertical-align: middle;
        border: 1px solid #cccccc;
        background-color: white;
    }

    .wt-block-nolicense h1
    {
        margin: 0 !important;
    }

    .wbcr-content-section table
    {
        width: 100%;
        margin: 20px;
    }

    .wbcr-content-section table td
    {
        width: 50%;
        text-align: center;
    }

    .wbcr-content-section table td a
    {
        font-size: 18px !important;
        width: 200px;
        font-weight: 700 !important;
    }
</style>
<div class="wbcr-content-section">
    <div class="wt-container wt-block-nolicense">
        <h1><?php _e( 'Please activate your license to use this plugin feature.', 'titan-security' ) ?></h1>
    </div>
    <table>
        <tbody>
        <tr>
            <td>
                <a href="<?php echo esc_url(\WBCR\Titan\Plugin::app()->getPluginPageUrl( 'license' )); ?>"
                   class="btn btn-primary"><?php _e( 'Activate license', 'titan-security' ) ?></a>
            </td>
            <td>
                <a href="https://titansitescanner.com/pricing/"
                   class="btn btn-gold"><?php _e( 'Buy license', 'titan-security' ) ?></a>
            </td>
        </tr>
        </tbody>
    </table>
</div>