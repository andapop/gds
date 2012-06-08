<?php

add_action('admin_menu', 'gds_wristbands_menu');

function gds_wristbands_menu() {
	add_menu_page('Wristbands', 'Wristbands', 'administrator', 'wristbands', 'gds_wristbands' , '', '28' ); 
}
function gds_prepare_status($status){
    if('approved' == $status) {
        $status = true;
    }elseif ('pending' == $status) {
        $status = false;
    }
    return $status;
}

function gds_wristbands(){
   
    //row action was performed    
    $action = isset($_REQUEST['wbaction']) ? $_REQUEST['wbaction'] : 0 ; 

    if($action) { 

        if ( isset($_REQUEST['w'])) {
            $wristband_id = (int)$_REQUEST['w']; 
        }elseif ( wp_get_referer() ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }   
    
        $nonce_action =$action.'_'.$wristband_id;
        
        if(! check_admin_referer($nonce_action)){
            exit;
        }

        $approved_wristband = $unapproved_wristband = $deleted_wristband = $do_edit = $updated_wristband = 0;

        $redirect_to = remove_query_arg( array( 'approved_wristband', 'unapproved_wristband', 'deleted_wristband', '$updated_wristband', 'approved', 'unapproved', 'deleted', 'ids', 'wbaction','w', '_wpnonce'), wp_get_referer() );
        $redirect_to = add_query_arg( 'paged', $pagenum, $redirect_to ); 
           

            switch ($action) {
                case 'approvewristband':
                    gds_set_wristband_status( $wristband_id, true );
                    $approved_wristband = $wristband_id;
                    break;
                case 'unapprovewristband':
                    gds_set_wristband_status( $wristband_id, false );
                    $unapproved_wristband = $wristband_id;
                    break;
                case 'deletewristband':
                    gds_delete_wristband( $wristband_id );
                    $deleted_wristband = $wristband_id;
                    break;
                case 'updatewristband':
                    $args = array( 'ID' =>  $wristband_id,  
                                'story1' => $_POST['story1'],
                                'story2' => $_POST['story2'],
                                'city' => $_POST['city'],
                                'state' => $_POST['state'],
                                'country' => $_POST['country'],
                                'approved' => (bool) $_POST['approved']
                                );
                    gds_update_wristband($args);                                 
                    $updated_wristband = $wristband_id;
                    break;
                case 'editwristband':
                    $do_edit++;            
                    break;
            }

        if ( $approved_wristband )
            $redirect_to = add_query_arg( 'approved_wristband', $approved_wristband, $redirect_to );
        if ( $unapproved_wristband )
            $redirect_to = add_query_arg( 'unapproved_wristband', $unapproved_wristband, $redirect_to );
        if ( $deleted_wristband )
            $redirect_to = add_query_arg( 'deleted_wristband', $deleted_wristband, $redirect_to );
        if( $updated_wristband ) {
            $redirect_to = add_query_arg( 'updated_wristband', $updated_wristband, $redirect_to );
        }

       
        if(!$do_edit){
            wp_safe_redirect( $redirect_to );
            exit;
        }
    } 

      

    //if we have to edit the wristband get the edit form, else show the list table
    if($do_edit) {
       gds_show_edit_wristband_form($wristband_id);
    } else { 
        gds_show_wristband_list();

    }
}

