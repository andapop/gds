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

     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'ID':
            case 'date_created':
            case 'leader_id':
            case 'parent_id':
            case 'passcode':
                return $item->$column_name;
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recomended. Custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. 

     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){
        
        //Build row actions
        $actions = array(
          //  'edit'      => sprintf('<a href="?page=%s&action=%s&chian=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&chain=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
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
            'ID'     => 'Chain ID',
            'passcode'    => 'Passcode',
            'leader_id'  => 'Leader ID/Corpoarate Account ID',
            'parent_id' => 'Parent_ID',
            'date_created' => 'Date Created'
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
            'ID'     => array('ID',true),     //true means its already sorted
            'leader_id' => array('leader_id',false),
            'parent_id'  => array('parent_id',false),
            'date_created'  => array('date_created',false)
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
            'assign_leader' => 'Assign Leader'
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
            wp_die('Delete chains!');
        }
        if( 'assign_leader'===$this->current_action() ) {
            wp_die('Do the assigment!');
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
        $total_items = intval ($wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->chains ")) );         
        $total_pages = ceil($total_items/$per_page);
        
        
        /**
        * Start constructing the query for the data that will be displayed  
        */

        $the_query = "SELECT * FROM $wpdb->chains";

        /* Checks for sorting input */       
        
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'ID'; 
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'ASC';  

        $the_query .= " ORDER BY ". $orderby ." ". $order;
        
        /* Add pagination details */

        $offset = ($current_page-1)*$per_page;
        $the_query .= ' LIMIT ' .$per_page . ' OFFSET ' . $offset;

        $chains = $wpdb->get_results($the_query);
        
        
        /**
         * REQUIRED. Add *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $chains;
        
        
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

