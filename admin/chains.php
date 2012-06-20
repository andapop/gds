<?php

add_action('admin_menu', 'gds_chains_menu');

function gds_chains_menu() {
	add_menu_page('Chains', 'Chains', 'administrator', 'chains', 'gds_chains' , '', '27' ); 
	add_submenu_page('chains', 'All Chains', 'All Chains', 'administrator', 'chains', 'gds_chains');
	add_submenu_page('chains', 'Add Chains', 'Add Chains', 'administrator', 'add-chains', 'gds_add_chains');
    add_submenu_page('chains', 'Assign Corporate', 'Assign Corporate', 'administrator', 'assign-corporate', 'gds_assign_corporate');
    add_submenu_page('chains', 'Export', 'Export', 'administrator', 'export-chains', 'gds_export_chains');
}



/*******************************************************************************************************************************************************
*
* Chains Page
*
********************************************************************************************************************************************************/


/*******************************
*
* chack for bulk and row actions
*
********************************/
add_action( 'load-toplevel_page_chains', 'gds_chains_onload' );

function gds_chains_onload(){
	
	$pagenum = isset($_REQUEST['_page']) ? $_REQUEST['_page'] : 0 ;
    
	// check for row action activate/ delete / or submission from edit form 
	
    $action = isset($_REQUEST['chaction']) ? $_REQUEST['chaction'] : 0 ;
    if($action) { 
        if ( isset($_REQUEST['c'])) {
            $chain_id = (int)$_REQUEST['c']; 
        }elseif ( wp_get_referer() ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }   
    
        $nonce_action =$action.'_'.$chain_id;
        
        if(! check_admin_referer($nonce_action)){
            exit;
            }
        $activated_chain = $deactivated_chain = $updated_chain = 0 ;

        $redirect_to = remove_query_arg( array( 'activated', 'deactivated', 'assignedcorporate', 'corporate_id' ,'ids','activated_chain', 'deactivated_chain', 'updated_chain', 'chaction', 'c', '_wpnonce', 'edit_chaction'), wp_get_referer() );
      
        if( $pagenum > 1)
        $redirect_to = add_query_arg( 'paged', $pagenum, $redirect_to );           

            switch ($action) {
                case 'activatechain':
                    gds_set_chain_active_status( $chain_id, true );
                    $activated_chain = $chain_id;
                    break;
                case 'deactivatechain':
                    gds_set_chain_active_status( $chain_id, false );
                    $deactivated_chain = $chain_id;
                    break;
                case 'updatechain':
                    $args = array('ID' => $chain_id,
                                'corporate_id' => $_POST['corporate_id'],
                                'parent_id' => $_POST['parent_id'],
                                'active' => (bool) $_POST['active']
                                );
                    gds_update_chain($args);                                 
                    $updated_chain = $chain_id;
                    break;
            }

        if ( $activated_chain )
            $redirect_to = add_query_arg( 'activated_chain', $activated_chain, $redirect_to );
        if ( $deactivated_chain )
            $redirect_to = add_query_arg( 'deactivated_chain', $deactivated_chain, $redirect_to );
        if( $updated_chain ) {
            $redirect_to = add_query_arg( 'updated_chain', $updated_chain, $redirect_to );
        }             
          wp_safe_redirect( $redirect_to );
          exit;
    } 
    
    
    // check for bulk  actions
    	
        $doaction = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0 ;
        
        if ( $doaction ) {            
        check_admin_referer('bulk-chains');

        if ( isset( $_REQUEST['ids'] )) {
            $chain_ids = array_map( 'absint', $_REQUEST['ids']  );
            $corporate_id = (isset($_REQUEST['assign_corporate_id'])) ? $_REQUEST['assign_corporate_id'] : 0 ;
        }elseif ( wp_get_referer() ) {
            $redirect_to = remove_query_arg( array( 'activated', 'deactivated', 'assignedcorporate' ,'ids', 'corporate_id','activated_chain', 'deactivated_chain', 'updated_chain'), wp_get_referer() );
            wp_safe_redirect($redirect_to);
            exit;
        } 

        $activated = $deactivated = $assignedcorporate = 0;

        $redirect_to = remove_query_arg( array( 'activated', 'deactivated','assignedcorporate', 'corporate_id' ,'ids','activated_chain', 'deactivated_chain', 'updated_chain'), wp_get_referer() );
        
        if($pagenum > 1)
            $redirect_to = add_query_arg( 'paged', $pagenum, $redirect_to );
        
        
        foreach ( $chain_ids as $chain_id ) { 
            // Check the permissions on each
            // if ( !current_user_can( 'edit_chain', $chain_id ) )
            //  continue;
            switch ( $doaction ) {
                case 'activate' :
                    gds_set_chain_active_status( $chain_id, true );
                    $activated++;
                    break;
                case 'deactivate' :
                    gds_set_chain_active_status( $chain_id, false );
                    $deactivated++;
                    break;
                case 'assign_corporate' :
                    gds_update_chain_corporate($chain_id, $corporate_id );
                    $assignedcorporate++ ;
                    break;
            }
        }           
    

         if ( $activated )
            $redirect_to = add_query_arg( 'activated', $activated, $redirect_to );
        if ( $deactivated )
            $redirect_to = add_query_arg( 'deactivated', $deactivated, $redirect_to );
        if ( $assignedcorporate ) {
            $redirect_to = add_query_arg( 'assignedcorporate', $assignedcorporate, $redirect_to );
            $redirect_to = add_query_arg( 'corporate_id', $corporate_id, $redirect_to );
        }

        
        wp_safe_redirect( $redirect_to );
        exit; 
        
    } 
    
    elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
         wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
         exit;
    }
		
	
}

