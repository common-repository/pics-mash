jQuery(document).ready(function($) {

	$('#ps-ajax').submit(function(){
	
		
		$('#feedback').html('<div class="loading"><img src="' + window.loadingImg + '" alt="" title="Adding pics" /><br />Adding pics...</div>').fadeIn(1000);
			
		data = {
			action: 'picsmash_get_all_pics'
		};
		
		$.post(ajaxurl, data, function(response){
			
			$('#feedback').html(response);
			
		});
		
	
		return false;
	});
	
	

	
	

 
});