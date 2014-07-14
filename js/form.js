function loadForm(frm, data){
		frm = $(frm).get(0); 
		if ( frm.tagName != 'FORM' ) return false;
		
		var obj; 
		
		$.each(data, function(k){
		  obj = $(frm).find("[name="+k+"]").get(0);
		  if ( obj == undefined ) return true;
				 
		  if ( obj.tagName == 'INPUT' ){
		  		
			 $(obj).val(data[k]);
		  }
		  
		  if ( obj.tagName == 'SELECT' ){
		  		
			 $(obj).val(data[k]);
		  }
			 
		  if ( $(obj).hasClass("dt-date") ){
			var val = data[k];
			val = intDateParse(obj,val);
				
			$(obj).val(val);
		}
		  
		  if ( obj.tagName == 'TEXTAREA' ){

			 $(obj).val("");
 			 $(obj).val(data[k]);
		  }
		});
		
  }
  

// Declaramos el objeto Global validate
validate = {};

validate.filters = {};
validate.filters.email = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;

validate.validateComponent = function(frm, ctrl){
	var value = $(ctrl).val();
	
	if ( value == '' ) return false;
	
	if ( $(ctrl).hasClass("nowhitespaces") && $.trim(value) == '' ) return false;
	
	if ( $(ctrl).filter('[min]') ) {
		if ( $(ctrl).filter(".autocomplete.combobox").length > 0 ){
			value = $(frm).find("input[name="+$(ctrl).attr("val")+"]").val();
		}
		if ( $(ctrl).attr("min") > value ) return false;
	}
	
	if ( $(ctrl).hasClass("email") && ! value.match(validate.filters.email) ) return false;

	return true;
};

validate.validateForm = function(frm){

	var controls = $(frm).find("input[type=password],input[type=text],textarea,select,input[type=checkbox]");
	
	// Solo actuamos sobre los que deben ser validados ( class required )
	controls = $(controls).filter(".required");
	
	//Asumimos que no hay error
	var isValid = true;
	$(frm).removeClass("error");
	$(controls).removeClass("error");
	$(frm).find("div.errormessage").hide();
	
	$(controls).each(function(){
		//Validamos el presente control
		var control = this;
		if ( ! validate.validateComponent(frm,control) ){
			isValid = false;
			$(control).addClass("error");
			$(frm).find("div.errormessage[for="+$(control).attr("name")+"]").show();
		}
	});

	if ( isValid == false ) $(frm).addClass("error");
	return isValid;
};
  
$(document).ready(function(){
	
	// Evitamos el molesto autocomplete del browser
	$("form").attr("autocomplete","off");
  
  	$("form.validate").each(function(){
  		$(this).validate();
  	});
  
  	$("form.ajax").bind('submit',function(e){
		e.preventDefault();

		var frm = this;
		
		if ( typeof(CKEDITOR) == 'object'){
			
  			for ( instance in CKEDITOR.instances ){
        		CKEDITOR.instances[instance].updateElement();
        	}
		}

		if ( $(frm).hasClass("validate") ){
			
			if ( ! $(frm).valid() ) return false;
		}
		
		if ( $(frm).hasClass("validable") ){
		  if ( ! validate.validateForm(frm) ) return false;
		}
		
		$(frm).find("textarea").each(function(){
			//$(this).val(escape($(this).val()));
		});
		
		var url = $(this).attr("action");
		var dataString = $(this).serialize();
		var dataType = $(this).attr("dataType") || 'json';

		getData( { url:url, data:dataString, dataType:dataType }, function(response){ 
			$(frm).trigger('success',response);
		});
		/*
		$.ajax({
		  url:url,
		  dataType:'json',
		  data:dataString,
		  success: function(response){
			 
		  }
		});
		*/
  }); // form.ajax
  

	/**
	 * Creacion de componente de upload file
	 * input: nombre del campo donde se coloca la URL del archivo
	 * limit: indica la cantidad de archivos que es permitido subir por formulario, undefined para ilimitado
	 * action: indica la URL donde se procesa la carga de archivos
	 * title: el texto que se carga en el boton de subir
	 */
//	$(".fileuploader").each(function(){  //console.log(this);
		
	document.fileuploader = function(fu){		
//		var fu = this;
		if ( fu == undefined ) return false;
		var inputname = $(fu).attr("input") || 'file';
		var limit = $(fu).attr("limit");
		var action = $(fu).attr("action");
		var button = $(fu).attr("button");
		var backgroundcolor = $(fu).attr("background-color") || "#800";
		
		if ( action == undefined ) {
			
			throw "Not defined action URL at fileuploader "+$(fu).attr("id");
		}
		
		
		
		new qq.FileUploaderBasic({
                element: fu,
                button: fu,
                action: $(fu).attr("action"),
                debug: true,
                buttonTitle: button,
                inputName: inputname,
                onSubmit: function(id, filename){
                	
                	$(fu).trigger("progress","");  //trigger agregado 08/03/2013 //DEPRECATED
                	$(fu).trigger("onSubmit",{element:fu});

                },
                onComplete: function(id, filename, response){
                		
//						
						if ( response.success == false ) {
							msgBox(response.errormessage);
							return false;
						}
												
						$(fu).trigger('itemLoaded',{element:fu, response: response.response.data});					
				}
			});
	
	}
	//});
	
	
	$(".fileuploader").each(function(){  document.fileuploader(this);  });
	
	
	if ( typeof(CKEDITOR)  == 'object' ){
			$("form textarea.rich").each(function(){
				var ta = this;
			CKEDITOR.replace(ta, {toolbar:
				[
					['Source'],
					['Bold','Italic','Underline'],
					['NumberedList','BulletedList'],
					['Image', 'Flash', 'Table'],
					['Format','Font','FontSize'],
					['TextColor','BGColor']
				],
				filebrowserUploadUrl: $(ta).attr("uploadURL")
				});
		});
	}

  
});
