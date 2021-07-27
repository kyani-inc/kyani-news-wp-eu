<?php
/*
 * Plugin Name: Kyani Europe Custom Configuration
 * Description: This plugin holds custom functionality for Kyani Europe News
 * Version: 1.0
 * Author: Kyani Corp.
 * Author URI: https://kyani.com
 */

$kyani_plugin_includes = array(
	'/setup.php'
);

foreach ($kyani_plugin_includes as $file) {
	require_once plugin_dir_path(__FILE__) . 'includes' . $file;
}
