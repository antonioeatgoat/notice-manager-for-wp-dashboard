<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload the classes of the plugin. It's the backup autoload system for who cannot use the composer autoload system.
 *
 * @param  string $class
 *
 * @return bool
 */
function nmwp_autoload($class) {

    if ( false === strpos( $class, 'Aeg\DashboardNotice\\' ) ) {
        return false;
    }

    $directory_path = dirname(__FILE__) . '/src/classes/';
    $file_name      = str_replace('Aeg\DashboardNotice\\', '', $class);

    $file = $directory_path . $file_name . '.php';

    if( file_exists( $file ) ) {
	    include $file;
	    return true;
    }

    return false;
}

spl_autoload_register('nmwp_autoload');