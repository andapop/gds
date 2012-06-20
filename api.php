<?php 

/****************************************************************************/
/* Chains 
/****************************************************************************/

function gds_add_chain($corporate_id = false){

		global $wpdb;

		if(!$corporate_id) {
			$corporate_id = 0;
		}

		$date_created = current_time('mysql');
		$random_passcode = wp_generate_password( $length=10, $include_standard_special_chars=false );
		
		$t_name = $wpdb->prefix . "chains" ;
		$wpdb->insert( $t_name ,
			array(
				'date_created' => $date_created,
				'corporate_id' => $corporate_id,
				'passcode' => $random_passcode
				)
			);

		$chain_id = $wpdb->insert_id;

		return($chain_id);	
}

function gds_update_chain_corporate($chain_id, $corporate_id) {
	global $wpdb;
	$t_name = $wpdb->prefix . "chains" ;
	$wpdb->update($t_name, 
			array( 
				'corporate_id' => $corporate_id
			), 
			array( 'ID' => $chain_id ), 
			array( 
				'%d'
			), 
			array( '%d' ) 
		);
}

function gds_add_n_chains($number, $corporate_id = false ) {
	while ($number > 0 ):
		gds_add_chain($corporate_id);
		$number--; 
	endwhile;
}

function gds_update_chain($args) {
	global $wpdb;

	extract($args);

	$t_name = $wpdb->prefix . "chains" ;
	$wpdb->update($t_name, 
			array(  'corporate_id' => $corporate_id,
                    'parent_id' => $parent_id,
                    'active' => $active
			), 
			array( 'ID' => $ID )			
		);
}

function gds_start_chain( $args ) {
		
		global $wpdb;
		extract($args);
		$is_branch = isset($parent_id) ? $parent_id : 0;



		$result = $wpdb->get_row( $wpdb->prepare( "	SELECT *
													FROM $wpdb->chains 
													WHERE ID = %d 
													AND passcode = %s
												", 
												$chain_id, 
												$passcode
											) );
		
		if($result) { //if the chain exists and passcode is ok

			if ($result->leader_id != 0 ) {
				return "Seems this chain already has a leader";
				exit;
			}

			$current_user = wp_get_current_user();
			$leader_id = $current_user->ID;
			
			
			if($is_branch) {

				$chain_update_array = array( 
								'parent_id' => $parent_id,
								'leader_id' => $leader_id
							);
			}
			else {
				$chain_update_array = array( 
								'leader_id' => $leader_id
							);
			}

			// update chain 
			$t_name = $wpdb->prefix . "chains" ;
			$wpdb->update( $t_name,  
							$chain_update_array ,
							array( 'ID' => $chain_id )
						);


			// add the first wristband
			$wb_args = array('wb_number'=> 1, 
						'chain_id' => $chain_id,
						'user_id' => $leader_id,
						'story1' => $story1,
						'story2' => $story2,
						'city' => $city,
						'state' => $state,
						'country' => $country
						);
			$add_wb = gds_add_wristband($wb_args);

			if(is_bool($add_wb) && ($add_wb)) {
				return true;
			} else {
				return "Seems this chain was already started. Check the information again, or try contacting us.";
			}
		}
		else { 	//chain with these details does not exist
			return 'Combination id/passcode is incorrect. Please check them and try again';
		}
}

function gds_set_chain_active_status($id, $status) {
	global $wpdb;

	$t_name = $wpdb->prefix . "chains" ;
	$upadted = $wpdb->update( $t_name,
								array(
									'active'=> $status
									),
								array( 'ID' => $id
									)
			);
}

function gds_get_chain_for_id($id) {
	global $wpdb;	
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->chains WHERE ID = %d ", $id ));
	return $result;
}

function gds_get_chain_corporate_id($chain_id) {
	global $wpdb;
	$result = $wpdb->get_col( $wpdb->prepare("SELECT corporate_id FROM $wpdb->chains WHERE ID = %d ", $chain_id));

	return (int)$result[0];
}

function gds_get_chain_parent_id($chain_id) {
	global $wpdb;
	$result = $wpdb->get_col( $wpdb->prepare("SELECT parent_id FROM $wpdb->chains WHERE ID = %d ", $chain_id));

	return (int)$result[0];
}

