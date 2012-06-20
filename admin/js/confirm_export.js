jQuery(document).ready(function() {

	var lo = jQuery('input[name="lower_id"]').val();
	var hi = jQuery('input[name="higher_id"]').val();
	jQuery('input[name="lower_confirm"]').val(lo);
	jQuery('input[name="higher_confirm"]').val(hi);

	
	jQuery('input[name="lower_id"]').change(function(){
		jQuery('input[name="lower_confirm"]').val(jQuery(this).val());
	});

	jQuery('input[name="higher_id"]').change(function(){
		jQuery('input[name="higher_confirm"]').val(jQuery(this).val());
	});
		
});