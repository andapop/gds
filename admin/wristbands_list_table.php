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
class GDS_Wristband_List_Table extends WP_List_Table {
    
     
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;

        
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'wristband',     //singular name of the listed records
            'plural'    => 'wristbands',    //plural name of the listed records
            'ajax' => true,        //does this table support ajax?
        ) );
        
     
    }
    
    
    /** ************************************************************************
     * Recomended. Method called when the parent class can't find a method
     * specifically build for a given column.

     * @param array $wristband A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($wristband, $column_name){
        switch($column_name){
            case 'wb_number':
            case 'chain_id':
            case 'user':
            case 'stories':
            case 'approved':
            case 'date_claimed':
                return $wristband->$column_name;
            default:
                return print_r($wristband,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recomended. Custom column method and is responsible for what
     * is rendered in any column with a speciffic name/slug

     * @see WP_List_Table::::single_row_columns()
     * @param array $wristband A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (both stories)
     **************************************************************************/
    function column_stories($wristband) {
        global $wristband_status;
        $the_wristband_status = $wristband->approved; // yes/no

        if ( current_user_can('edit_posts')) {
            $url = "admin.php?page=wristbands&w=$wristband->ID";

            $approve_url = esc_url( wp_nonce_url(  $url ."&wbaction=approvewristband", "approvewristband_$wristband->ID" ) );
            $unapprove_url = esc_url( wp_nonce_url(  $url ."&wbaction=unapprovewristband", "unapprovewristband_$wristband->ID" ) );
            $edit_url = esc_url( wp_nonce_url(  $url ."&edit_wbaction=editwristband", "editwristband_$wristband->ID" ) );
            $delete_url = esc_url( wp_nonce_url(  $url ."&wbaction=deletewristband", "deletewristband_$wristband->ID" ) );
        }

        printf(__('<h4>Story 1</h4><p>%1$s</p><h4>Sory 2</h4><p>%2$s</p>'),
            /*$1%s*/ $wristband->story1,
            /*$2%s*/ $wristband->story2
        );
        
       if (current_user_can('edit_posts')) {
            // preorder it: Approve | Edit | Delete
            $actions = array(
                'approve' => '', 'unapprove' => '',
                'edit' => '',
                'delete' => ''
            );

            if ( $wristband_status && 'all' != $wristband_status ) { // not looking at all wristbands
                if ( 'approved' == $wristband_status )
                    $actions['unapprove'] = "<a href='$unapprove_url' title='" . esc_attr__( 'Unapprove this wristband' ) . "'>" . __( 'Unapprove' ) . '</a>';
                else if ( 'unapproved' == $wristband_status )
                    $actions['approve'] = "<a href='$approve_url' title='" . esc_attr__( 'Approve this wristband' ) . "'>" . __( 'Approve' ) . '</a>';
            } else {
                $actions['approve'] = "<a href='$approve_url' title='" . esc_attr__( 'Approve this wristband' ) . "'>" . __( 'Approve' ) . '</a>';
                $actions['unapprove'] = "<a href='$unapprove_url' title='" . esc_attr__( 'Unapprove this wristband' ) . "'>" . __( 'Unapprove' ) . '</a>';
            }

            $actions['edit'] = "<a href='$edit_url' >" . __( 'Edit' ) . '</a>';
            $actions['delete'] = "<a href='$delete_url'>" . __( 'Delete' ) . '</a>';

            $actions = apply_filters( 'wristband_row_actions', array_filter( $actions ), $wristband );

            $i = 0;
            echo '<div class="row-actions">';
            foreach ( $actions as $action => $link ) {
                ++$i;
                ( ( ( 'approve' == $action || 'unapprove' == $action ) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';


                echo "<span class='$action'>$sep$link</span>";
            }
            echo '</div>';
        }


        
    }

    function column_wb_number($wristband){
        //Return the Wristband # and id
        return sprintf('%1$s<span class="silver"> (id: %2$s)</span>',
            /*$1%s*/ $wristband->wb_number,
            /*$2%s*/ $wristband->ID
        );
    }    

    function column_user($wristband){

        global $wpdb;
        $user = get_user_by('id', $wristband->user_id);

        //Return the Wristband # and id
        return sprintf('%1$s <span class="silver">(id: %2$s)</span><p class="location"><span class="silver">From: </span><br />%3$s, %4$s <br /> %5$s</p>',
            /*$1%s*/ $user->user_nicename,
            /*$2%s*/ $wristband->user_id,
            /*$3%s*/ $wristband->city, 
            /*$4%s*/ $wristband->state,
            /*$5%s*/ $wristband->country
        );
    }    

    function column_approved($wristband){
        if($wristband->approved) {
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
     * @param array $wristband A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td>
     **************************************************************************/
    function column_cb($wristband){
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%1$s" />',
            /*$1%s*/ $wristband->ID               //The value of the checkbox should be the record's id
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
            'wb_number'     => 'Wristband # <br/>(Sort by ID)',
            'chain_id'    => 'Chain ID',
            'user'  => 'Added by',
            'stories'  => 'Stories',
            'approved' => 'Is Approved',
            'date_claimed' => 'Date Claimed'
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
            'wb_number' => array('ID', true),     //true means its already sorted
            'chain_id' => array('chain_id', false),
            'date_claimed' => array('date_claimed', false)
        );
        return $sortable_columns;
    }
   
    

    /** ************************************************************************
     * Optional. Content of a single row. 
     **************************************************************************/

    function single_row( $an_item ) {
        global $wristband;

        $wristband = $an_item;
        if($wristband->approved){
            $the_wristband_class = 'wristband approved';
        } else {
             $the_wristband_class = 'wristband unapproved';
        }
        

        echo "<tr id='wristband-$wristband->ID' class='$the_wristband_class'>";
        echo $this->single_row_columns( $wristband );
        echo "</tr>\n";
    }


    /** ************************************************************************
     * Optional. Define bulk actions.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {

        global $wristband_status;

        $actions = array();
        if ( in_array( $wristband_status, array( 'all', 'approved' ) ) )
            $actions['unapprove'] = __( 'Unapprove' );
        if ( in_array( $wristband_status, array( 'all', 'unapproved' ) ) )
            $actions['approve'] = __( 'Approve' );
        $actions['delete'] = __( 'Delete' );
        
        return $actions;
    }
    
    

    /** ************************************************************************
     * Optional. Get views
     **************************************************************************/

    function get_views() {
        global $wristband_status;        

        $status_links = array();
        $stati = array(
                'all' => _nx_noop('All', 'All', 'wristbands'), // singular not used
                'unapproved' => _n_noop('Pending <span class="count">(<span class="pending-count">%s</span>)</span>', 'Pending <span class="count">(<span class="pending-count">%s</span>)</span>'),
                'approved' =>_nx_noop('Approved', 'Approved', 'wristbands')
            );

        $link = 'admin.php?page=wristbands';
        foreach ( $stati as $status => $label ) {
            $class = ( $status == $wristband_status ) ? ' class="current"' : '';

            $link = add_query_arg( 'wristband_status', $status, $link );
           
            $status_links[$status] = "<a href='$link'$class>" . sprintf(
                translate_nooped_plural( $label, $this->get_num_wristbands($status) ),
                number_format_i18n( $this->get_num_wristbands($status) )
            ) . '</a>';
        }

        $status_links = apply_filters( 'wristband_status_links', $status_links );
        return $status_links;
    }

    function get_num_wristbands($status = 'all') {
        global $wpdb;

        switch ($status) {
            case 'unapproved' : 
                    $approved = false;
                    break;
            case 'approved' : 
            default : $approved = true; break;
        }

    
        $num_wristbands = intval($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->wristbands WHERE approved = %s" , $approved ) )); 

        return $num_wristbands;
    }


    function get_per_page( $wristband_status = 'all' ) {
        $wristbands_per_page = 20;
        $wristbands_per_page = apply_filters( 'per_page', $wristbands_per_page, $wristband_status );
        return $wristbands_per_page;
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

        global $wpdb, $wristband_status, $search ;

        $wristband_status = (isset( $_REQUEST['wristband_status'] )) ? $_REQUEST['wristband_status'] : 'all';
        if ( !in_array( $wristband_status, array( 'all', 'unapproved', 'approved' ) ) )
            $wristband_status = 'all';

        $search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';

        //$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        $wristbands_per_page = $this->get_per_page($wristband_status);

        $status_map = array(
            'unapproved' => false, //unapproved wristbands
            'approved' => true  //approved wristbands
        );

        /**
         * REQUIRED for pagination. 
         */
        $current_page = $this->get_pagenum();
        $total_items = intval ($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->wristbands ")) );         
        //$total_pages = ceil($total_items/$wristbands_per_page);       
        
        
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
            
        
        /**
        * Start constructing the query for the data that will be displayed  
        */

        $the_query = "SELECT * FROM $wpdb->wristbands";

        /* Check for staus */
        $status = isset( $status_map[$wristband_status] ) ? $status_map[$wristband_status] : $wristband_status;

        $has_where = false;
        if(is_bool($status) ) {
            $the_query .= " WHERE approved = '".$status. "'" ;
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
                            OR wb_number LIKE '%".$search."%'
                            OR chain_id LIKE '%".$search."%'
                            OR story1 LIKE '%".$search."%'
                            OR story2 LIKE '%".$search."%'
                            OR city LIKE '%".$search."%'
                            OR state LIKE '%".$search."%'
                            OR country LIKE '%".$search."%'" . $s_sufix;
        }

        $total_items_query = $the_query;

        /* Checks for sorting input */       
        
        $orderby = (isset($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; 
        $order = (isset($_REQUEST['order'])) ? $_REQUEST['order'] : 'DESC';  

        $the_query .= " ORDER BY ". $orderby ." ". $order;
        
        /* Add pagination details */

        $offset = ($current_page-1)*$wristbands_per_page;
        $the_query .= ' LIMIT ' . $wristbands_per_page . ' OFFSET ' . $offset;

        $wristbands = $wpdb->get_results($the_query);
        
        /**
         * REQUIRED. Add *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $wristbands;
        
        
        /**
         * REQUIRED. Register pagination options & calculations.
         */

        $total_wristbands = $wpdb->get_results($total_items_query);
        $this->set_pagination_args( array(
            'total_items' => count($total_wristbands),                  
            'per_page'    => $wristbands_per_page,
        ) );
    }

    
}

