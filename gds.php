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
		parent_id int(20) UNSIGNED DEFAULT '0'  NOT NULL,
		passcode varchar(20) DEFAULT '' NOT NULL,
		PRIMARY KEY  (ID)
	");

	$wristband_table = new scbTable( 'wristbands', __FILE__, "
		wb_number int(1) UNSIGNED NOT NULL,
		chain_id int(20) UNSIGNED NOT NULL, 
		user_id int(20) UNSIGNED DEFAULT '0'  NOT NULL,
		story1 text NOT NULL,
		story2 text NOT NULL,
		city varchar(30) DEFAULT '' NOT NULL,
		state varchar(3) DEFAULT '' NOT NULL, 
		country varchar(20) DEFAULT '' NOT NULL,
		PRIMARY KEY  (chain_id , wb_number),
		FOREIGN KEY (chain_id) REFERENCES chains(ID)
	");

	require_once dirname( __FILE__ ) . '/api.php';
	require_once dirname( __FILE__ ) . '/adminpage.php';
	require_once dirname( __FILE__ ) . '/chains_list_table.php';

}

scb_init( 'gds_init' );

?>