
$(document).ready(function(){
	$(window).resize(function(){
		$(".fullscreen").each(function(){
		
			$(this).width(
				$(this).parent().width() -
				$(this).parent().find("#menu").width() -
				20
				);
				
			$(this).height($(this).parent().height() - 30 );
		});
	});
	
	$(".fullscreen").each(function(){
		
			$(this).width(
				$(this).parent().width() -
				$(this).parent().find("#menu").width() -
				20
				);
				
			$(this).height($(this).parent().height() - 30 );
		});
});
