<?php

use Aeg\DashboardNotice\Notice;
use Aeg\DashboardNotice\NoticeFactory;
use Aeg\DashboardNotice\NoticeRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The actual notice that will be displayed in the dashboard.
 */
class NoticeRendererTest extends aeg_NM_UnitTestCase {

	private $defaults = array(
			'title'          => '',
			'dismiss_text' => '',
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

	public function test_render() {
		$notice_mock = Mockery::mock( 'Aeg\DashboardNotice\Notice')->makePartial();
		$notice_mock->shouldReceive( 'is_dismissed')->andReturnFalse();

		$test = new NoticeRenderer( $notice_mock );

		$this->assertTrue( $test->render() );
	}

	public function test_render_dismissed() {
		$notice_mock = Mockery::mock( 'Aeg\DashboardNotice\Notice' )->makePartial();
		$notice_mock->shouldReceive( 'is_dismissed' )->andReturnTrue();

		$test = new NoticeRenderer( $notice_mock );

		$this->assertFalse( $test->render() );
	}

	public function test_print_notice_html() {
		$notice = ( new NoticeFactory() )->create(
				'notice-test',
				'Hello World',
				array(
						'title' => 'Title'
				));

		$test = new NoticeRenderer( $notice );

		ob_start();
		$test->print_notice_html();
		$string = ob_get_clean();

		$this->assertStringEqualsFile(dirname(__FILE__) . '/../templates/test_print_notice_html.php', $string );
	}

	public function test_print_notice_html_not_valid_template() {
		$notice = ( new NoticeFactory() )->create(
				'notice-test',
				'Hello World',
				array(
						'title' => 'Title'
				));

		$test = new NoticeRenderer( $notice, 10, 'fake' );

		ob_start();
		$test->print_notice_html();
		$string = ob_get_clean();

		$this->assertEmpty( $string );
	}
}