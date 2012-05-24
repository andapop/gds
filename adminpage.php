<?php

add_action('admin_menu', 'gds_chains_menu');

function gds_chains_menu() {
	add_menu_page('Chains', 'Chains', 'administrator', 'chains', 'gds_list_chains' , '', '26' ); 
	add_submenu_page('chains', 'List Chains', 'List Chains', 'administrator', 'chains', 'gds_list_chains');
	add_submenu_page('chains', 'Add Chains', 'Add Chains', 'administrator', 'add-chains', 'gds_add_chains');
}

function gds_list_chains () {
	//Create an instance of our chain list class
    $chainListTable = new GDS_Chain_List_Table();

    //Fetch, prepare, sort, and filter our data
    $chainListTable->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Chains</h2>
        
        <form id="chain-filter" method="get">
            <!-- Ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Render the completed list table -->
            <?php $chainListTable->display() ?>
        </form>
        
    </div>

	</div>
<?php
}

function gds_add_chains() {


	$users = get_users();

?>
	<div class="wrap">
        <h2> Add Chains </h2>

		<?php 
		if (isset($_POST['createchains'])) {

			gds_add_n_chains( $_POST['nb_chains'], $_POST['leader'] );			

		echo '<div id="message" class="updated">

				<p><strong>'.$_POST['nb_chains'].' Chain(s) were added.</strong></p>	

			</div>';

		} ?>

		<form method="post" class="" action="">
            <input type="hidden" name="createchains" value="1" />
            <label for="nb_chains">Number of Chains you want to add</label>
            <input name="nb_chains" id="nb_chains" type="number" min="1" value="1" />
            <label for="leader">Assing a chain leader</label>
            <select name="leader" id="leader">
            	<?php 
            	foreach($users as $user) { ?>
					<option value="<?php echo $user->ID?>"><?php echo $user->user_nicename?></option>
            		
            	<?php } ?>
            	
            <input type="submit" class="button-primary" value="Create">
       
	</div>
<?php
}



?>