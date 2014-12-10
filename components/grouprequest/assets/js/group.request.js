(function ($){
	$(document).ready(function(){
		$('#autocomplete').on("click", 'li', function(evt){
			$('#input-user').val($(this).data('username'));
			$('#autocomplete').html('');
			$('#autocomplete').addClass('hidden');
		});		
	});
	
	$('#result').on('ajaxBeforeSend', function(){
		$('#autocomplete').html('');
		$('#autocomplete').addClass('hidden');
	})

}(window.jQuery));