function  gds_show_wristband_list() {
    global $wpdb;
    $wristbandListTable = new GDS_Wristband_List_Table();    

    //Fetch, prepare, sort, and filter our data
    $wristbandListTable->prepare_items();
        
    ?>
    <div class="wrap">
            
        <div id="icon-users" class="icon32"><br/></div>
        <h2><?php echo __('Wristbands');

        if ( isset($_REQUEST['s']) && $_REQUEST['s'] )
             printf( '<span class="subtitle">' . sprintf( __( 'Search results for &#8220;%s&#8221;' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ) . '</span>' ); ?>

        </h2>
        <?php
        if ( isset( $_REQUEST['error'] ) ) {
            $error = (int) $_REQUEST['error'];
            $error_msg = '';
            switch ( $error ) {
                case 1 :
                    $error_msg = __( 'Oops, no wristband with this ID.' );
                    break;
                case 2 :
                    $error_msg = __( 'You are not allowed to edit wristbands.' );
                    break;
            }
            if ( $error_msg )
                echo '<div id="moderated" class="error"><p>' . $error_msg . '</p></div>';
        }

        if ( isset($_REQUEST['approved']) || isset($_REQUEST['deleted']) || isset($_REQUEST['unapproved']) || isset($_REQUEST['approved_wristband']) || isset($_REQUEST['unapproved_wristband']) || isset($_REQUEST['deleted_wristband']) || isset($_REQUEST['updated_wristband'])  ) {
            $approved  = isset( $_REQUEST['approved']  ) ? (int) $_REQUEST['approved']  : 0;
            $unapproved  = isset( $_REQUEST['unapproved']  ) ? (int) $_REQUEST['unapproved']  : 0;
            $deleted   = isset( $_REQUEST['deleted']   ) ? (int) $_REQUEST['deleted']   : 0;
            $approved_wristband  = isset( $_REQUEST['approved_wristband']  ) ? (int) $_REQUEST['approved_wristband']  : 0;
            $unapproved_wristband  = isset( $_REQUEST['unapproved_wristband']  ) ? (int) $_REQUEST['unapproved_wristband']  : 0;
            $deleted_wristband  = isset( $_REQUEST['deleted_wristband']  ) ? (int) $_REQUEST['deleted_wristband']  : 0;
            $updated_wristband  = isset( $_REQUEST['updated_wristband']  ) ? (int) $_REQUEST['updated_wristband']  : 0;
            

            if ( $approved > 0 || $deleted > 0 || $unapproved > 0 || $approved_wristband > 0 || $deleted_wristband > 0 || $unapproved_wristband > 0 || $updated_wristband > 0 ) {
                if ( $approved > 0 )
                    $messages[] = sprintf( _n( '%s wristband approved', '%s wristbands approved', $approved ), $approved );

                if ( $unapproved > 0 ) 
                    $messages[] = sprintf( _n( '%s wristband unapproved.', '%s wristbands unapproved.', $unapproved ), $unapproved );

                if ( $deleted > 0 ) 
                     $messages[] = sprintf( _n( '%s wristband permanently deleted.', '%s wristbands permanently deleted.', $deleted ), $deleted );

                if ( $approved_wristband > 0 )
                    $messages[] = sprintf( __( 'Wristband with id %s was approved'), $approved_wristband );
                
                if ( $unapproved_wristband > 0 )
                    $messages[] = sprintf( __( 'Wristband with id %s was unapproved'), $unapproved_wristband ); 

                if ( $deleted_wristband > 0 ) 
                     $messages[] = sprintf( __( 'Wristband with id %s was deleted'), $deleted_wristband ); 

                if ( $updated_wristband > 0 ) 
                     $messages[] = sprintf( __( 'Wristband with id %s was updated'), $updated_wristband );         
                   
               

                echo '<div id="moderated" class="updated"><p>' . implode( "<br/>\n", $messages ) . '</p></div>';
            }
        } ?>

        <?php $wristbandListTable->views(); ?>

           
        <form id="wristnbads-form" action="" method="get">

            <?php
            $wristband_status = $wpdb->escape( $_REQUEST['wristband_status'] );
             $wristbandListTable->search_box( __( 'Search Wristbands' ), 'wristband' ); ?>

            <input type="hidden" name="wristband_status" value="<?php echo esc_attr($wristband_status); ?>" />
            
            <input type="hidden" name="_total" value="<?php echo esc_attr( $wristbandListTable->get_pagination_arg('total_items') ); ?>" />
            <input type="hidden" name="_per_page" value="<?php echo esc_attr( $wristbandListTable->get_pagination_arg('per_page') ); ?>" />
            <input type="hidden" name="_page" value="<?php echo esc_attr( $wristbandListTable->get_pagination_arg('page') ); ?>" />

            <?php if ( isset($_REQUEST['paged']) ) { ?>
                <input type="hidden" name="paged" value="<?php echo esc_attr( absint( $_REQUEST['paged'] ) ); ?>" />
            <?php } ?>

            <!-- Ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

            <!-- Render the completed list table -->
            <?php $wristbandListTable->display() ?>

        </form>
    </div>
<?php 
}


function gds_show_edit_wristband_form($wristband_id ){

    global $wpdb;

    $wristband = gds_get_wristband_for_id($wristband_id);
    var_dump($wristband);
    $user = get_user_by('id', $wristband->user_id);
    ?>
    
            
        
        

    <form name="post" action="admin.php?page=wristbands" method="post" id="post">
    <?php wp_nonce_field('updatewristband_' . $wristband->ID) ?>
    <input type="hidden" name="wbaction" value='updatewristband' />

        <div class="wrap">
            <div id="icon-users" class="icon32"><br/></div>
            <h2><?php echo __('Edit Wristband'); ?></h2>

            <h3><?php printf(__('Wristband No.: %1$s / Chain ID: %2$s / User: %3$s'), $wristband->wb_number, $wristband->chain_id, $user->user_nicename ); ?></h3>

            <div id="poststuff" class="metabox-holder has-right-sidebar">

                <div id="side-info-column" class="inner-sidebar">
                    <div id="submitdiv" class="stuffbox" >
                        <h3><span class='hndle'><?php _e('Status') ?></span></h3>
                        <div class="inside">
                            <div class="submitbox" id="submitwristband">
                                <div id="minor-publishing">
                                    <div id="misc-publishing-actions">
                                        <div class="misc-pub-section" id="wristband-status-radio">
                                            <label class="approved"><input type="radio"<?php checked( $wristband->approved, '1' ); ?> name="approved" value="1" /><?php /* translators: comment type radio button */ _ex('Approved', 'adjective') ?></label><br />
                                            <label class="waiting"><input type="radio"<?php checked( $wristband->approved, '0' ); ?> name="approved" value="0" /><?php /* translators: comment type radio button */ _ex('Unapproved', 'adjective') ?></label><br />
                                        </div>
                                    </div> <!-- misc actions -->
                                    <div class="clear"></div>
                                </div>

                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                         <?php echo "<a class='submitdelete deletion' href='" . wp_nonce_url("admin.php?page=wristbands&w=$wristband->ID" . "&wbaction=deletewristband", "deletewristband_$wristband->ID" ). "'>" .  __('Delete Permanently' ) . "</a>\n"; ?>
                                    </div>
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
                            <h3><label for="namediv"><?php _e( 'Wristband location data' ) ?></label></h3>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr valign="top">
                                            <td class="first"><?php _e( 'City:' ); ?></td>
                                            <td><input type="text" name="city" size="30" value="<?php echo esc_attr( $wristband->city ); ?>" tabindex="1" id="city" /></td>                                             
                                        </tr>
                                        <tr valign="top">
                                            <td class="first"><?php _e( 'State:' ); ?></td>
                                            <td><input type="text" name="state" size="30" value="<?php echo esc_attr( $wristband->state ); ?>" tabindex="2" id="state" /></td>  
                                        </tr>
                                        <tr valign="top">
                                            <td class="first"><?php _e( 'Country:' ); ?></td>
                                            <td><input type="text" name="country" size="30" value="<?php echo esc_attr( $wristband->country ); ?>" tabindex="3" id="state" /></td>  
                                        </tr>                    
                                    </tbody>
                                </table>
                                <br />
                            </div>
                        </div>
                    <h3><label for="postdiv1"><?php _e( 'Story 1' ) ?></label></h3>
                    <div id="postdiv1" class="postarea">                        
                        <?php
                        $quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' );
                        wp_editor( $wristband->story1, 'story1', array( 'media_buttons' => false, 'tinymce' => false, 'quicktags' => $quicktags_settings ) );
                        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
                    </div>
                    <h3><label for="postdiv2"><?php _e( 'Story 2' ) ?></label></h3>
                    <div id="postdiv2" class="postarea">                        
                        <?php
                        $quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' );
                        wp_editor( $wristband->story2, 'story2', array( 'media_buttons' => false, 'tinymce' => false, 'quicktags' => $quicktags_settings ) );
                        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
                    </div>

                    <?php
                    
                    ?>
                    <input type="hidden" name="w" value="<?php echo esc_attr($wristband->ID) ?>" />
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