jQuery(document).ready(function() {

	var action= '';

	jQuery('select[name="action"]').change(function(){
		action = jQuery(this).val();


		if (action == 'delete'){
			jQuery('#doaction').click(function(){
				return confirm('Are you sure you want to delete the wristbands?\nThis will permanently delete them!');
			});
		}
	});

	jQuery('div.row-actions span.delete a').click(function(){
				return confirm('Are you sure you want to delete this item?\nThis will permanently delete item!');
	});

	jQuery('#delete-action a.submitdelete').click(function(){
				return confirm('Are you sure you want to delete this item?\nThis will permanently delete item!');
	});

	
		
});