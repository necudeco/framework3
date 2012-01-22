$(document).ready(function(){

	fillSelect = function(sel, val){
		var href = $(sel).attr("data-href");
		var params = {};
		params.ajax = 'ajax';
		
		var keyname = $(sel).attr("data-keyname");
		var valuename = $(sel).attr("data-valuename");
		
		getData( { url:href, data:params }, function(r){
		
				$(sel).find("option").remove();
				
				$.each(r.data, function(){
					
					var opt = document.createElement("option");
					$(opt).val( eval('this.'+keyname) );
					$(opt).text( eval('this.'+valuename) );
					
					$(sel).append(opt);
				});
			
				if ( val != undefined ) $(sel).val(val);
			
			});	
	}
	
	$("select.fillSelect").each(function(){
		fillSelect(this);
	});

});
