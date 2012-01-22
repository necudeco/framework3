$(document).ready(function(){

$("form.validate").submit(function(e){
	e.preventDefault();
	debug("FORM VALIDATE");
	var frm = this;
	$(frm).find(".wrong").removeClass("wrong");
	$(frm).find(".required").each(function(){
	
	validate = {};
	
	
	var control = this;
	
	if ( $(control).hasClass("combobox") ){
	
	}
	
	if ( $(control).val() == '' ){
		$(control).parents("div").eq(0).addClass("wrong");
	}
	
	
	});
	
	if ( $(frm).find(".wrong").length > 0 ){
		e.stopImmediatePropagation();
		return false;
	}
	
	return true;
});

});