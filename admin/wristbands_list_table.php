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

     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'wb_number':
            case 'chain_id':
            case 'user':
            case 'stories':
            case 'approved':
                return $item->$column_name;
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recomended. Custom column method and is responsible for what
     * is rendered in any column with a speciffic name/slug

     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (both stories)
     **************************************************************************/
    function column_stories($item){
        
        //Build row actions
        $actions = array(
            'approve'    => sprintf('<a href="?page=%s&action=%s&wristband=%s">Approve</a>',$_REQUEST['page'],'approve',$item->ID),
            'delete'    => sprintf('<a href="?page=%s&action=%s&wristband=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
            'edit'    => sprintf('<a href="?page=%s&action=%s&wristband=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID)
        );
        
        //Return the Sories contents
        return sprintf('<h4>Story 1</h4><p>%1$s</p><h4>Sory 2</h4><p>%2$s</p>%3$s',
            /*$1%s*/ $item->story1,
            /*$2%s*/ $item->story2,
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_wb_number($item){
        //Return the Wristband # and id
        return sprintf('%1$s<span style="color:silver"> (id: %2$s)</span>',
            /*$1%s*/ $item->wb_number,
            /*$2%s*/ $item->ID
        );
    }    

    function column_user($item){

        global $wpdb;
        $user = get_user_by('id', $item->user_id);

        //Return the Wristband # and id
        return sprintf('%1$s <span style="color:silver">(id: %2$s)</span><p class="location"><span style="color:silver">From: </span><br />%3$s, %4$s <br /> %5$s</p>',
            /*$1%s*/ $user->user_nicename,
            /*$2%s*/ $item->user_id,
            /*$3%s*/ $item->city, 
            /*$4%s*/ $item->state,
            /*$5%s*/ $item->country
        );
    }    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td>
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label
            /*$2%s*/ $item->ID               //The value of the checkbox should be the record's id
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
            'approved' => 'Is Approved'
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
            'chain_id' => array('chain_id', false)
        );
        return $sortable_columns;
    }
   
    
    /** ************************************************************************
     * Optional. Define bulk actions.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
            'approve' => 'Approve/Unapprove'
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. Handle for bulk actions 
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Will Delete wristbands!');
        }
        if( 'approve'===$this->current_action() ) {
            wp_die('Will Approve wristbands!');
        }
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

        global $wpdb;

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 20;
        
        
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
         * Optional. Handle bulk actions.
         */
        $this->process_bulk_action();
        
        /**
         * REQUIRED for pagination. 
         */
        $current_page = $this->get_pagenum();
        $total_items = intval ($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->wristbands ")) );         
        $total_pages = ceil($total_items/$per_page);
        
        
        /**
        * Start constructing the query for the data that will be displayed  
        */

        $the_query = "SELECT * FROM $wpdb->wristbands";

        /* Checks for sorting input */       
        
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; 
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'ASC';  

        $the_query .= " ORDER BY ". $orderby ." ". $order;
        
        /* Add pagination details */

        $offset = ($current_page-1)*$per_page;
        $the_query .= ' LIMIT ' .$per_page . ' OFFSET ' . $offset;



        $wristbands = $wpdb->get_results($the_query);
        
        /**
         * REQUIRED. Add *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $wristbands;
        
        
        /**
         * REQUIRED. Register pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                    
            'total_pages' => $total_pages  
        ) );
    }
    
}

