<?php

namespace WBCR\Titan\Tweaks;

/**
 * Class Login_Interstitial_Base
 */
abstract class Login_Interstitial_Base {

	/**
	 * Should this interstitial be shown to the given user.
	 *
	 * @param \WP_User $user
	 * @param bool $is_requested
	 *
	 * @return bool
	 */
	public function show_to_user( \WP_User $user, $is_requested ) {
		return true;
	}

	/**
	 * Only show this interstitial if the user logged-in via wp-login.php.
	 *
	 * @param \WP_User $user
	 *
	 * @return bool
	 */
	public function show_on_wp_login_only( \WP_User $user ) {
		return false;
	}

	/**
	 * Render the interstitial.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param array $args
	 *
	 * @return void
	 */
	abstract public function render( Login_Interstitial_Session $session, array $args );

	/**
	 * Run code before any HTML it outputted for rendering an interstitial.
	 *
	 * @param Login_Interstitial_Session $session
	 */
	public function pre_render( Login_Interstitial_Session $session ) {
	}

	/**
	 * Must this interstitial be completed by the given user.
	 *
	 * @param Login_Interstitial_Session $session
	 *
	 * @return bool
	 */
	public function is_completion_forced( Login_Interstitial_Session $session ) {
		return true;
	}

	/**
	 * Is there a submit handler.
	 *
	 * @return bool
	 */
	public function has_submit() {
		return false;
	}

	/**
	 * Handle submitting the interstitial.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param array $data
	 *
	 * @return \WP_Error|null
	 */
	public function submit( Login_Interstitial_Session $session, array $data ) {
		_doing_it_wrong( __METHOD__, 'Must override ::submit if has submit handler.', '5.3.0' );

		return new \WP_Error( 'internal_server_error', 'Internal Server Error' );
	}

	/**
	 * Does the interstitial have async GET actions.
	 *
	 * @return bool
	 */
	public function has_async_action() {
		return false;
	}

	/**
	 * Handle an async action.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param string $action
	 * @param array $args
	 *
	 * @return true|array|\WP_Error|void
	 *      True if success.
	 *      Array if success with output customizations.
	 *      WP_Error if error.
	 *      Void/null if action not processed.
	 *      Or display custom HTML and die.
	 */
	public function handle_async_action( Login_Interstitial_Session $session, $action, array $args ) {
	}

	/**
	 * Does the interstitial have ajax handlers.
	 *
	 * @return bool
	 */
	public function has_ajax_handlers() {
		return false;
	}

	/**
	 * Handle an ajax request.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param array $data
	 */
	public function handle_ajax( Login_Interstitial_Session $session, array $data ) {
	}

	/**
	 * Get an info message to display above the interstitial form.
	 *
	 * @param Login_Interstitial_Session $session
	 *
	 * @return string
	 */
	public function get_info_message( Login_Interstitial_Session $session ) {
		return '';
	}

	/**
	 * Execute code after the interstitial has been submitted.
	 *
	 * @param Login_Interstitial_Session $session
	 * @param array $data
	 */
	public function after_submit( Login_Interstitial_Session $session, array $data ) {
	}

	/**
	 * Get the priority. A higher priority number is displayed later.
	 *
	 * @return int
	 */
	public function get_priority() {
		return 5;
	}
}
