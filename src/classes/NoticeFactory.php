<?php

namespace Aeg\DashboardNotice;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NoticeFactory {

	/**
	 * Creates a Notice object, see the documentation of the object to have more details on the parameters
	 *
	 * @param string $id
	 * @param string $message
	 * @param array  $args
	 *
	 * @return Notice
	 */
	public function create( $id, $message, $args = array() ) {
		return new Notice( $id, $message, $args );
	}

}