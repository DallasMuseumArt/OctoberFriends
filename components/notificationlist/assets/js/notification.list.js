$(function(){
	
	$('.markReadAll').on('click', function(){
		var $el = $(this);
		$.request('onMarkAllRead', {
			update: {'@default': '#notifications-list'},
		});
	});
	

	$('.markRead').on('click', function(){
		var $el = $(this);
		var options = {
			data: { id : $(this).data('id') },
	    	success: function(data){

	    		// Remove or just remove unread class
	    		if($el.data('remove')){
	    			$el.parent('div').remove();	    			
	    		}else{
	    			$el.parent('div').removeClass('notification-unread');
	    		}
	    		
	    		// Let's finish by calling default success function 
	    		this.success(data);
	    		
	    	}
		};
		
		if ($('.notification').length == 1 && $el.data('remove')){
			delete options['success'];
			options['update'] = {'@default': '#notifications-list'};
		}
				
		$.request('onMarkRead', options);
	});
	
	
});