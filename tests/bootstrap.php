<?php

use PHPUnit\Framework\TestCase;

\WP_Mock::bootstrap();

class aeg_NM_UnitTestCase extends TestCase {

	/**
	 * SetUp initial settings
	 */
	function setUp() {
		\WP_Mock::setUp();
	}

	/**
	 * Break down for next test
	 */
	function tearDown() {
		\WP_Mock::tearDown();
	}
}
