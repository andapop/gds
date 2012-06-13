<?php 

$wristband_id = (int)get_query_var('wristband_id');
$wristband = gds_get_wristband_for_id($wristband_id);

$current_user = wp_get_current_user();
$my_id = (int)$current_user->ID;


if ($wristband_id != 0 ) {
	if($my_id == $wristband->user_id) {
	
		if (isset($_POST['editwristband'])) {	

			$args = array('ID' =>  $wristband_id,  
                        'story1' => $_POST['story1'],
                        'story2' => $_POST['story2'],
                        'city' => $_POST['city'],
                        'state' => $_POST['state'],
                        'country' => $_POST['country'],
                        'approved' => false
				);


			$result = gds_update_wristband($args);
		    
		    if(is_bool($result))	{
		    	$message = 'Edit failed.  <a href="'. get_bloginfo('url').'/your-chains/view-chains">Veiw the Chain &rarr;</a>';
		    	$class = 'error';
		    } else {		    	
		    	$message = 'Story succesfully updated. <a href="'. get_bloginfo('url').'/your-chains/view-chains">Veiw the Chain &rarr;</a>';
		    	$class= 'success';
		    }
		    ?>

		    <div id="message" class="<?php echo $class;?>">
				<p><?php echo $message?></p>	
			</div>

			<?php 
		}
		// pretty title
		if ((int)$wristband->wb_number == 1) {?>
			<h1> Editing Chain <?php echo $wristband->chain_id;?></h1>
			<?php 
		} else { ?>
			<h1> Editing Wristband #<?php echo $wristband->wb_number;?> from Chain <?php echo $wristband->chain_id;?></h1>
			<?php 
		}?>
		<form method="post" class="clearfix" action="">
			<input type="hidden" name="editwristband" value="1" />
			<ol>   
				<li>
					<label for="city">City</label>
					<input name="city" id="city" type="text" required value="<?php echo isset($_POST['city'] )? $_POST['city'] : $wristband->city; ?>"/>
				</li>
				<li>
					<label for="state">State</label>
					<input name="state" id="state" type="text" required value="<?php echo isset($_POST['state'] )? $_POST['state'] : $wristband->state; ?>" />
				</li>
				<li>
					<label for="country">Country</label>
					<input name="country" id="country" type="text" required value="<?php echo isset($_POST['country'] )? $_POST['country'] : $wristband->country; ?>"/>
				</li>
				<li>
					<label for="story1">What inspired you to start this good deed chain?</label>
					<textarea name="story1" id="story1" rows = "4" required=""><?php echo isset($_POST['story1'] )? $_POST['story1'] : $wristband->story1; ?></textarea>
				</li>
				<li>
					<label for="story2">What do you want others in this chain to know?</label>
					<textarea name="story2" id="story2" rows="4" required=""><?php echo isset($_POST['story2'] )? $_POST['story2'] : $wristband->story2; ?></textarea>
		        </li>
		    </ol>
		    <p class="attention">Your story will have to be reapproved after submitting the changes. Until it's approved it will only be visible to you.</p>
		    <input type="submit" class="button" value="Submit Changes">
		</form>
		<?php 
	} else { ?>
		<p>You do not have permission to edit this story. Choose one of your chains <a href="<?php echo get_bloginfo('url') ;?>/your-chains/view-chains">here</a>.</p> 
	<?php 
	}
} else { ?>		
		<p>Nothing chosen to be edited. Choose something <a href="<?php echo get_bloginfo('url'); ?>/your-chains/view-chains">here</a>.</p> 
	<?php 
	}
?>