/*******************************
*
* Chains Page hadler function
*
********************************/

function gds_chains(){
	//edit row action was performed    
    $edit_action = isset($_REQUEST['edit_chaction']) ? $_REQUEST['edit_chaction'] : 0 ;
    
     //if we have to edit the chain get the edit form
    if($edit_action) { 
        $chain_id = isset($_REQUEST['c']) ? (int)$_REQUEST['c'] : 0 ;  
        $nonce_action ='editchain_'.$chain_id;        
        if(! check_admin_referer($nonce_action)){
        	echo __('You are not allowed');
            exit;
           }
    	    	
       gds_show_edit_chain_form($chain_id);
    }
    else{    // else show the list table 
        gds_show_chain_list();
    }
}

/*******************************
*
* Chains List
*
********************************/

function gds_show_chain_list () {
    global $wpdb;
	
    $chainListTable = new GDS_Chain_List_Table();

    //Fetch, prepare, sort, and filter our data
    $chainListTable->prepare_items();
    
    ?>
    <div class="wrap">
            
        <div id="icon-post" class="icon32"><br/></div>
        <h2><?php echo __('Chains');

        if ( isset($_REQUEST['s']) && $_REQUEST['s'] )
             printf( '<span class="subtitle">' . sprintf( __( 'Search results for &#8220;%s&#8221;' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ) . '</span>' ); ?>

        </h2>
        <?php
        if ( isset( $_REQUEST['error'] ) ) {
            $error = (int) $_REQUEST['error'];
            $error_msg = '';
            switch ( $error ) {
                case 1 :
                    $error_msg = __( 'Oops, no chain with this ID.' );
                    break;
                case 2 :
                    $error_msg = __( 'You are not allowed to edit chains.' );
                    break;
            }
            if ( $error_msg )
                echo '<div id="moderated" class="error"><p>' . $error_msg . '</p></div>';
        }

        if ( isset($_REQUEST['activated']) || isset($_REQUEST['deactivated']) || isset($_REQUEST['assignedcorporate']) || isset($_REQUEST['activated_chain']) || isset($_REQUEST['deactivated_chain']) || isset($_REQUEST['updated_chain'])  ) {
            $activated  = isset( $_REQUEST['activated']  ) ? (int) $_REQUEST['activated']  : 0;
            $deactivated  = isset( $_REQUEST['deactivated']  ) ? (int) $_REQUEST['deactivated']  : 0;
          
            $assignedcorporate  = isset( $_REQUEST['assignedcorporate']  ) ? (int) $_REQUEST['assignedcorporate']  : 0;
            $activated_chain  = isset( $_REQUEST['activated_chain']  ) ? (int) $_REQUEST['activated_chain']  : 0;
            $deactivated_chain  = isset( $_REQUEST['deactivated_chain']  ) ? (int) $_REQUEST['deactivated_chain']  : 0;
            $updated_chain  = isset( $_REQUEST['updated_chain']  ) ? (int) $_REQUEST['updated_chain']  : 0;
            $corporate_id  = isset( $_REQUEST['corporate_id']  ) ? (int) $_REQUEST['corporate_id']  : 0;
            

            if ( $activated > 0 || $deactivated > 0 || $assignedcorporate > 0|| $activated_chain > 0 || $deactivated_chain > 0 || $updated_chain > 0 ) {
                if ( $activated > 0 )
                    $messages[] = sprintf( _n( '%s chain activated', '%s chains activated', $activated ), $activated );

                if ( $deactivated > 0 ) 
                    $messages[] = sprintf( _n( '%s chain deactivated.', '%s chains deactivated.', $deactivated ), $deactivated );  

                if ( $assignedcorporate > 0 ) 
                     $messages[] = sprintf( _n( 'Chain was assigned to corporate id %s', 'Chains were assigned to corporate id %s', $assignedcorporate ), $corporate_id); 

                if ( $activated_chain > 0 )
                    $messages[] = sprintf( __( 'Chain with id %s was activated'), $activated_chain );
                
                if ( $deactivated_chain > 0 )
                    $messages[] = sprintf( __( 'Chain with id %s was deactivated'), $deactivated_chain ); 

                if ( $updated_chain > 0 ) 
                     $messages[] = sprintf( __( 'Chain with id %s was updated'), $updated_chain );         
                   
               

                echo '<div id="moderated" class="updated"><p>' . implode( "<br/>\n", $messages ) . '</p></div>';
            }
        }         
        ?>

        <?php $chainListTable->views(); ?>
        
        <form id="chain-filter" method="get">
            <?php
            $chain_status = isset($_REQUEST['chain_status']) ? $_REQUEST['chain_status'] : 'all';
            $chainListTable->search_box( __( 'Search Chains' ), 'chain' ); 

          ?>             
       

            <input type="hidden" name="chain_status" value="<?php echo esc_attr($chain_status); ?>" />
            
            <input type="hidden" name="_total" value="<?php echo esc_attr( $chainListTable->get_pagination_arg('total_items') ); ?>" />
            <input type="hidden" name="_per_page" value="<?php echo esc_attr( $chainListTable->get_pagination_arg('per_page') ); ?>" />
            <input type="hidden" name="_page" value="<?php echo esc_attr( $chainListTable->get_pagination_arg('page') ); ?>" />

            <?php if ( isset($_REQUEST['paged']) ) { ?>
                <input type="hidden" name="paged" value="<?php echo esc_attr( absint( $_REQUEST['paged'] ) ); ?>" />
            <?php } ?>

            <!-- Ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

            <!-- Render the completed list table -->
            <?php $chainListTable->display() ;

            // add the select dropdown 
           $chainListTable->show_select_corporate_id(); ?>


        </form>

        
        
    </div>

	</div>
<?php
}

/*******************************
*
* Edit Chain Form
*
********************************/

function gds_show_edit_chain_form($chain_id){

    global $wpdb;

    $chain = gds_get_chain_for_id($chain_id);
    $user = get_user_by('id', $chain->leader_id);
    if(!$user) {
        $leader_name = "Unassigned";
    } else  {
        $leader_name = $user->user_nicename;
    }

    ?>

    <form name="post" action="admin.php?page=chains" method="post" id="post">
        <?php wp_nonce_field('updatechain_' . $chain->ID) ?>
        <input type="hidden" name="chaction" value='updatechain' />

        <div class="wrap">
            <div id="icon-posts" class="icon32"><br/></div>
            <h2><?php echo __('Edit Chain'); ?></h2>

            <h3><?php printf(__('Chain ID: %1$s / Leader: %2$s'), $chain->ID, $leader_name ); ?></h3>

            <div id="poststuff" class="metabox-holder has-right-sidebar">

                <div id="side-info-column" class="inner-sidebar">
                    <div id="submitdiv" class="stuffbox" >
                        <h3><span class='hndle'><?php _e('Status') ?></span></h3>
                        <div class="inside">
                            <div class="submitbox" id="submitchain">
                                <div id="minor-publishing">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section" id="chain-status-radio">
                                            <label class="approved"><input type="radio"<?php checked( $chain->active, '1' ); ?> name="active" value="1" /><?php /* translators: comment type radio button */ _ex('Active', 'adjective') ?></label><br />
                                            <label class="waiting"><input type="radio"<?php checked( $chain->active, '0' ); ?> name="active" value="0" /><?php /* translators: comment type radio button */ _ex('Inactive', 'adjective') ?></label><br />
                                        </div>
                                    </div> <!-- misc actions -->
                                    <div class="clear"></div>
                                    <div class="misc-publishing-actions">
                                        <?php 
                                            if($chain->exported){ 
                                                $exp_text = "This chain has beed exported";
                                            } else {
                                                 $exp_text = "Chain not exported yet.";
                                            }
                                        ?>
                                        <p class="export_status"> <?php echo $exp_text;?></p>
                                    </div>

                                </div>

                                <div id="major-publishing-actions">
                    
                                    <div id="publishing-action">
                                          <?php submit_button( __( 'Update' ), 'primary', 'save', false, array( 'tabindex' => '4' ) ); ?>
                                    </div>
                                    <div class="clear"></div>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div id="post-body">
                    <div id="post-body-content">
                        <div id="namediv" class="stuffbox">
                            <h3><label for="namediv"><?php _e( 'Chain editable data' ) ?></label></h3>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr valign="top">
                                            <td class="first"><?php _e( 'Assigned to Corporation:' ); ?></td>
                                            <?php  $users = get_users(array('role' => 'corporate'));?>
                                            <td>
                                                <select name="corporate_id" id="corporate_id">
                                                    <?php  
                                                    if($chain->corporate_id == 0 ) {?>
                                                        <option selected value="0">Not Assigned</option>
                                                        <?php 
                                                    }
                                                    $selected = '';
                                                    foreach($users as $user) { 
                                                        if($user->ID == $chain->corporate_id ) {
                                                            $selected = 'selected';
                                                        }?>
                                                        <option <?php echo $selected; ?> value="<?php echo $user->ID; ?>" ><?php echo $user->user_nicename; ?></option>                    
                                                        <?php 
                                                    } ?>
                                                </select>
                                            </td>                                             
                                        </tr>
                                        <tr valign="top">
                                            <td class="first"><?php _e( 'Parent ID:' ); ?></td>
                                            <td>
                                                <select name="parent_id" id="parent_id">
                                                    <?php  
                                                    if($chain->parent_id == 0 ) {?>
                                                        <option selected value="0">Not Assigned</option>
                                                        <?php 
                                                    }
                                                    $selected = '';
                                                    global $wpdb;
                                                    $a_chain_ids = $wpdb->get_col(" SELECT ID FROM $wpdb->chains WHERE active = true");
                                                    
                                                    foreach($a_chain_ids as $a_chain_id) { 
                                                        if( $a_chain_id == $chain->corporate_id ) {
                                                            $selected = 'selected';
                                                        }?>
                                                        <option <?php echo $selected; ?> value="<?php echo $a_chain_id; ?>" ><?php echo $a_chain_id; ?></option>                    
                                                        <?php 
                                                    } ?>
                                                </select>(Only active chains are listed)
                                            </td>  
                                        </tr>                   
                                    </tbody>
                                </table>
                                <br />
                            </div>
                        </div>
                    

                    <?php
                    
                    ?>
                    <input type="hidden" name="c" value="<?php echo esc_attr($chain->ID) ?>" />
                    <input name="referredby" type="hidden" id="referredby" value="<?php echo esc_url(stripslashes(wp_get_referer())); ?>" />
                    <?php wp_original_referer_field(true, 'previous'); ?>
                    <input type="hidden" name="noredir" value="1" />

                    </div>
                </div>
            </div>
        </div>
    </form>
        
 <?php
}




/*******************************************************************************************************************************************************
*
* Add Chains Page
*
********************************************************************************************************************************************************/
function gds_add_chains() {
    //get only corporate users - requires that this role already exists
	$users = get_users(array('role' => 'corporate'));

?>
	<div class="wrap">
        <h2> Add Chains </h2>

		<?php 

		if (isset($_POST['createchains'])) {

			gds_add_n_chains( $_POST['nb_chains'], $_POST['corporate'] );	
            $nb_chains = $_POST['nb_chains']; ?>		

		    <div id="message" class="updated">
				<p><strong><?php printf( _n( '%s chain was created.', '%s chains were created.', $nb_chains ), $nb_chains );?></strong></p>	
			</div>
        <?php 
		} ?>

		<form method="post" class="" action="">
            <input type="hidden" name="createchains" value="1" />
            <label for="nb_chains">Number of Chains you want to add</label>
            <input name="nb_chains" id="nb_chains" type="number" min="1" value="1" />
            <label for="corporate">Assign corporate account</label>
            <select name="corporate" id="corporate">
                <option selected value="0">None</option>
            	<?php 
            	foreach($users as $user) { ?>
					<option value="<?php echo $user->ID?>"><?php echo $user->user_nicename?></option>
            		
            	<?php } ?>
            </select>	
            <input type="submit" class="button-primary" value="Create">
        </form>
       
	</div>
<?php
}


/*******************************************************************************************************************************************************
*
* Assign Corporate Page
*
********************************************************************************************************************************************************/
function gds_assign_corporate(){ 
    $users = get_users(array('role' => 'corporate'));
    ?>
    <div class="wrap">
        <h2> Assign Corporate ID</h2>

        <?php 
        if (isset($_POST['assignchains'])) {

            $result = gds_update_chain_corporate_range( $_POST['lower_id'], $_POST['higher_id'], $_POST['corporate'] ); ?>
            <div id="message" class="updated">
                <?php
                if(false === $result) { ?>
                    <p><strong>Assignment failed.</strong></p> 
                    <?php
                } else {?>  
                    <p><strong>Assignment was succesful.</strong></p> 
                    <?php 
                }?>       
            </div>
        <?php 
        } ?>

        <form method="post" class="" action="">
            <input type="hidden" name="assignchains" value="1" />
            <p>Choose the range of ids for the Chains</p> 
            <label for="lower_id">From ID</label>
            <input name="lower_id" id="lower_id" type="number" min="1" value="1" /> <br />
            <label for="higher_id">To ID</label>
            <input name="higher_id" id="higher_id" type="number" min="1" value="1" /> <br />
            <label for="corporate">Assign corporate account</label>
            <select name="corporate" id="corporate">
                <option selected value="0">None</option>
                <?php 
                foreach($users as $user) { ?>
                    <option value="<?php echo $user->ID?>"><?php echo $user->user_nicename?></option>
                    
                <?php } ?>
            </select>   
            <input type="submit" class="button-primary" value="Assign">
        </form>
       
    </div>
    <?php
}

/*******************************************************************************************************************************************************
*
* Export Page
*
********************************************************************************************************************************************************/

if ( is_admin() ) {
    if ($_GET['page'] == "export-chains") {

        function gds_export_chains_onload() { 
            if (isset($_POST['savecsv'])) { 
                if( (int)$_POST['lower_id'] > (int)$_POST['higher_id']) {
                    $redirect_to = add_query_arg('failed', '1', wp_get_referer());
                    wp_safe_redirect($redirect_to);
                    exit;
                } else {
                    gds_createcsv($_POST['lower_id'] , $_POST['higher_id']);
                    exit;
                }
            }
            elseif(isset($_GET['all'])) {
                gds_createcsv();
                exit;
            }
        }
        add_action( 'admin_menu', 'gds_export_chains_onload' );
    }
}

function gds_export_chains() { ?>
    <div class="wrap">
        <h2> Export Chains</h2>
        <?php 
        if(isset($_GET['failed'])) { ?>
            <div id="message" class="error">
                <p><strong>Range incorrectly selected. "From ID" must be smaller or equal to "To ID".</strong></p>
                <?php $redirect_to = remove_query_arg(array('failed'), wp_get_referer());?>
                <p><a href="<?php echo $redirect_to;?>">Try Again</a></p>
            </div>    
            <?php        
        } else {
            if (isset($_POST['exportconfirm'])) {
                $result = gds_set_exported_range( $_POST['lower_confirm'], $_POST['higher_confirm']); 
                if(!is_bool($result)) { ?>
                    <div id="message" class="updated">
                        <p><strong>Chains were flagged as exported.</strong></p>
                    </div>
                    <?php
                }
            }
            ?>

            <form method="post" class="" action="">
                <input type="hidden" name="savecsv" value="1" />
                <input type="hidden" name="showconfirm" value="1" />
                <p>Choose the range of ids for the Chains</p> 
                <label for="lower_id">From ID</label>
                <input name="lower_id" id="lower_id" type="number" min="1" value="<?php $lo = isset( $_POST['lower_confirm']) ?  $_POST['lower_confirm'] : 1 ; echo $lo;?>" /> <br />
                <label for="higher_id">To ID</label>
                <input name="higher_id" id="higher_id" type="number" min="1" value="<?php $hi = isset( $_POST['higher_confirm']) ?  $_POST['higher_confirm'] : 1 ; echo $hi;?>" /> <br />
               
                <input type="submit" class="button-primary" value="Export">
            </form>

            <h3>Or</h3>
            <p><a href="<?php echo admin_url( 'admin.php?page=export-chains&all=1');?>">Export all unexported Chains</a></p>

            <div id="message" class="updated">
                <form method="post" class="" action="">
                    <input type="hidden" name="exportconfirm" value="1" />
                    <input type="hidden" name="lower_confirm" value="0" />
                    <input type="hidden" name="higher_confirm" value="0" />
                    <p><strong>After saving the export file, please confirm that the export was done correctly so the chains can be flagged as exported.</strong>            
                    <input type="submit" class="button-primary" value="Confirm Export"></p>     
                </form>
            </div>           
            <?php  
        } ?>
    </div> 
    <?php
}
?>