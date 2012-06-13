<?php 
if (isset($_POST['startchain'])) {
	$args = array();
	$args['chain_id'] = isset($_POST['chain_id']) ? $_POST['chain_id'] : 0 ;
	$args['passcode'] = isset($_POST['passcode']) ? $_POST['passcode'] : '' ;
	$args['story1'] = isset($_POST['story1']) ? $_POST['story1'] : '' ;
	$args['story2'] = isset($_POST['story2']) ? $_POST['story2'] : '' ;
	$args['city'] = isset($_POST['city']) ? $_POST['city'] : '' ;
	$args['state'] = isset($_POST['state']) ? $_POST['state'] : '' ;
	$args['country'] = isset($_POST['country']) ? $_POST['country'] : '' ;

	$result = gds_start_chain($args);	
    
    if(is_bool($result) && ($result))	{
    	$message = 'Chain succesfully created. <a href="'. get_bloginfo('url').'/your-chains/view-chains">Veiw Your Chains &rarr;</a>' ;
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

<div class="form-details">
        <p><strong>IMPORTANT</strong>: Choose the wristband with the specially marked tag and enter the attached code. This is your wristband to keep. Fill out the form below, click submit, and REMOVE THE TAG FROM YOUR WRISTBAND ONLY. Then do a good deed for someone and pass the lanyard, instruction card, and remaining wristbands to the next person.</p>
        <p>Keep checking back to watch your chain grows, see where it travels, and read about what happens along the way! Remember, you can also chat, post a message, or leave a thank you note for other chain members in our Social Forum. </p>
</div>
<form method="post" class="clearfix" action="">
	<input type="hidden" name="startchain" value="1" />
	<ol>   
	    <li>
		    <label for="chian_id">Chain ID</label>
		    <input name="chain_id" id="chain_id" type="number" value="<?php echo isset($_POST['chain_id'] )? $_POST['chain_id'] : 0; ?>" required />
		</li>
		<li>
		    <label for="passcode">Letter Code or Mixed Number/Letter Code</label>
			<input name="passcode" id="passcode" type="text" required value="<?php echo isset($_POST['passcode'] )? $_POST['passcode'] : ''; ?>" />
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
    <input type="submit" class="button" value="Start Chain">
</form>
<?php 
?>