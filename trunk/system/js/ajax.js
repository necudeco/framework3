// Global Var, it will access as cache
cache = new Array();
if ( typeof(translateString) == "undefined" ) translateString = {};
translateString.PLEASEWAIT = "Por favor espere un momento";

function getData(params, fsuccess, ferror){ 
	var dlgWait = document.createElement("div");
	var url;
	var defaults = {
		url: params.url || null,
		data: params.data || {ajax:'ajax'},
		dataType: params.dataType || 'json', // text json
		beforeSend: function(xhr,set){
			if ( params.cache != "true" ) return true;
			url = murmurhash3_32_gc(set.url,0);
			if ( cache[url] != undefined ){
				defaults.success( cache[url] );
				return false;
			}else{
				return true;
			}
	
		},
                dataFilter: function(data, type){ //console.log(data);
                    if ( type == 'json'){
                        var str = data;
                        str=str.replace(/\\'/g,'\'');
                        str=str.replace(/\\"/g,'"');    
                        str=str.replace(/\\0/g,'\0');
                      //  str=str.replace(/\\\\/g,'\\');
                        return str;
                    }
                    return data;

                },
		success: function(response){ //console.log(response);
//			console.log(response, typeof(response));
			if ( typeof(response) == 'string' && dataType == 'json' ){
			
			}
			$(dlgWait).dialog('close');



			if ( response.code == 'ERROR') {
				if ( typeof(ferror) == 'function' ){
					eval('ferror(response)');
					return false;
				}
				var msg = $("div#"+response.message+".dialog"); 
				if ( $(msg).length > 0 ) $(msg).dialog('open');
			}else{ // Llamamos a la funcion correcta
				if ( params.cache == "true" ) cache[url] = response;
				if ( typeof(fsuccess) == 'function' ){
					eval('fsuccess(response.response)');
				}
			}
			
			if ( defaults.dataType == 'html' ){ 
				if ( typeof(fsuccess) == 'function' ){
					if ( params.cache == "true" )  cache[url] = response;
					eval('fsuccess(response)');
				}
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			var r = jqXHR.responseText;
			var o = eval('('+r+')');
			eval('fsuccess(o.response)');
			$(dlgWait).dialog('close');
		}
	};
	
	if ( params.noWaitDialog == undefined ){

	$(dlgWait).addClass("dialog")
		.append('<p style="width: 300px;">'+translateString.PLEASEWAIT+'</p>')
		.attr("id","dlgWait");
					
	$("div#dlgWait").remove();
	$(document).append(dlgWait);
	dialog(dlgWait); 
	$(dlgWait).dialog("open");
	$(dlgWait).parent().find(".ui-dialog-titlebar").hide();

	}

	$.ajax(defaults);

	
}
