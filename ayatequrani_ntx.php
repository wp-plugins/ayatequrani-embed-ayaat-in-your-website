<?php
/*
 * Plugin Name: Ayat e Qurani
 * Version: 1.0
 * Plugin URI: http://nitroxis.com/
 * Description: Include a single or multiple ayats in your content with an easy shortcode e.g. <strong>[quran ayat="112" surah="2"]</strong>
 * Author: Nitroxis
 * Author URI: http://hammad.nitroxis.com/
 * Requires at least: 3.4
 * Tested up to: 4.1
 *
 * Text Domain: ayatequrani_ntx
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hammad Asif
 * @since 0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$token = "ayatequrani_ntx";
$version = "1.0";
// Load plugin class files
require_once('inc/plugin.class.php');
// load the p
NTX_plugin::instance(__FILE__, $version, $token);
