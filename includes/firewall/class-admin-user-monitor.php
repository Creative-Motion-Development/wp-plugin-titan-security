<?php

/**
 * The function of this class is to detect admin users created via direct access to the database (in other words, not
 * through WordPress).
 */
class wfAdminUserMonitor {

	public function isEnabled()
	{
		$options = wfScanner::shared()->scanOptions();
		$enabled = $options['scansEnabled_suspiciousAdminUsers'];
		if( $enabled && is_multisite() ) {
			if( !function_exists('wp_is_large_network') ) {
				require_once(ABSPATH . WPINC . '/ms-functions.php');
			}
			$enabled = !wp_is_large_network('sites') && !wp_is_large_network('users');
		}

		return $enabled;
	}

	/**
	 *
	 */
	public function createInitialList()
	{
		$admins = $this->getCurrentAdmins();
		wfConfig::set_ser('adminUserList', $admins);
	}

	/**
	 * @param int $userID
	 */
	public function grantSuperAdmin($userID = null)
	{
		if( $userID ) {
			$this->addAdmin($userID);
		}
	}

	/**
	 * @param int $userID
	 */
	public function revokeSuperAdmin($userID = null)
	{
		if( $userID ) {
			$this->removeAdmin($userID);
		}
	}

	/**
	 * @param int $ID
	 * @param mixed $role
	 * @param mixed $old_roles
	 */
	public function updateToUserRole($ID = null, $role = null, $old_roles = null)
	{
		$admins = $this->getLoggedAdmins();
		if( $role !== 'administrator' && array_key_exists($ID, $admins) ) {
			$this->removeAdmin($ID);
		} else if( $role === 'administrator' ) {
			$this->addAdmin($ID);
		}
	}

	/**
	 * @return array|bool
	 */
	public function checkNewAdmins()
	{
		$loggedAdmins = $this->getLoggedAdmins();
		$admins = $this->getCurrentAdmins();
		$suspiciousAdmins = array();
		foreach($admins as $adminID => $v) {
			if( !array_key_exists($adminID, $loggedAdmins) ) {
				$suspiciousAdmins[] = $adminID;
			}
		}

		return $suspiciousAdmins ? $suspiciousAdmins : false;
	}

	/**
	 * Checks if the supplied user ID is suspicious.
	 *
	 * @param int $userID
	 * @return bool
	 */
	public function isAdminUserLogged($userID)
	{
		$loggedAdmins = $this->getLoggedAdmins();

		return array_key_exists($userID, $loggedAdmins);
	}

	/**
	 * @return array
	 */
	public function getCurrentAdmins()
	{
		require_once(ABSPATH . WPINC . '/user.php');
		if( is_multisite() ) {
			if( function_exists("get_sites") ) {
				$sites = get_sites(array(
					'network_id' => null,
				));
			} else {
				$sites = wp_get_sites(array(
					'network_id' => null,
				));
			}
		} else {
			$sites = array(
				array(
					'blog_id' => get_current_blog_id(),
				)
			);
		}

		// not very efficient, but the WordPress API doesn't provide a good way to do this.
		$admins = array();
		foreach($sites as $siteRow) {
			$siteRowArray = (array)$siteRow;
			$user_query = new WP_User_Query(array(
				'blog_id' => $siteRowArray['blog_id'],
				'role' => 'administrator',
			));
			$users = $user_query->get_results();
			if( is_array($users) ) {
				/** @var WP_User $user */
				foreach($users as $user) {
					$admins[$user->ID] = 1;
				}
			}
		}

		// Add any super admins that aren't also admins on a network
		$superAdmins = get_super_admins();
		foreach($superAdmins as $userLogin) {
			$user = get_user_by('login', $userLogin);
			if( $user ) {
				$admins[$user->ID] = 1;
			}
		}

		return $admins;
	}

	public function getLoggedAdmins()
	{
		$loggedAdmins = wfConfig::get_ser('adminUserList', false);
		if( !is_array($loggedAdmins) ) {
			$this->createInitialList();
			$loggedAdmins = wfConfig::get_ser('adminUserList', false);
		}
		if( !is_array($loggedAdmins) ) {
			$loggedAdmins = array();
		}

		return $loggedAdmins;
	}

	/**
	 * @param int $userID
	 */
	public function addAdmin($userID)
	{
		$loggedAdmins = $this->getLoggedAdmins();
		if( !array_key_exists($userID, $loggedAdmins) ) {
			$loggedAdmins[$userID] = 1;
			wfConfig::set_ser('adminUserList', $loggedAdmins);
		}
	}

	/**
	 * @param int $userID
	 */
	public function removeAdmin($userID)
	{
		$loggedAdmins = $this->getLoggedAdmins();
		if( array_key_exists($userID, $loggedAdmins) && !array_key_exists($userID, $this->getCurrentAdmins()) ) {
			unset($loggedAdmins[$userID]);
			wfConfig::set_ser('adminUserList', $loggedAdmins);
		}
	}
}
