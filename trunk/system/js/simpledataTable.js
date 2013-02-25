(function($){

$.dataTable = function(e){

  this.table = e;
  this.init();
  $(e).data('instance',this);
  return this;
};

var $sdt = $.dataTable;

$sdt.fn = $sdt.prototype = {
        dataTable: '0.1.0'
};

$sdt.fn.extend = $sdt.extend = $.extend;

$sdt.fn.extend({
 
/**
*	Se encarga de inicializar el control, y verificar que existen los componentes
*	necesarios
*/
  init: function(){
//	 debug("Inicializando dataTable",this.table);
	 var divContainer = document.createElement("div");
	 $(divContainer).addClass("dataTable");
	 $(this.table).wrap(divContainer);
	 
	 $(this.table).find("thead").addClass("ui-widget-header");
	 
	 $(this.table).find("tfoot").addClass("ui-widget-content");
	 $(this.table).find("tfoot").empty();
	 
	 $(this.table).attr("cellspacing",0);
	 
	 $(this.table).find("tbody tr:not(.model)").remove();
	 
	 $(this.table).find("tbody tr.model").addClass("ui-widget-content");
	 
	 $(this.table).data("params",{ajax:'ajax'});
	 
	 this.loadData();
	 
	 
	 
  },
  /**
  *
  */
  loadData: function(){
  	var obj = this;
	 var a = $(this.table).find("thead a.onload");
	 var url = $(a).attr("href");
	 if ( url == undefined ) throw '<a class="onload" root="" href="" />';
	 var root = $(a).attr("root");
	 
	 var params = $.extend({},$(this.table).data("params"));
	 
	 var limit = $(this.table).attr("data-limit") || 10;
	 var offset = $(this.table).attr("data-offset") || 0;
	 params.o = offset;
	 params.l = limit;
	 obj.offset = offset;
	 obj.limit = limit;



    var _params = $(a).attr("data-params");
    if ( (_params !=undefined) && ( _params != ""  ) ){
        _params = _params.split(","); // SEPARACION ENTRE PARAMETROS
        for ( i = 0; i < _params.length ; i ++ ){
            var key = _params[i].split(":");
            var value = key[1];
            key = key [0];
            eval("params."+key+" ='"+value+"'");
        }
    }
    
    
    $(this.table).find("thead th a.order[data-order][data-orderby]").each(function(){
    
    	var key = "orderby";
    	var value = $(this).attr("data-orderby");
    	eval("params."+key+" ='"+value+"'");
		params.order = $(this).attr("data-order");
    });
    
	this.getData(url, params,  root );
  },
  
  pagination: function(c, tfoot){
	  if ( tfoot == undefined ) return false;
	
		var obj = this;

		var page = Math.floor( obj.offset / obj.limit ) +1;
		var npages = Math.floor(c / obj.limit) + ( (c % obj.limit > 0 )?1:0 );

		$(tfoot).empty();
		var tr = document.createElement("tr");
		var td = document.createElement("td");
		$(td).attr("colspan",$(obj.table).find("th").length);
			
		$(tfoot).append(tr);
		$(tr).append(td);
	
		$(td).css("text-align","right");
				
		if ( npages > 10 ){
			var sel = document.createElement("select");

			for( var i = 1; i <= npages; i++){
				var a = document.createElement("option");
				$(a).val(i)
				$(a).text(i);
				$(a).bind('click', this.changePage);
				if ( page == i ) $(a).attr("selected","selected");
				$(sel).append(a);
			}		

			$(td).append(sel);
		}else{
			for( var i = 1; i <= npages; i++){
				var a = document.createElement("a");
				$(a).attr("href","#");
				$(a).text(i);
				$(a).css("display","inline-block");
				$(a).css("padding","2px");
				$(a).bind('click', this.changePage);
				$(td).append(a);
			}		
		}
				

		
  },
  currentPage: function(){
  		var obj = this;  	
  		
  		//console.log($(obj.table).data("Page"));	
  		var page = $(obj.table).data("Page");
  		return page;
  },  
  changePage: function(e){

	e.preventDefault();
	var obj = $(this).parents("table.dataTable").eq(0);
	var page = $(this).text();
	
	 $(obj).data("Page",page);	 
	  
     $(obj).trigger("changePage",page);	  
	  

	 $(this).parent().children().removeClass("activate");
	 $(this).addClass("activate");
		
	 var offset = ( page - 1 ) * 10;
	 $(obj).attr("data-offset",offset);

	 $(obj).dataTable("loadData");
	  
  },
  
  parseData: function(response, root){
  		var obj = this;
  		$(obj.table).find("tbody tr:not(.model)").remove();

		var c = response.count;		
		obj.pagination(c, $(obj.table).find("tfoot"));
  		
  		var r = eval("response."+root);

		$.each(r, function(){
			var tr = obj.newTR();
			obj.loadTR(tr,this);
		});
		
		$(this.table).trigger('dataloaded');
  },
  
  getData:function(url, params,  root){
	 var obj = this;
		obj.offset = params.o;

	getData( { url:url, data:params, dataType:'json' }, function(response){

			obj.parseData(response, root );
		});
  },
  
  newTR: function(){
	var tr = $(this.table).find("tbody tr.model").clone();
	$(tr).removeClass("model");
	$(this.table).find("tbody").append(tr);
	return tr;
  },
  
  setValue: function (td, data){ 
	  
		if ( $(td).hasClass("dt-date") ){ 
		  
		data = intDateParse(td,data);
		}
	$(td).text(data);  
  },
    
  loadTR : function(tr,data){ 
	  var obj = this;
		$.each(data, function(i){  
			var td = $(tr).find("td."+i); 
			obj.setValue(td,data[i]);
			//$(td).text(data[i]);
		});
		$(tr).data('data',data);
		$(this.table).trigger('rowloaded', {tr:tr, obj:data} );
		
  },
  
  orderBy : function(){
  
  }
  
});

$.fn.dataTable = function(o){
  if ( typeof(o) == 'string' ){ 
	 var ins = $(this).data('instance'); 
	 args = Array.prototype.slice.call(arguments, 1);
	 if ( typeof(eval("ins."+o) ) == 'function' ){
	 	eval("ins."+o+"()");
	 }
	 //ins[o].apply(ins,args);
  }else{
	 $(this).each(function(){
		new $sdt(this,o);
	 });
  }
};

/** Activando funcion borrar ***/

$("table.dataTable tbody tr a.delete").live('click', function(e){
	e.preventDefault();
	
	var tr = $(this).parents("tr").eq(0);
	var table = $(tr).parents("table.dataTable").eq(0);
	
	var url = $(this).attr("href");
	var data = {};
	data.ajax = 'ajax';
	
	var obj = $(tr).data("data");
	
	var pk = $(tr).attr("data-pk");
	eval('data.'+pk+'= obj.'+pk);
	
	$(table).trigger('delete', { url:url, data:data, obj:obj, tr:tr });
	
});

/********************************/
/*** Activando funcion editar ***/


$("table.dataTable tbody tr a.edit").live('click', function(e){
	e.preventDefault();
	
	var tr = $(this).parents("tr").eq(0);
	var table = $(tr).parents("table.dataTable").eq(0);
	
	var url = $(this).attr("href");
	var data = {};
	data.ajax = 'ajax';
	
	var obj = $(tr).data("data");
	
	var pk = $(tr).attr("data-pk");
	eval('data.'+pk+'= obj.'+pk);
	
	$(table).trigger('edit', { url:url, data:data, obj:obj, tr:tr });
	
	
});

/**
* Habilita la Funcion de ordenar.
* Los TH deben ser enlaces de la clase order
*/

$("table.dataTable thead th a.order").live('click', function(e){
	e.preventDefault();

	var table = $(this).parents("table.dataTable").eq(0);

	var order = $(this).attr("data-order") || undefined;
	$(table).find("thead th a.order").removeAttr("data-order");
	
	if ( order == undefined ) order = 'asc';
	else if ( order == 'asc' ) order = 'desc';
	else if ( order == 'desc' ) order = undefined;
	
	if ( order == undefined ) $(this).removeAttr("data-order");
	else $(this).attr("data-order", order);


	$(table).dataTable("loadData");
	
	
});

})(jQuery);