function gds_update_chain_corporate_range($lower_id, $higher_id, $corporate_id){
	global $wpdb;

	$result = $wpdb->query( $wpdb->prepare( "	UPDATE $wpdb->chains 
											SET corporate_id = %d
											WHERE ID >= %d 
											AND  ID <= %d 
												", 
											$corporate_id,
											$lower_id, 
											$higher_id
										) );	

	return $result;
}

function gds_set_exported_range($lower_id, $higher_id) {
	global $wpdb;
	$date = current_time('mysql');

	$result = $wpdb->query( $wpdb->prepare( " UPDATE $wpdb->chains 
											SET exported = true,
											date_exported = %s
											WHERE ID >= %d 
											AND  ID <= %d 
												",
											$date,
											$lower_id, 
											$higher_id
										) );	

	return $result;
}

function gds_get_active_chain_ids(){
	global $wpdb;

	$results = $wpdb->get_col(" SELECT ID FROM $wpdb->chains WHERE active = true");	

	return $result;
}

function gds_get_claimed_chain_ids() {
	global $wpdb;

	$query = " SELECT c.ID FROM $wpdb->chains c
			INNER JOIN $wpdb->wristbands w
			ON  c.ID = w.chain_id
			WHERE c.active = true 
			GROUP BY c.ID"; 

	$chain_ids = $wpdb->get_col($wpdb->prepare($query));


	return $chain_ids;
}


/****************************************************************************/
/* Wristbands
/****************************************************************************/

function gds_add_wristband($args){
	global $wpdb;

	extract($args);

	$date_claimed = current_time('mysql');

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
				'country' => $country,
				'date_claimed' => $date_claimed
				),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
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

function gds_update_wristband($args){
	global $wpdb;
	extract($args);
	
	$t_name = $wpdb->prefix . "wristbands" ;
	
	$result = $wpdb->update( $t_name,
					array(
						'story1' => $story1,
						'story2' => $story2,
						'city' => $city,
						'state' => $state,
						'country' => $country,
						'approved' => $approved
						),
					array( 'ID' => $ID 
						)
				);	
	return $result;
}

function gds_set_wristband_status($id, $status) {
	global $wpdb;

	$t_name = $wpdb->prefix . "wristbands" ;
	$upadted = $wpdb->update( $t_name,
								array(
									'approved'=> $status
									),
								array( 'ID' => $id
									)
			);
}

function gds_delete_wristband($id) {
	global $wpdb;
	var_dump($id);

	$delete= $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wristbands
		 									WHERE ID = %d",
	        								$id ));
	var_dump($delete);
}

function gds_get_wristband_for_id($id) {
	global $wpdb;	
	$result = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->wristbands WHERE ID = %d", $id));

	return $result;
}

//removed approved from here on
function gds_get_wristbands_for_chain($chain_id) {
	global $wpdb;	
	$results = $wpdb->get_results(  $wpdb->prepare(" SELECT * FROM $wpdb->wristbands 
												WHERE chain_id = %d
												ORDER BY wb_number ASC"
												, $chain_id  ));

	return $results;
}

function gds_latest_wristband_for_chain($chain_id) {
	global $wpdb;	
	$result = $wpdb->get_row(  $wpdb->prepare(" SELECT * FROM $wpdb->wristbands 
												WHERE chain_id = %d
												ORDER BY date_claimed DESC"
												, $chain_id ));

	return $result;
}

function gds_get_wristbands_for_chain_cronological_desc($chain_id) {
	global $wpdb;	
	$results = $wpdb->get_results(  $wpdb->prepare(" SELECT * FROM $wpdb->wristbands 
												WHERE chain_id = %d
												ORDER BY date_claimed DESC"
												, $chain_id  ));

	return $results;
}


/****************************************************************************/
/* Users & Misc
/****************************************************************************/

function gds_user_pretty_name($id) {
	$user = get_user_by('id', $id);
	$name = $user->display_name;
	return $name;
}

function gds_user_has_branch($user_id, $chain_id) {
	global $wpdb;	
	$result = $wpdb->get_col(  $wpdb->prepare(" SELECT ID FROM $wpdb->chains 
												WHERE leader_id = %d
												AND parent_id = %d"
												, $user_id , $chain_id ));
	return $result;
}

