<?php 
if (isset($_POST['continuechain'])) {
	$args = array();
	$args['chain_id'] = isset($_POST['chain_id']) ? $_POST['chain_id'] : 0 ;
	$args['wb_number'] = isset($_POST['wb_number']) ? $_POST['wb_number'] : '' ;
	$args['story1'] = isset($_POST['story1']) ? $_POST['story1'] : '' ;
	$args['story2'] = isset($_POST['story2']) ? $_POST['story2'] : '' ;
	$args['city'] = isset($_POST['city']) ? $_POST['city'] : '' ;
	$args['state'] = isset($_POST['state']) ? $_POST['state'] : '' ;
	$args['country'] = isset($_POST['country']) ? $_POST['country'] : '' ;

	$current_user = wp_get_current_user();
	$args['user_id'] = $current_user->ID;

	$result = gds_add_wristband($args);	
    
    if(is_bool($result) && ($result)) {
    	$message = 'Chain succesfully continued. <a href="'. get_bloginfo('url').'/your-chains/view-chains">Veiw Your Story &rarr;</a>' ;
    	$class= 'success';
    	
    } else {
    	$message = $result;
    	$class = 'error';
    }
    ?>

    <div id="message" class="<?php echo $class;?>">
		<p><?php echo $message?></p>	
	</div>

<?php 
} ?>


<form method="post" class="clearfix" action="">
	<input type="hidden" name="continuechain" value="1" />
	<ol>   
	    <li>
		    <?php echo gds_chain_ids_dropdown(); ?>
		</li>
		<li>
		    <label for="wb_number">Wristband Number</label>		    
			<input name="wb_number" id="wb_number" type="number" required value="<?php echo isset($_POST['wb_number'] )? $_POST['wb_number'] : ''; ?>" />
		</li>
		<li>
			<label for="city">City</label>
			<input name="city" id="city" type="text" value="<?php echo isset($_POST['city'] )? $_POST['city'] : ''; ?>"/>
		</li>
		<li>
			<label for="state">State</label>
			<input name="state" id="state" type="text" value="<?php echo isset($_POST['state'] )? $_POST['state'] : ''; ?>" />
		</li>
		<li>
			<label for="country">Country</label>
			<input name="country" id="country" type="text" value="<?php echo isset($_POST['country'] )? $_POST['country'] : ''; ?>"/>
		</li>
		<li>
			<label for="story1">What inspired you to start this good deed chain?</label>
			<textarea name="story1" id="story1" rows = "4" required="" placeholder="Example: My family and I were driving to New York, and the stranger ahead of us paid for our toll.  What a nice surprise!  We can't wait to pass this good deed along to someone else!"><?php if(isset($_POST['story1'])) echo $_POST['story1'];?></textarea>
		</li>
		<li>
			<label for="story2">What do you want others in this chain to know?</label>
			<textarea name="story2" id="story2" rows="4" required="" placeholder="Example: Thanks for being a part of this.  Let's go out there and make the world a better place!"><?php if(isset($_POST['story2'])) echo $_POST['story2'];?></textarea>
        </li>
    </ol>	
    <input type="submit" class="button" value="Continue Chain">
</form>
<?php 
?>