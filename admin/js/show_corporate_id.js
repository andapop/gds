jQuery(document).ready(function() {

	//jQuery('#chain-filter').append('<input type="hidden" name="corp_id" value="0" />');
	var action= '';

	jQuery('select[name="action"]').change(function(){
		action = jQuery(this).val();
		if (action == 'assign_corporate'){
			jQuery('#assign_corporate_id').show().insertAfter(this);
		}
	});

	/*jQuery('#corporate_ids').change(function(){ 
		var newval = jQuery(this).val();
		jQuery('input[name="corp_id"]').val(newval) ;
	}); */


		
});