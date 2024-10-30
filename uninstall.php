<?php

if( ! defined('WP_UNINSTALL_PLUGIN') ) exit;

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_decor");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_decor_link");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_geoip");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_item");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_item_rules");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_resolution");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_resolution_type");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_search");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_search_item");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_template");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_template_meta");
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}custom_block_time");

delete_option('cb_show_for');
delete_option('cb_blocks');
