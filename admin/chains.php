<?php

add_action('admin_menu', 'gds_chains_menu');

function gds_chains_menu() {
	add_menu_page('Chains', 'Chains', 'administrator', 'chains', 'gds_chains' , '', '27' ); 
	add_submenu_page('chains', 'All Chains', 'All Chains', 'administrator', 'chains', 'gds_chains');
	add_submenu_page('chains', 'Add Chains', 'Add Chains', 'administrator', 'add-chains', 'gds_add_chains');
}

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

        $redirect_to = remove_query_arg( array( 'activated', 'deactivated', 'exported','assignedcorporate', 'corporate_id' ,'ids','activated_chain', 'deactivated_chain', 'updated_chain', 'chaction', 'c', '_wpnonce', 'edit_chaction'), wp_get_referer() );
      
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
            $redirect_to = remove_query_arg( array( 'activated', 'deactivated', 'exported','assignedcorporate' ,'ids', 'corporate_id','activated_chain', 'deactivated_chain', 'updated_chain'), wp_get_referer() );
            wp_safe_redirect($redirect_to);
            exit;
        } 

        $activated = $deactivated = $assignedcorporate = 0;

        $redirect_to = remove_query_arg( array( 'activated', 'deactivated', 'exported','assignedcorporate', 'corporate_id' ,'ids','activated_chain', 'deactivated_chain', 'updated_chain'), wp_get_referer() );
        
        if($pagenum > 1)
            $redirect_to = add_query_arg( 'paged', $pagenum, $redirect_to );
        
        if('export' == $doaction) {
                $exported++;
                // gds_export_chains($chain_ids);

        }
        else {
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
        }

        if ( $activated )
            $redirect_to = add_query_arg( 'activated', $activated, $redirect_to );
        if ( $deactivated )
            $redirect_to = add_query_arg( 'deactivated', $deactivated, $redirect_to );
        if ( $exported )
            $redirect_to = add_query_arg( 'exported', $exported, $redirect_to );
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

        if ( isset($_REQUEST['activated']) || isset($_REQUEST['deactivated']) || isset($_REQUEST['exported']) || isset($_REQUEST['assignedcorporate']) || isset($_REQUEST['activated_chain']) || isset($_REQUEST['deactivated_chain']) || isset($_REQUEST['updated_chain'])  ) {
            $activated  = isset( $_REQUEST['activated']  ) ? (int) $_REQUEST['activated']  : 0;
            $deactivated  = isset( $_REQUEST['deactivated']  ) ? (int) $_REQUEST['deactivated']  : 0;
            $exported   = isset( $_REQUEST['exported']   ) ? (int) $_REQUEST['exported']   : 0;
            $assignedcorporate  = isset( $_REQUEST['assignedcorporate']  ) ? (int) $_REQUEST['assignedcorporate']  : 0;
            $activated_chain  = isset( $_REQUEST['activated_chain']  ) ? (int) $_REQUEST['activated_chain']  : 0;
            $deactivated_chain  = isset( $_REQUEST['deactivated_chain']  ) ? (int) $_REQUEST['deactivated_chain']  : 0;
            $updated_chain  = isset( $_REQUEST['updated_chain']  ) ? (int) $_REQUEST['updated_chain']  : 0;
            $corporate_id  = isset( $_REQUEST['corporate_id']  ) ? (int) $_REQUEST['corporate_id']  : 0;
            

            if ( $activated > 0 || $deactivated > 0 || $exported > 0 || $assignedcorporate > 0|| $activated_chain > 0 || $deactivated_chain > 0 || $updated_chain > 0 ) {
                if ( $activated > 0 )
                    $messages[] = sprintf( _n( '%s chain activated', '%s chains activated', $activated ), $activated );

                if ( $deactivated > 0 ) 
                    $messages[] = sprintf( _n( '%s chain deactivated.', '%s chains deactivated.', $deactivated ), $deactivated );

                if ( $exported > 0 ) 
                     $messages[] = sprintf( _n( '%s chain exported.', '%s chains exported.', $exported ), $exported );

                if ( $assignedcorporate > 0 ) 
                     $messages[] = sprintf( _n( 'Chain was assigned to corporate id %s', 'Chains were assigned to corporate id %s', $assignedcorporate ), $corporate_id); 
                    // $messages[] .= sprintf( ' %d' , $corporate_id); 
                if ( $activated_chain > 0 )
                    $messages[] = sprintf( __( 'Chain with id %s was activated'), $activated_chain );
                
                if ( $deactivated_chain > 0 )
                    $messages[] = sprintf( __( 'Chain with id %s was deactivated'), $deactivated_chain ); 

                if ( $updated_chain > 0 ) 
                     $messages[] = sprintf( __( 'Chain with id %s was updated'), $updated_chain );         
                   
               

                echo '<div id="moderated" class="updated"><p>' . implode( "<br/>\n", $messages ) . '</p></div>';
            }
        } ?>

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

function gds_add_chains() {

    //to do: just get corporate account users
	$users = get_users();

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
                                            <td class="first"><?php _e( 'Corporate ID:' ); ?></td>
                                            <td><input type="text" name="corporate_id" size="30" value="<?php echo esc_attr( $chain->corporate_id ); ?>" tabindex="1" id="corporate_id" /></td>                                             
                                        </tr>
                                        <tr valign="top">
                                            <td class="first"><?php _e( 'Parent ID:' ); ?></td>
                                            <td><input type="text" name="parent_id" size="30" value="<?php echo esc_attr( $chain->parent_id ); ?>" tabindex="2" id="parent_id" /></td>  
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



?>