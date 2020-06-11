<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var \WBCR\Titan\Views $this
 * @var array $data
 */
?>

<script type="text/html" id="wtitan-tmpl-<?php echo esc_attr( $data['id'] ); ?>">
    <h2 class="wtitan-modal__title">
		<?php echo $data['title']; ?>
    </h2>
    <div class="wtitan-modal__content">
		<?php echo $data['content']; ?>
    </div>
</script>