function gds_pretty_date($date) {
	$php_date = strtotime($date);
	$pretty_date = date('D, d M Y', $php_date);

	return $pretty_date ;
}

/****************************************************************************/
/* Shortcodes 
/****************************************************************************/

function gds_start_form() {
	include dirname( __FILE__ ) . '/users/start_form.php';
}
add_shortcode( 'gds-start-form', 'gds_start_form' );


function gds_continue_form() {
	include dirname( __FILE__ ) . '/users/continue_form.php';
}
add_shortcode( 'gds-continue-form', 'gds_continue_form' );

function gds_branch_form() {
	include dirname( __FILE__ ) . '/users/branch_form.php';
}
add_shortcode( 'gds-branch-form', 'gds_branch_form' );

function gds_edit_form() {
	include dirname( __FILE__ ) . '/users/edit_form.php';
}
add_shortcode( 'gds-edit-form', 'gds_edit_form' );

function gds_display_my_wristbands(){
	global $wpdb;

	$current_user = wp_get_current_user();
	$my_id = $current_user->ID;

	//find all wristbands for a current user
	$wristbands = $wpdb->get_results(  $wpdb->prepare(" SELECT * FROM $wpdb->wristbands 
												WHERE user_id = %d
												ORDER BY ID DESC" , $my_id ));
	if($wristbands) {	?>
		<div class="chain-container">
			<ul class="chain-list">
				<?php
				foreach ($wristbands as $wristband) { ?>
					<li>
						<div class="chain">
							<div class= "chain-meta">
								<?php
								if ($wristband->wb_number == 1) {?>
									 <span> You <strong>started</strong> the Chain with id <?php echo $wristband->chain_id;?></span>
								<?php
								} else { ?>
									 <span> You <strong>continued</strong> the Chain with id <?php echo $wristband->chain_id;?> with Wristband #<?php echo $wristband->wb_number;?></span>					
								<?php
								} ?>
								<a href="<?php echo get_bloginfo('url');?>/chains/<?php echo $wristband->chain_id;?>" class="right">View Chain &rarr;</a>
								<?php
								if ($wristband->approved == false) {?>
									 <p class="attention"><em> Your story is awaiting approval. It is only visible to you </em></p>
								<?php
								}  ?>
							</div>
							<div class="chain-info clearfix">
								<p>Location: <?php echo $wristband->city; ?>, <?php echo $wristband->state; ?>, <?php echo $wristband->country; ?></p>
								<h3>Good Deed</h3>
								<p><?php echo $wristband->story1; ?></p>
								<h3>What I Want to Share</h3>
								<p><?php echo $wristband->story2; ?></p>
								<p><a href="<?php echo get_bloginfo('url');?>/your-chains/edit-chain/<?php echo $wristband->ID;?>" class="button" style="float: right;">Edit</a></p>
							</div>
						</div>
					</li>
					<?php
				} //for each ?>
			</ul>
      	</div>
	<?php 
	}
	else {
		echo '<p>You have no activity yet.</p>';
	}
}
add_shortcode('gds-display-my-wristbands', 'gds_display_my_wristbands');

function gds_display_chains($atts) {

	$chain_id = (int)get_query_var('chain_id');
		if($chain_id != 0) {
		echo gds_single_chain_view($chain_id);
	}
	else {
		extract(shortcode_atts( array( 
			'per_page' => 5
			), $atts ) );

		$pagenum = get_query_var('page');

		echo gds_display_chain_links($pagenum, $per_page); ?>
	<?php 
	}
}
add_shortcode('gds-display-chains', 'gds_display_chains');


/****************************************************************************/
/* Other form elements 
/****************************************************************************/

function gds_single_chain_view($chain_id) {
	$wristbands = gds_get_wristbands_for_chain($chain_id); 

	if($wristbands) {
		$first_wristband = $wristbands[0];
		$nb_wb = count($wristbands);

		$output = '';
		$output .= '<h1>Chain '.$chain_id.' </h1>';
		$output .= '<div class="chain-container">';
		

		// check if chain is assigned to corporate and display logo if necessary

		$corporate_id = gds_get_chain_corporate_id($chain_id);
		if($corporate_id != 0 ) {
			$corporate_logo_url = get_metadata('user', $corporate_id , 'logo_url' , true);
			if ($corporate_logo_url !== '') {
				$output .= '<img src="'.$corporate_logo_url.'" />';
			}
		}

		// check if chain has a parent

		$parent_id = gds_get_chain_parent_id($chain_id);
		if($parent_id != 0 ) {
			$output .='<a href="'.get_bloginfo('url').'/chains/'.$parent_id.'">This Chain is a branch. View Parent Chain.</a>';
		}

		$output .= '<ul class="chain-list">';
		$first_wristband = $wristbands[0];					
		$output .= '<li class= "first last">';

			//first wristband

			$output .='<div class="chain" id="wristband-1">';
				$output .='<div class="chain-meta">';
					$output .='<span class="name">'. gds_user_pretty_name($first_wristband->user_id).'</span> - <span class="location">'. $first_wristband->city .', '. $first_wristband->state .', '. $first_wristband->country .'</span>';
					$output .='<p><span class="date"> Chian was started on: '. gds_pretty_date($first_wristband->date_claimed) .'</span></p>';
					$output .='<a href="'.site_url('/chains/id/').$chain_id.'/feed" target="_blank" >RSS</a>';
				$output .='</div>';
				$output .='<div class="chain-info">';
					$output .='<h3>Good Deed</h3>';
					$output .='<p>'. $first_wristband->story1 . '</p>';
					$output .='<h3>What I Want to Share</h3>';
					$output .='<p>'. $first_wristband->story2 . '</p>';
				$output .='</div>';
			$output .='</div>';

			//the other wristbands

			if($nb_wb > 1) {
				$output .='<ul>';	

				for( $i=1 ; $i<$nb_wb ; $i++) { 
					$output .='<li>';
						$output .='<div class="chain" id="wristband-'.$wristbands[$i]->wb_number.'">';
							$output .='<div class="chain-meta">';
								$output .= gds_user_pretty_name($wristbands[$i]->user_id).'</span> - <span class="location">'. $wristbands[$i]->city .', '. $wristbands[$i]->state .', '. $wristbands[$i]->country .'</span>';
								$output .='<p><span class="date"> Wristband (#'.$wristbands[$i]->wb_number.') claimed on: '. gds_pretty_date($wristbands[$i]->date_claimed) .'</span></p>';
								$output .='<a href="#" class="back-top">back to top</a>';
							$output .='	</div>';
							$output .='<div class="chain-info">';
								$output .='<h3>Good Deed</h3>';
								$output .='<p>'. $wristbands[$i]->story1.'</p>';
								$output .='<h3>What I Want to Share</h3>';
								$output .='<p>'. $wristbands[$i]->story2 .'</p>';
							$output .='</div>';
						$output .='</div>';
				
					$branches = gds_user_has_branch($wristbands[$i]->user_id, $chain_id);
					if($branches) {
						foreach ($branches as $branch) {
							$output .='<ul>';
								$output .='<li>';
									$output .='<div class="chain">';
										$output .='<a href="'.get_bloginfo('url').'/chains/'.$branch.'">';
											$output .='<p>This user has created a branch. Click to view </p>';
										$output .='</a>';
									$output .='</div>';
								$output .='</li>';
							$output .='</ul>';
						}
						
					} //is branched	

					$output .='</li>';
				} //for

				$output .='</ul>';
			}
			
		$output .='</li>';
		$output .='</ul>';
		$output .='</div>';
	}
	else $output = "This chain does not exist, has not been claimed yet or is waiting approval.";

	return $output;
}

function gds_display_chain_links($pagenum, $per_page = false) {
	global $wpdb;

	$chain_ids = gds_get_claimed_chain_ids();
	$total_chains = count($chain_ids);
	$output ='';

	if(!$pagenum) {
		$pagenum = 1;
	}

	$total_pages = ceil($total_chains/$per_page);
	$base =  get_permalink();

	$args = array(
    'base'         => $base.'%_%',
    'format'       => '/page/%#%',
    'total'        => $total_pages,
    'current'      => $pagenum,
    'show_all'     => False,
    'end_size'     => 2,
    'mid_size'     => 3,
    'prev_next'    => True,
    'prev_text'    => __('&laquo; Previous'),
    'next_text'    => __('Next &raquo;')
    );

	$output .= '<p class="pagination"><span>Pages </span>'.paginate_links( $args ).'</p>';

	$offset = ($pagenum - 1) * $per_page;
	$current_page_chain_ids = array_slice($chain_ids, $offset, $per_page);

	$output .='<ul>';
	foreach ($current_page_chain_ids as $chain_id ) {
		$output .='<li>';
		$output .='<a href="'.site_url('/chains/id/').$chain_id.'">Chain '.$chain_id.'</a>';
		$output .='</li>';
	}

	return $output;	
}

function gds_chain_ids_dropdown($name = false, $label = false) {
	global $wpdb;
	$select_name = ($name) ? $name : 'chain_id';
	$select_label = ($label) ? $label : 'Choose a chain ID';

	$chain_ids = $wpdb->get_results( " SELECT ID FROM $wpdb->chains 
										WHERE active = true " );
	$output .= '<label for="chain_id">'.$select_label.'</label>';
	$output .= '<select name="'.$select_name.'" id="'.$select_name.'">';
	$output .= '<option value= "0" > None </option>';
	foreach ($chain_ids as $chain_id) {

		$output .= '<option value="'.$chain_id->ID.'">'.$chain_id->ID.'</option>';
	}
	$output .= '</select>';

	return $output;
}

function gds_claimed_chain_ids_dropdown($name = false, $label = false) {
	global $wpdb;
	$select_name = ($name) ? $name : 'chain_id';
	$select_label = ($label) ? $label : 'Choose a chain ID';

	$chain_ids = gds_get_claimed_chain_ids();
	$output .= '<label for="chain_id">'.$select_label.'</label>';
	$output .= '<select name="'.$select_name.'" id="'.$select_name.'">';
	$output .= '<option value= "0" > None </option>';
	foreach ($chain_ids as $chain_id) {
		$output .= '<option value="'.$chain_id->ID.'">'.$chain_id.'</option>';
	}
	$output .= '</select>';

	return $output;
}

/****************************************************************************/
/* Rewite rules 
/****************************************************************************/

add_filter( 'rewrite_rules_array','gds_insert_rewrite_rules' );
add_filter( 'query_vars','gds_insert_query_vars' );
add_action( 'wp_loaded','gds_flush_rules' );

// flush_rules() if our rules are not yet included
function gds_flush_rules(){
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['chains/id/([0-9]{1,})$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	} 
	if ( ! isset( $rules['chains/page/([0-9]{1,})$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	} 


	if ( ! isset( $rules['your-chains/edit-chain/([0-9]{1,})/?$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}

	if ( ! isset( $rules['your-chains/id/([0-9]{1,})/feed?$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

// Add new rules
function gds_insert_rewrite_rules( $rules ) {

	$edit_page = get_page_by_title( 'Edit Chain' );
	$edit_id = $edit_page->ID;

	$chains_page = get_page_by_title( 'Chains' );
	$chains_id = $chains_page->ID;

	$newrules = array();
	
	$newrules['chains/id/([0-9]{1,})$'] = 'index.php?page_id='.$chains_id.'&chain_id=$matches[1]';
	$newrules['chains/page/([0-9]{1,})$'] = 'index.php?page_id='.$chains_id.'&page=$matches[1]';
	$newrules['your-chains/edit-chain/([0-9]{1,})/?$'] = 'index.php?page_id='.$edit_id.'&wristband_id=$matches[1]';
	$newrules['chains/id/([0-9]{1,})/feed$'] = 'index.php?feed=chain'.'&chain_id=$matches[1]';
	return $newrules + $rules;
}

// Adding query vars
function gds_insert_query_vars( $vars ) {
    array_push($vars, 'chain_id', 'wristband_id', 'export_ids');
    return $vars;
}


/****************************************************************************/
/* Feed
/****************************************************************************/
add_action( 'init', 'gds_add_chain_feed');

function gds_add_chain_feed() {
	add_feed( 'chain', 'do_feed_chain' );
}

function do_feed_chain() {
  	load_template( dirname( __FILE__ ) .'/feed/chain-feed.php');
}

add_action( 'do_feed_chain','do_feed_chain', 10, 1 );
?>