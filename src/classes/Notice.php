<?php

namespace Aeg\DashboardNotice;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The actual notice that will be displayed in the dashboard.
 */
class Notice {

	const STATUS_INFO = 'info';
	const STATUS_ERROR = 'error';
	const STATUS_SUCCESS = 'success';
	const STATUS_WARNING = 'warning';

	const DISMISS_NONE = 'none';
	const DISMISS_GLOBAL = 'global';
	const DISMISS_USER = 'user';

	/**
	 * @var array
	 */
	private $defaults = array(
			'title'          => '',
			'dismiss_text'   => '',
			'dismiss_mode'   => self::DISMISS_NONE,
			'show_close_btn' => false,
			'cta_text'       => '',
			'cta_href'       => '',
			'status'         => self::STATUS_INFO,
			'custom_class'   => ''
	);

	/**
	 * @var string A unique identifier for the notice message
	 */
	private $id;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * Notice constructor.
	 *
	 * @param string $id             An unique identified for the notice message
	 * @param string $message        The message of the notice.
	 * @param array $args {
	 *      Optional. An array of arguments.
	 *
	 *      @param string $title          The eventual title of the notice.
	 *      @param string $dismiss_mode   How an eventual dismiss button works [none|global|user].
	 *      @param string $dismiss_anchor The text of the dismiss button.
	 *      @param bool   $show_close_btn Show the button to close the notice (not permanent dismiss)
	 *      @param string $cta_href       The href attribute of an eventual CTA.
	 *      @param string $cta_anchor     The text of the CTA button.
	 *      @param string $status         The status of the notice [info|error|warning|success].
	 *      @param string $custom_class   A custom class to applu to the notice.
	 * }
	 */
	public function __construct( $id, $message, $args = array() ) {
		$this->id      = $id;
		$this->message = $message;

		$this->args = wp_parse_args( $args, $this->defaults );

		$this->args['dismiss_mode'] = $this->validate_dismiss_mode( $this->args['dismiss_mode'] );
		$this->args['status']       = $this->validate_status( $this->args['status'] );
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->args['title'];
	}

	/**
	 * @return string
	 */
	public function get_dismiss_mode() {
		return $this->args['dismiss_mode'];
	}

	/**
	 * @return string
	 */
	public function get_dismiss_text() {
		return $this->args['dismiss_text'];
	}

	/**
	 * @return string
	 */
	public function get_dismiss_url() {
		return esc_url( wp_nonce_url( add_query_arg( NoticesManager::DISMISS_QUERY_ARG, $this->get_id() ), NoticesManager::DISMISS_QUERY_ARG . '-' . get_current_user_id() ) );
	}

	/**
	 * @return bool
	 */
	public function get_show_close_btn() {
		return (bool) $this->args['show_close_btn'];
	}

	/**
	 * @return string
	 */
	public function get_cta_text() {
		return $this->args['cta_text'];
	}

	/**
	 * @return string
	 */
	public function get_cta_href() {
		return $this->args['cta_href'];
	}

	/**
	 * @return string
	 */
	public function get_status() {
		return $this->args['status'];
	}

	/**
	 * @return string
	 */
	public function get_custom_class() {
		return $this->args['custom_class'];
	}

	/**
	 * Dismisses the current notice, if it is dismissible, based on its params
	 *
	 * @return bool|int. True if the notice has been dismissed. False if it failed. 0 if the notice wasn't dismissible
	 */
	public function dismiss() {
		if ( self::DISMISS_NONE === $this->get_dismiss_mode() ) {
			return 0;
		}

		$dismissed_notices = NoticesManager::get_dismissed_options( $this->get_dismiss_mode() );
		$new_option        = array_merge( $dismissed_notices, array( $this->get_id() ) );

		if ( self::DISMISS_GLOBAL === $this->get_dismiss_mode() ) {
			return $this->dismiss_global_notice( $new_option );
		}

		// The only other possible case is self::DISMISS_USER
		return $this->dismiss_user_notice( $new_option );

	}

	/**
	 * @param array $new_option
	 *
	 * @return bool
	 */
	private function dismiss_global_notice( $new_option ) {
		return update_option( NoticesManager::DISMISSED_NOTICES_OPTION, $new_option, false );
	}

	/**
	 * @param array $new_option
	 *
	 * @return bool
	 */
	private function dismiss_user_notice( $new_option ) {
		return (bool) update_user_meta( get_current_user_id(), NoticesManager::DISMISSED_NOTICES_OPTION, $new_option );
	}

	/**
	 * Checks if a the notice can be shown to the current user
	 *
	 * @return bool
	 */
	public function is_dismissed() {
		$dismissed_notices = NoticesManager::get_dismissed_options( $this->get_dismiss_mode() );

		$is_dismissed = ( in_array( $this->get_id(), $dismissed_notices ) );

		return apply_filters( 'aeg_nm_is_notice_dismissed', $is_dismissed );
	}

	/**
	 * @param string $dismiss_mode
	 *
	 * @return string
	 */
	private function validate_dismiss_mode( $dismiss_mode ) {
		return ( in_array( $dismiss_mode, self::available_dismiss_modes() ) ) ? $dismiss_mode : self::DISMISS_NONE;
	}

	/**
	 * @param string $status
	 *
	 * @return string
	 */
	private function validate_status( $status ) {
		return ( in_array( $status, self::available_statuses() ) ) ? $status : self::STATUS_INFO;
	}

	/**
	 * @return array
	 */
	private static function available_statuses() {
		return array(
				self::STATUS_INFO,
				self::STATUS_ERROR,
				self::STATUS_SUCCESS,
				self::STATUS_WARNING
		);
	}

	/**
	 * @return array
	 */
	private static function available_dismiss_modes() {
		return array(
				self::DISMISS_NONE,
				self::DISMISS_GLOBAL,
				self::DISMISS_USER
		);
	}
}