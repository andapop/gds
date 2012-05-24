<?php 
// used in admin to create the chains
function gds_add_chain($leader_id = false){

		global $wpdb;

		if(!$leader_id) {
			$current_user = wp_get_current_user();
			$leader_id = $current_user->ID;
		}

		$date_created = current_time('mysql');
		$random_passcode = wp_generate_password( $length=10, $include_standard_special_chars=false );
		
		$t_name = $wpdb->prefix . "chains" ;
		$wpdb->insert( $t_name ,
			array(
				'date_created' => $date_created,
				'leader_id' => $leader_id,
				'passcode' => $random_passcode
				)
			);

		$chain_id = $wpdb->insert_id;

		return($chain_id);
		
}

// used to add a wristband
function gds_add_wristband($args){
	global $wpdb;

	extract($args);

	$t_name = $wpdb->prefix . "wristbands" ;
	$inserted = $wpdb->insert( $t_name,
			array(
				'wb_number' => $wb_number,
				'chain_id' => $chain_id, 
				'user_id' => $user_id,
				'story1' => $story1,
				'story2' => $story2,
				'city' => $city,
				'state' => $state,
				'country' => $country
				),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
				)
			);

	if(!$inserted){
		$error = 'Wristband no. '. $wb_number .' for chain id '. $chain_id.' already exists';
		return $error;
	}
	else {
		return true;
	}

}

function gds_update_chain_leader($chain_id, $leader_id) {
		global $wpdb;
		$t_name = $wpdb->prefix . "chains" ;
		$wpdb->update($t_name, 
				array( 
					'leader_id' => $leader_id
				), 
				array( 'ID' => $chain_id ), 
				array( 
					'%d'
				), 
				array( '%d' ) 
			);
}

// used when a user starts a chain 
// returns true if successfull, error message if not
function gds_start_chain( $args ) {
		
		global $wpdb;
		extract($args);
		var_dump($chain_id . "  " .$passcode );

		$result = $wpdb->get_row( $wpdb->prepare( "	SELECT *
													FROM $wpdb->chains 
													WHERE ID = %d 
													AND passcode = %s
												", 
												$chain_id, 
												$passcode
											) );
		
		if($result) { //if the chain exists and passcode is ok

			$current_user = wp_get_current_user();
			$leader_id = $current_user->ID;
			
			
			if($parent_id) {
				// update both parent chain and chain leader
				$t_name = $wpdb->prefix . "chains" ;
				$wpdb->update( $t_name, 
							array( 
								'parent_id' => $parent_id,
								'leader_id' => $leader_id
							), 
							array( 'ID' => $chain_id ), 
							array( 
								'%d',
								'%d'
							), 
							array( '%d' ) 
						);


			}
			else {
				//update only the chain leader
				gds_update_chain_leader($chain_id, $leader_id);
			}

			// add the first wristband
			$wb_args= array('wb_number'=> 1, 
						'chain_id' => $chain_id,
						'user_id' => $leader_id,
						'story1' => $story1,
						'story2' => $story2,
						'city' => $city,
						'state' => $state,
						'country' => $country
						);
			gds_add_wristband($wb_args);

			return true;
		}
		else { 	//chain with these details does not exist
			$error = "Combination id/passcode is incorrect. Please Check them an try again";

			return $error;
		}

}

function gds_edit_wristband($args){
	global $wpdb;
	extract($args);
	
	$t_name = $wpdb->prefix . "wristbands" ;
	$upadted = $wpdb->update( $t_name,
								array(
									'story1' => $story1,
									'story2' => $story2,
									'city' => $city,
									'state' => $state,
									'country' => $country
									),
								array( 'chain_id' => $chain_id , 
										'wb_number' => $wb_number 
									), 
								array(
									'%s',
									'%s',
									'%s',
									'%s',
									'%s'
									),
								array(
									'%d',
									'%d'
									)
			);

}

function gds_add_n_chains($number, $leader_id = false ) {
	while ($number > 0 ):
		gds_add_chain($leader_id);
		$number--; 
	endwhile;
}
?>