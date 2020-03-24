<?php
/* @var WBCR\Titan\Plugin $this Plugin
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
	.wt-block-nolicense h3
	{
		margin: 0 !important;
        font-size: 20px;
        font-weight: 700 !important;
	}
	.wbcr-content-section table
	{
		width: 100%;
		margin: auto;
	}
	.wbcr-content-section table td
	{
		width: 25%;
		text-align: center;
        padding: 20px;
	}
	.wbcr-content-section table td a
	{
		font-size: 18px !important;
		width: 200px;
        height: 35px;
		font-weight: 700 !important;
	}
</style>
<div class="wbcr-content-section">
	<div class="wt-container wt-block-nolicense">
        <h3><?php echo __('Please activate your license to use this plugin feature.', 'titan-security') ?></h3>
        <table>
            <tbody>
            <tr>
                <td>&nbsp;</td>
                <td><a href="<?php echo $this->plugin->getPluginPageUrl( 'license'); ?>" class="btn btn-primary"><?php echo __('Activate license', 'titan-security') ?></a></td>
                <td><a href="https://titansitescanner.com/pricing/" class="btn btn-gold"><?php echo __('Buy license', 'titan-security') ?></a></td>
                <td>&nbsp;</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>