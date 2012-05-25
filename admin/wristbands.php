<?php

add_action('admin_menu', 'gds_wristbands_menu');

function gds_wristbands_menu() {
	add_menu_page('Wristbands', 'Wristbands', 'administrator', 'wristbands', 'gds_list_wristbands' , '', '28' ); 
}

function gds_list_wristbands () {
	//Create an instance of our chain list class
    $wristbandListTable = new GDS_Wristband_List_Table();

    //Fetch, prepare, sort, and filter our data
    $wristbandListTable->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Wristbands</h2>
        
        <form id="wristband-filter" method="get">
            <!-- Ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Render the completed list table -->
            <?php $wristbandListTable->display() ?>
        </form>
        
    </div>

	</div>
<?php
}





?>