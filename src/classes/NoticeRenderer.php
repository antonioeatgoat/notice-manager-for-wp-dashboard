<?php

namespace Aeg\DashboardNotice;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * It takes care of the displaying of a single given notice on the dashboard page
 */
class NoticeRenderer {

	/**
	 * @var Notice
	 */
	private $notice;

	/**
	 * NoticeRenderer constructor.
	 *
	 * @param Notice $notice
	 * @param int           $priority
	 * @param string        $template
	 */
	public function __construct( Notice $notice, $priority = 10, $template = '' ) {
		$this->notice   = $notice;
		$this->priority = (int) $priority;
		$this->template = ( empty ( $template ) ) ? dirname( __FILE__ ) . '/../templates/notice.php' : (string) $template;
	}

	/**
	 * Hooks the notice message to WordPress
	 *
	 * @return bool
	 */
	public function render() {
		if ( $this->notice->is_dismissed() ) {
			return false;
		}

		add_action( 'admin_notices', array( $this, 'print_notice_html' ), $this->priority );

		return true;
	}

	/**
	 * Prints the HTML of ot notice message
	 */
	public function print_notice_html() {
		if ( ! $this->is_valid_template() ) {
			return;
		}

		$notice = $this->notice;

		include $this->template;
	}

	/**
	 * Checks if a template exists
	 *
	 * @return bool
	 */
	private function is_valid_template() {
		return ( file_exists( $this->template ) );
	}
}