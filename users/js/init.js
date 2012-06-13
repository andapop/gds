jQuery(document).ready(function() {

	var id= '';

	jQuery('select[name="chain_id"]').change(function(){
		id = jQuery(this).val();
		_href = jQuery('#show-chain').attr("href");
		jQuery('#show-chain').attr("href", _href +"/" + id);
	});
		
});