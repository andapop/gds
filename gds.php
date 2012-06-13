<?php
/*
Plugin Name: Good Deed Seed
Version: 0.1
Description: Adds all Chains and Wristbands related functionality
Author: Forward Inc.
Plugin URI: 
Text Domain:

*/

require dirname( __FILE__ ) . '/scb/load.php';

function gds_init() {
	$chain_table = new scbTable( 'chains', __FILE__, "
		ID int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		leader_id int(20) UNSIGNED DEFAULT '0'  NOT NULL,
		corporate_id int(20) UNSIGNED DEFAULT '0'  NOT NULL,
		parent_id int(20) UNSIGNED DEFAULT '0'  NOT NULL,
		passcode varchar(20) DEFAULT '' NOT NULL,
		active bool DEFAULT '1' NOT NULL,
		exported bool DEFAULT '0' NOT NULL,
		date_exported datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY (ID),
		KEY leader_id (leader_id),
 		KEY corporate_id (corporate_id),
 		KEY parent_id (parent_id)
	");

	$wristband_table = new scbTable( 'wristbands', __FILE__, "
		ID int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		wb_number int(1) UNSIGNED NOT NULL,
		chain_id int(20) UNSIGNED NOT NULL, 
		user_id int(20) UNSIGNED DEFAULT '0'  NOT NULL,
		story1 text NOT NULL,
		story2 text NOT NULL,
		city varchar(30) DEFAULT '' NOT NULL,
		state varchar(20) DEFAULT '' NOT NULL, 
		country varchar(20) DEFAULT '' NOT NULL,
		approved bool DEFAULT '0' NOT NULL,
		PRIMARY KEY  (ID),
 		UNIQUE KEY wb_number_chain_id (wb_number,chain_id),
 		KEY wb_number (wb_number),
 		KEY chain_id (chain_id),
 		KEY user_id (user_id)
	");



	require_once dirname( __FILE__ ) . '/api.php';
	require_once dirname( __FILE__ ) . '/admin/chains.php';
	require_once dirname( __FILE__ ) . '/admin/chains_list_table.php';
	require_once dirname( __FILE__ ) . '/admin/wristbands.php';
	require_once dirname( __FILE__ ) . '/admin/wristbands_list_table.php';

	
	add_action( 'admin_init', 'my_enqueues' );

	function my_enqueues() {
		
		$dir = plugin_dir_url( __FILE__ );

		wp_enqueue_style( 'gds_style',  $dir .'gds.css' , '', '1.0' );
		wp_enqueue_script( 'gds_confirm_delete',  $dir . 'admin/js/confirm_delete.js' , array('jquery'), '0.1', true );
		wp_enqueue_script( 'gds_show_select_ids',  $dir . 'admin/js/show_corporate_id.js' ,array('jquery'), '0.1', true );
	}

	add_action('template_redirect', 'my_user_enqueues');
	function my_user_enqueues() {
		
		$dir = plugin_dir_url( __FILE__ );

		wp_enqueue_script( 'gds_user_init',  $dir . 'users/js/init.js' ,array('jquery'), '0.1', true );
	}


	

}

scb_init( 'gds_init' );

?>