<?php
/*
Inspired by Custom List Table Example Plugin by Matt Van Andel
Plugin URI: http://www.mattvanandel.com/
 * ========================================================================== */



/*************************** LOAD THE BASE CLASS *******************************/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/************************** CREATE A PACKAGE CLASS *****************************
 * Create a new list table package that extends the core WP_List_Table class.
 */
class GDS_Chain_List_Table extends WP_List_Table {
    
     
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;

        
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'chain',     //singular name of the listed records
            'plural'    => 'chains',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
     
    }
    
    
    /** ************************************************************************
     * Recomended. Method called when the parent class can't find a method
     * specifically build for a given column.

     * @param array $chain A singular chain (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($chain, $column_name){
        switch($column_name){
            case 'ID':
            case 'date_created':
            case 'leader_id':
            case 'corporate_id':
            case 'parent_id':
            case 'passcode':
            case 'date_exported':
            case 'active':
                return $chain->$column_name;
            default:
                return print_r($chain,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recomended. Custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. 

     * @see WP_List_Table::::single_row_columns()
     * @param array $chain A singular chain (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_ID ($chain) {        
        global $chain_status;
        $chain_active_status = $chain->active;
        $chain_export_status = $chain->exported;

        if ( current_user_can('edit_posts')) {
            $url = "admin.php?page=chains&c=$chain->ID";

            $activate_url = esc_url( wp_nonce_url(  $url ."&chaction=activatechain", "activatechain_$chain->ID" ) );
            $deactivate_url = esc_url( wp_nonce_url(  $url ."&chaction=deactivatechain", "deactivatechain_$chain->ID" ) );
            $edit_url = esc_url( wp_nonce_url(  $url ."&edit_chaction=editchain", "editchain_$chain->ID" ) );
        }

        printf('%d', $chain->ID);
        
        if (current_user_can('edit_posts')) {
            // preorder it: Activate | Deactivate | Edit 
            $actions = array(
                'activate' => '', 'deactivate' => '',
                'edit' => ''
            );

            if ( $chain_status) { 
                if ( $chain_active_status )
                    $actions['deactivate'] = "<a href='$deactivate_url' title='" . esc_attr__( 'Deactivate this chain' ) . "'>" . __( 'Deactivate' ) . '</a>';
                else if (!$chain_active_status)
                    $actions['activate'] = "<a href='$activate_url' title='" . esc_attr__( 'Activate this chain' ) . "'>" . __( 'Activate' ) . '</a>';
            }

            $actions['edit'] = "<a href='$edit_url' >" . __( 'Edit' ) . '</a>';
            
            $actions = apply_filters( 'chain_row_actions', array_filter( $actions ), $chain );

            $i = 0;
            echo '<div class="row-actions">';
            foreach ( $actions as $action => $link ) {
                ++$i;
                ( ( ( 'activate' == $action || 'deactivate' == $action ) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';


                echo "<span class='$action'>$sep$link</span>";
            }
            echo '</div>';
        }

    }

    function column_active($chain){
        if($chain->active) {
            return 'yes';
        } else {
            return 'no';
        }
    }    
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $chain A singular chain (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($chain) {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%1$s" />',
            /*$1%s*/ $chain->ID               //The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles.
     *    
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'ID'     => 'Chain ID',
            'passcode'    => 'Passcode',
            'leader_id'  => 'Leader ID',
            'corporate_id'  => 'Corpoarate Account ID',
            'parent_id' => 'Parent_ID',
            'date_created' => 'Date Created',
            'active' => 'Is Active',
            'date_exported' => 'Date Exported'
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. Register more columns to be sortable (ASC/DESC toggle)
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'ID'     => array('ID', true),     //true means its already sorted
            'leader_id' => array('leader_id', false),
            'corporate_id' => array('corporate_id', false),
            'parent_id'  => array('parent_id', false),
            'date_created'  => array('date_created', false),
            'date_exported'  => array('date_exported', false)
        );
        return $sortable_columns;
    }
    
    /** ************************************************************************
     * Optional. Content of a single row. 
     **************************************************************************/

    function single_row( $an_item ) {
        global $chain;

        $chain = $an_item;
        if($chain->active) {
            $the_chain_class = 'chain active';
        } else {
             $the_chain_class = 'chain inactive';
        }

        if(!$chain->exported) {
             $the_chain_class .= ' notexported';
        }
        
        echo "<tr id='chain-$chain->ID' class='$the_chain_class'>";
        echo $this->single_row_columns( $chain );
        echo "</tr>\n";
    }
    
    /** ************************************************************************
     * Optional. Define bulk actions.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        global $chain_status;

        $actions = array();
        if ( in_array( $chain_status, array( 'all', 'active','notexported', 'exported' ) ) )
            $actions['deactivate'] = __( 'Deactivate' );               
        if ( in_array( $chain_status, array( 'all', 'inactive', 'notexported', 'exported' ) ) )
            $actions['activate'] = __( 'Activate' );
        /*if ( in_array( $chain_status, array( 'all','active','notexported' ) ) )
            $actions['export'] = __( 'Export' );*/
        $actions['assign_corporate'] = __( 'Assign Corporate Account' );
        
        return $actions;

    }
    
    
    
    /** ************************************************************************
     * Optional. Get views
     **************************************************************************/

    function get_views() {
        global $chain_status;        

        $status_links = array();
        $stati = array(
                'all' => _nx_noop('All', 'All', 'chains'), // singular not used
                'inactive' => _n_noop('Inactive <span class="count">(<span class="pending-count">%s</span>)</span>', 'Inactive <span class="count">(<span class="pending-count">%s</span>)</span>'),
                'active' =>_nx_noop('Active', 'Active', 'chains'),
                'notexported' => _n_noop('Not Exported <span class="count">(<span class="pending-count">%s</span>)</span>', 'Not Exported <span class="count">(<span class="pending-count">%s</span>)</span>'),
                'exported' =>_nx_noop('Exported', 'Exported', 'chains')
            );

        $link = 'admin.php?page=chains';
        foreach ( $stati as $status => $label ) {
            $class = ( $status == $chain_status ) ? ' class="current"' : '';

            $link = add_query_arg( 'chain_status', $status, $link );
           
            $status_links[$status] = "<a href='$link'$class>" . sprintf(
                translate_nooped_plural( $label, $this->get_num_chains($status) ),
                number_format_i18n( $this->get_num_chains($status) )
            ) . '</a>';
        }

        $status_links = apply_filters( 'comment_status_links', $status_links );
        return $status_links;
    }

    function get_num_chains($status = 'all') {
        global $wpdb;
        $is_exp = $active = false;
        switch ($status) {
            case 'inactive' : 
                    $active = false;
                    break;
            case 'active' :
                    $active = true;                    
                    break; 
            case 'notexported' : 
                    $exported = false;
                    $is_exp = true;
                    break; 
            case 'exported' : 
                    $exported = true;
                    $is_exp = true;
                    break;            
        }

        if ($is_exp) {
            $num_chains = intval($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->chains WHERE exported = %s" , $exported ) )); 
        } else {
            $num_chains = intval($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->chains WHERE active = %s" , $active ) )); 
        }   

        return $num_chains;
    }


    function get_per_page( $chain_status = 'all' ) {
        $chains_per_page = 20;
        $chains_per_page = apply_filters( 'per_page', $chains_per_page, $chain_status );
        return $chains_per_page;
    }
    
    
    /** ************************************************************************
     * REQUIRED! Prepare your data for display.
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {

        global $wpdb, $chain_status, $search ;

        $chain_status = (isset( $_REQUEST['chain_status'] )) ? $_REQUEST['chain_status'] : 'all';
        if ( !in_array( $chain_status, array( 'all', 'active', 'inactive', 'exported', 'notexported' ) ) )
            $chain_status = 'all';

        $search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';

        //$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        $chains_per_page = $this->get_per_page($chain_status);

        $status_map = array(
            'inactive' => 1, //inactive chains
            'active' => 2,  //active chains
            'notexported' => 3, //not exported chains
            'exported' => 4  //exported chains
        );

               
        
        
        /**
         * REQUIRED. Define column headers.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Build an array to be used by the class for column headers. 
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        $current_page = $this->get_pagenum();
        
        /**
        * Start constructing the query for the data that will be displayed  
        */

        $the_query = "SELECT * FROM $wpdb->chains";

        /* Check for staus */
        $status = isset( $status_map[$chain_status] ) ? $status_map[$chain_status] : $chain_status;

        $has_where = false;
        if(is_integer($status) ) {
            if($status == 1) 
                $the_query .= " WHERE active = false" ;
            if($status == 2) 
                $the_query .= " WHERE active = true" ;
            if($status == 3) 
                $the_query .= " WHERE exported = false" ;
            if($status == 4) 
                $the_query .= " WHERE exported = true" ;
            $has_where = true;
        }

        /*add search */
        if($search != '') {
            if ($has_where) {
                $s_prefix = " AND ( ";
                $s_sufix = " )";

            }
            else {
                $s_prefix = " WHERE ";
                $s_sufix = "";
            } 

            $the_query .= $s_prefix.  "ID LIKE '%".$search."%' 
                            OR date_created LIKE '%".$search."%'
                            OR leader_id LIKE '%".$search."%'
                            OR corporate_id LIKE '%".$search."%'
                            OR parent_id LIKE '%".$search."%'
                            OR date_exported LIKE '%".$search."%'". $s_sufix;
        }

        $total_items_query = $the_query;
        /* Checks for sorting input */       
        
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; 
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC';  

        $the_query .= " ORDER BY ". $orderby ." ". $order;

        
        /* Add pagination details */

        $offset = ($current_page-1)*$chains_per_page;
        $the_query .= ' LIMIT ' .$chains_per_page . ' OFFSET ' . $offset;

       

        $chains = $wpdb->get_results($the_query);
        
        
        /**
         * REQUIRED. Add *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $chains;
        
        
        /**
         * REQUIRED. Register pagination options & calculations.
         */
        $total_chains = $wpdb->get_results($total_items_query);

        $this->set_pagination_args( array(
            'total_items' => count($total_chains),                  
            'per_page'    => $chains_per_page 
        ) );
    }

    function show_select_corporate_id() {
       
        //will change this to only choose corporate users
        $users = get_users(array('role' => 'corporate')); 
        ?>

        <select name="assign_corporate_id" id="assign_corporate_id" style="display: none;" >
            <option selected value="0">None</option>
         <?php 
           foreach($users as $user) { ?>
            <option value="<?php echo $user->ID?>"><?php echo $user->user_nicename?></option>
                    
         <?php } ?>
        </select>

    <?php 
    }
    
}

