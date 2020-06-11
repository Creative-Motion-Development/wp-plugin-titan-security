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
<ul class="wtitan-ips-blocking-modal__tabs">
    <li class="wtitan-ips-blocking-modal__tab wtitan-ips-blocking-modal__tab--active">
        <a href="#ip-address"><?php _e( 'Address', 'titan-security' ) ?></a>
    </li>
    <li class="wtitan-ips-blocking-modal__tab">
        <a href="#custom-pattern"><?php _e( 'Custom Pattern', 'titan-security' ) ?></a>
    </li>
</ul>
<div class="wtitan-ips-blocking-modal__form">
    <div id="wtitan-ips-blocking-modal__ip-address-tab-content"
         class="wtitan-ips-blocking-modal__tab-content wtitan-ips-blocking-modal__tab-content--active">
        <p>
            <label class="wtitan-ips-blocking-modal__form-label" for="wtitan-ips-blocking-modal__form-ip-field">
				<?php _e( 'Ip Address', 'titan-security' ) ?>:
            </label>
            <input type="text" id="wtitan-ips-blocking-modal__form-ip-field" placeholder="192.168.200.200">
        </p>
    </div>
    <div id="wtitan-ips-blocking-modal__custom-pattern-tab-content" class="wtitan-ips-blocking-modal__tab-content">
        <p>
            <label class="wtitan-ips-blocking-modal__form-label" for="wtitan-ips-blocking-modal__form-range-ip-field">
				<?php _e( 'IP Address Range', 'titan-security' ) ?>:
            </label>
            <input type="text" id="wtitan-ips-blocking-modal__form-range-ip-field"
                   placeholder="e.g., 192.168.200.200 - 192.168.200.220 or 192.168.200.0/24">
        </p>
        <p>
            <label class="wtitan-ips-blocking-modal__form-label" for="wtitan-ips-blocking-modal__form-hostname-field">
				<?php _e( 'Hostname', 'titan-security' ) ?>:
            </label>
            <input id="wtitan-ips-blocking-modal__form-hostname-field" type="text"
                   placeholder="e.g., *.amazonaws.com or *.linode.com">
        </p>
        <p>
            <label class="wtitan-ips-blocking-modal__form-label" for="wtitan-ips-blocking-modal__form-user-agent-field">
				<?php _e( 'User agent', 'titan-security' ) ?>:
            </label>
            <input id="wtitan-ips-blocking-modal__form-user-agent-field" type="text"
                   placeholder="e.g., *badRobot*, *MSIE*, or *browserSuffix">
        </p>
        <p>
            <label class="wtitan-ips-blocking-modal__form-label" for="wtitan-ips-blocking-modal__form-referrer-field">
				<?php _e( 'Referrer', 'titan-security' ) ?>:
            </label>
            <input id="wtitan-ips-blocking-modal__form-referrer-field" type="text"
                   placeholder="e.g., *badwebsite.example.com*">
        </p>
    </div>
    <p>
        <label class="wtitan-ips-blocking-modal__form-label" for="wtitan-ips-blocking-modal__form-reason-field">
			<?php _e( 'Reason', 'titan-security' ) ?>:
        </label>
        <textarea id="wtitan-ips-blocking-modal__form-reason-field" placeholder="Bad bot" maxlength="250"></textarea>
    </p>
</div>

