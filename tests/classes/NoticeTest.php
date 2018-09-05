<?php

use Aeg\DashboardNotice\Notice;
use Aeg\DashboardNotice\NoticeFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The actual notice that will be displayed in the dashboard.
 */
class NoticeTest extends aeg_NM_UnitTestCase {

	private $defaults = array(
			'title'          => '',
			'dismiss_text'   => '',
			'dismiss_mode'   => Notice::DISMISS_NONE,
			'show_close_btn' => false,
			'cta_text'     => '',
			'cta_href'       => '',
			'status'         => Notice::STATUS_INFO,
			'custom_class'   => ''
	);

	public function setUp() {
		parent::setUp();

		\WP_Mock::userFunction( 'wp_parse_args', array(
				'return' => function ( $args ) {
					return array_merge( $this->defaults, $args );
				}
		) );
	}

	public function test_get_id() {
		$expected = 'notice-test';
		$test     = ( new NoticeFactory() )->create( $expected, 'Hello World' );

		$this->assertEquals( $expected, $test->get_id() );
	}

	public function test_get_message() {
		$expected = 'Hello World';
		$test     = ( new NoticeFactory() )->create( 'notice-test', $expected );

		$this->assertEquals( $expected, $test->get_message() );
	}

	public function test_get_title() {
		$expected = 'This is a title';
		$test     = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'title' => $expected ) );

		$this->assertEquals( $expected, $test->get_title() );
	}

	public function test_get_dismiss_text() {
		$expected = 'This is a dismiss anchor';
		$test     = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'dismiss_text' => $expected ) );

		$this->assertEquals( $expected, $test->get_dismiss_text() );
	}

	public function test_get_show_close_btn() {
		$test = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'show_close_btn' => true ) );
		$this->assertTrue( $test->get_show_close_btn() );
	}

	public function test_get_cta_text() {
		$expected = 'This is a CTA';
		$test     = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'cta_text' => $expected ) );

		$this->assertEquals( $expected, $test->get_cta_text() );
	}

	public function test_get_cta_href() {
		$expected = '#';
		$test     = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'cta_href' => $expected ) );

		$this->assertEquals( $expected, $test->get_cta_href() );
	}

	public function test_get_custom_class() {
		$expected = 'custom-class';
		$test     = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'custom_class' => $expected ) );

		$this->assertEquals( $expected, $test->get_custom_class() );
	}

	/**
	 * @dataProvider provider_get_dismiss_mode
	 *
	 * @param string $mode
	 * @param string $expected
	 */
	public function test_get_dismiss_mode( $mode, $expected ) {
		$test = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'dismiss_mode' => $mode ) );

		$this->assertEquals( $expected, $test->get_dismiss_mode() );
	}

	/**
	 * @return array
	 */
	public function provider_get_dismiss_mode() {
		return array(
				array( 'global', 'global' ),
				array( 'user', 'user' ),
				array( 'none', 'none' ),
				array( 'fake', 'none' )
		);
	}

	/**
	 * @dataProvider provider_get_status
	 *
	 * @param string $status
	 * @param string $expected
	 */
	public function test_get_status( $status, $expected ) {
		$test = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'status' => $status ) );

		$this->assertEquals( $expected, $test->get_status() );
	}

	/**
	 * @return array
	 */
	public function provider_get_status() {
		return array(
				array( 'info', 'info' ),
				array( 'success', 'success' ),
				array( 'warning', 'warning' ),
				array( 'error', 'error' ),
				array( 'fake', 'info' )
		);
	}

	public function test_get_dismiss_url() {
		WP_Mock::passthruFunction( 'wp_nonce_url' );

		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );
		\WP_Mock::userFunction( 'add_query_arg', array(
				'times'  => 1,
				'args'   => array( 'aeg-notice-manager-dismiss', 'notice-test' ),
				'return' => 'aeg-notice-manager-dismiss=notice-test'
		) );

		$expected = 'aeg-notice-manager-dismiss=notice-test';
		$test     = ( new NoticeFactory() )->create( 'notice-test', 'Message' );

		$this->assertContains( $expected, $test->get_dismiss_url() );
	}

	public function test_useless_dismiss() {
		$test = ( new NoticeFactory() )->create( 'notice-test', 'Message' );

		$this->assertEquals( 0, $test->dismiss() );
	}

	public function test_dismiss_by_user() {
		$test1 = ( new NoticeFactory() )->create( 'notice-test-1', 'Message', array( 'dismiss_mode' => 'user' ) );
		$test2 = ( new NoticeFactory() )->create( 'notice-test-2', 'Message', array( 'dismiss_mode' => 'user' ) );

		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );

		\WP_Mock::userFunction( 'get_user_meta', array(
				'return_in_order' => array( false, array( 'notice-test-1' ) )
		) );

		\WP_Mock::userFunction( 'update_user_meta', array(
				'times'  => 2,
				'return' => true
		) );

		$this->assertTrue( $test1->dismiss() );
		$this->assertTrue( $test2->dismiss() );
	}

	public function test_dismiss_by_global() {
		$test1 = ( new NoticeFactory() )->create( 'notice-test-1', 'Message', array( 'dismiss_mode' => 'global' ) );
		$test2 = ( new NoticeFactory() )->create( 'notice-test-2', 'Message', array( 'dismiss_mode' => 'global' ) );

		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );

		\WP_Mock::userFunction( 'get_option', array(
				'return_in_order' => array( false, array( 'notice-test-1' ) )
		) );

		WP_Mock::userFunction( 'update_option', array(
				'times'  => 2,
				'return' => true
		) );

		$this->assertTrue( $test1->dismiss() );

		$this->assertTrue( $test2->dismiss() );
	}

	public function test_is_dismiss_by_user() {
		$test = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'dismiss_mode' => 'user' ) );

		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );

		\WP_Mock::userFunction( 'get_user_meta', array(
				'return_in_order' => array( false, array( 'notice-test' ) )
		) );

		\WP_Mock::userFunction( 'update_user_meta', array(
				'times'  => 1,
				'return' => true
		) );

		$this->assertFalse( $test->is_dismissed() );

		$test->dismiss();

		$this->assertTrue( $test->is_dismissed() );
	}

	public function test_is_dismiss_by_global() {
		$test = ( new NoticeFactory() )->create( 'notice-test', 'Message', array( 'dismiss_mode' => 'global' ) );

		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 1 ) );

		\WP_Mock::userFunction( 'get_option', array(
				'return_in_order' => array( false, array( 'notice-test' ) )
		) );

		\WP_Mock::userFunction( 'update_option', array(
				'times'  => 1,
				'return' => true
		) );

		$this->assertFalse( $test->is_dismissed() );

		$test->dismiss();

		$this->assertTrue( $test->is_dismissed() );
	}
}