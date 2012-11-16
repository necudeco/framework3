$(document).ready(function(){
  
  
  $("input.autocomplete").each(function(){
	 var inp = this;
	 console.log(this);
	 var defaultvalue = $(inp).val();
	 
	 var root = $(inp).attr("root");
	 var url = $(inp).attr("href");
	 var label = $(inp).attr("label") || 'label';
	 var value = $(inp).attr("val") || 'value';
	 
	 var hiddenvalue = $(inp).attr("hiddenvalue") || '';
	 var hiddenlabel = $(inp).attr("hiddenlabel") || '';
	 var displayhidden = ($(inp).attr("displayhidden")||false == "true")?true:false;
	 var hiddenattop = ($(inp).attr("hiddenattop")||false == "true")?true:false;
	 
	 var params = $(inp).attr("params");
	 var minLength = $(inp).attr("minlength") || 1;
	 if ( $(inp).hasClass("combobox")) minLength = 0;
	 if(params!=null)	params = params.split(":");

	 var _inp = document.createElement("input");
	 $(_inp).attr("type","hidden");
	 $(_inp).attr("name",value);
	 $(_inp).attr("value", hiddenvalue);
	 $(inp).after(_inp);
	 
	 
	 
	 $(inp).val(hiddenlabel);
	 
	 $(inp).autocomplete({
		 minLength: minLength,
		 source: function(req, fn){
		  var data = {};
		  data.ajax='ajax';
		  data.term = req.term; 
		  if(params!=null){
			  for ( i = 0; i < params.length ; i++ ){

			  	eval('data.'+params[i]+'= "'+$(inp).attr(params[i])+'"');
			  }
		  }
		  url = $(inp).attr("href");
	      label = $(inp).attr("label") || 'label';
	      value = $(inp).attr("val") || 'value';

		  $.ajax({url:url, data:data, dataType:'json', success: function(response){

			root = $(inp).attr("root");

			 if ( root == undefined ) r = response;
			 else r = eval('response.'+root);

			 $.each(r, function(){
				this.value = eval('this.'+label);
				this.id = eval('this.'+value);
			 });
			 
			 if ( displayhidden == true){
			 	if ( hiddenattop == true )
				 	r.unshift({id:hiddenvalue, value:hiddenlabel});
				 else
				 	r.push({id:hiddenvalue, value:hiddenlabel});
			}
			 
			 fn(r);
		  }});
		},
		select: function(e,ui){
		  var item = ui.item;
		  $(_inp).val(eval('item.'+value));
		  $(inp).trigger('change', item );
		  $(inp).trigger('selected', item );
		  
		},
		response: function(e, ui){
			/*var items = ui.content;
			if ( items.length == 0 ){
				items = [{label:'Agregar Paciente', value:'-1'}];
			}*/
		}
	 });
	 $(inp).val(defaultvalue);
  });
  
  
  $("input.combobox.autocomplete").each(function(){
     var inp = this;

		$(inp).attr("readonly","readonly");
	 var button = document.createElement("button");
	 $(button).attr("tabIndex",-1)
			  .attr("title","Show All Items")
			  .insertAfter(inp)
			  .button({
					icons:{
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
			  .height($(inp).height()+4)
			  .width(20)
			  .removeClass("ui-corner-all")
			  .addClass("ui-corner-right ui-button-icon")
			  .css("vertical-align","middle")
			  .click(function(e){
					e.preventDefault();
					if ( $(inp).autocomplete( "widget" ).is( ":visible" ) ) {
							$(inp).autocomplete( "close" );
					return;  
					}
					$(this).blur();
					$(inp).autocomplete("search","");
					$(inp).focus();
			  });
  });
  
});
