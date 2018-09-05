<?php

namespace Aeg\DashboardNotice;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * It lists all the existing notices and checks when notices have to be displayed or dismissed.
 */
final class NoticesManager {

	const DISMISS_QUERY_ARG = 'aeg-notice-manager-dismiss';

	const DISMISSED_NOTICES_OPTION = 'aeg-dismissed-notices';

	private $notices = array();

	/**
	 * @var NoticesManager|null
	 */
	private static $instance = null;

	/**
	 * @return NoticesManager
	 *
	 * @codeCoverageIgnore
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new NoticesManager();
		}

		return self::$instance;
	}

	/**
	 * Destroys the singleton instance
	 *
	 * @codeCoverageIgnore
	 */
	public static function destroy() {
		self::$instance = null;
	}

	/**
	 * NoticesManager constructor.
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		add_action( 'admin_head', array( $this, 'notices_dismiss_listener' ) );
		add_action( 'admin_head', array( $this, 'print_notices' ) );
	}

	/**
	 * @param Notice $notice
	 * @param int           $priority
	 * @param string        $template
	 */
	public function register_notice( Notice $notice, $priority = 10, $template = '' ) {
		$this->notices[ $notice->get_id() ] = [
				'notice'   => $notice,
				'priority' => $priority,
				'template' => $template
		];
	}

	/**
	 * Print all the notices that have to be printed
	 */
	public function print_notices() {
		foreach ( $this->notices as $notice_data ) {
			$rendered = new NoticeRenderer( $notice_data['notice'], $notice_data['priority'], $notice_data['template'] );
			$rendered->render();
		}
	}

	/**
	 * Register dismissal of admin notices.
	 *
	 * Acts on the dismiss link in the admin nag messages.
	 * If clicked, the admin notice disappears and will no longer be visible to this user.
	 *
	 * @return bool|int
	 */
	public function notices_dismiss_listener() {
		if ( ! isset( $_GET[ self::DISMISS_QUERY_ARG ] ) || ! check_admin_referer( self::DISMISS_QUERY_ARG . '-' . get_current_user_id() ) ) {
			return false;
		}

		$notice = $this->get_notice( $_GET[ self::DISMISS_QUERY_ARG ] );

		if ( ! $notice instanceof Notice ) {
			return false;
		}

		return $notice->dismiss();
	}

	/**
	 * @param string $notice_id
	 *
	 * @return null|Notice
	 */
	public function get_notice( $notice_id ) {
		if ( isset( $this->notices[ $notice_id ] ) ) {
			return $this->notices[ $notice_id ]['notice'];
		}

		return null;
	}

	/**
	 * Returns an array containing the dismissed notices ids, based on the dismiss mode passed and eventually the
	 * current user
	 *
	 * @param string $dismiss_mode
	 *
	 * @return array
	 */
	public static function get_dismissed_options( $dismiss_mode ) {
		$dismissed_notices = array();

		if ( Notice::DISMISS_GLOBAL === $dismiss_mode ) {
			$dismissed_notices = get_option( self::DISMISSED_NOTICES_OPTION );
		} else if ( Notice::DISMISS_USER === $dismiss_mode ) {
			$dismissed_notices = get_user_meta( get_current_user_id(), self::DISMISSED_NOTICES_OPTION, true );
		}

		return ( is_array( $dismissed_notices ) ) ? $dismissed_notices : array();
	}
}