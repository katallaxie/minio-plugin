<?php

/*
Plugin Name: Minio WordPress Plugin
Plugin URI: http://wordpress.org/extend/plugins/minio-plugin/
Description: Use Minio on your WordPress to store all your precious assets.
Author: Sebastian Döll <sebastian@katallaxie.me>
Version: 1.0
Author URI: https://katallaxie.me
Network: True
Text Domain: minio-plugin
Domain Path: /languages/

// Copyright 2017 Sebastian Döll <sebastian@katallaxie.me>
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

require 'vendor/autoload.php';

define( 'MINIO__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

global $minio;

$abspath = dirname( __FILE__ );
require_once $abspath . '/includes/functions.php';
require_once $abspath . '/classes/minio-settings-field.php';
require_once $abspath . '/classes/minio-settings-section.php';
require_once $abspath . '/classes/minio-settings.php';
require_once $abspath . '/classes/minio-local-to-s3.php';
require_once $abspath . '/classes/minio-s3-to-local.php';
require_once $abspath . '/classes/minio.php';

$mini = new Minio( __FILE__, '1.0.0' );

register_activation_hook( __FILE__, 'Minio::activation' );
