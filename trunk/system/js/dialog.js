	dialog = function(obj)
	{
		var title = '';
		title = $(obj).attr("title");
		if ( title == undefined ) title = '';		
		var resizable = $(obj).attr("resizable") || 'false';
		resizable = ( resizable == 'true' )?true:false;
		var width = 'auto';
		if ( $(obj).attr("width") != undefined ) width = $(obj).attr("width");
		
		$(obj).dialog(
		{
			autoOpen:false,
			modal:'modal',
			draggable:true,
			minHeight:'50px',
			width:width,
			//height:'auto',
			resizable: resizable,
			overlay: {opacity:1, background:'black'},
			open:function(){
				if ( $(obj).hasClass("notitle") ) $(obj).parent().find(".ui-dialog-titlebar").hide();
				$(obj).find(".firstelement").focus();
				var f_open = "open_"+$(obj).attr("id");
				if ( eval("typeof("+f_open+")") == 'function' ) { eval(f_open+"(this)"); }
				$(obj).trigger("open");
			},
			close:function(){
				$(obj).trigger("close");
			}
			
		});
	
	};


	msgBox = function(text, title, fafter)
	{	
		$("div.msgbox").each(function() {$(this).remove();});

		var div = document.createElement('div');
		$(div).addClass("msgbox dialog");
		$(div).css("padding","15px");
		$(div).css("padding-left","50px");
		$(div).css("padding-top","20px");
		$(div).css("text-align","justify");
		$(div).css("text-indent","0px");
		
	
		$(div).html(text);

		$("body").append(div);
	
		$(div).dialog({
				autoOpen: true,
				modal:true,
				title:title,
				position:'center',
				overlay: {opacity:1, background:'black'},
				buttons:{
					"OK":function(){	
								$(div).dialog("close");
								if ( typeof(fafter) == 'function' ) fafter(true);
							}
						},
				close: function(event, ui){
						$(div).remove();
						if ( typeof(fafter) == 'function' ) fafter(false);
					 },
				open: function(event, ui){
					if ( title == undefined || title == '' ){ 
						$(div).parent().find(".ui-dialog-titlebar").hide();
					}
						$(div).parent().find("button.ui-state-default.ui-button").focus();
					}
			 }); //dialog

	
		
	};

	confirmBox = function(text, title,fn)
	{
		if ( typeof(fn) != 'function' ) fn = function(r) {} ;
	
		$("div.confirmBox").remove();
		
		var div = document.createElement('div');
		$(div).addClass("confirmBox dialog");
		$(div).css("padding","15px");
		$(div).css("padding-left","50px");
		$(div).css("padding-top","20px");
		$(div).css("text-align","justify");
		$(div).css("text-indent","0px");
		
		$(div).html(text);		
		
		$("body").append(div);

		$(div).dialog({
					autoOpen: true,
					modal:true,
					title:title,
					overlay: {opacity:1, background:'black'},
					buttons:{
						"OK":function(){ $(this).remove(); fn(true); },
						"CANCEL":function(){ $(this).remove(); fn(false); }
						},
					close: function(event, ui){	fn(false);	$(this).remove(); },
					open: function(event, ui){	
						if ( title == undefined || title == '' ){ 
							$(div).parent().find(".ui-dialog-titlebar").hide();
						}	
						$(this).focus(); 
					}
						
		}); //dialog
	};

$(document).ready(function(){

	$("div.dialog").each(function()
	{
		dialog(this);

		$(this).find("input.submit").addClass("ui-priority-primary ui-corner-all");
		$(this).find("input.reset").addClass("ui-priority-secondary ui-corner-all");
	});
	
	$("a[dialog]").live('click',function(e)
	{
		e.preventDefault();
		
		var frm = $(this).attr("dialog");

		if ( $(frm).length > 0 ) $(frm).dialog("open");
	});


	$(".accordion").each(function()
	{	
		$(this).accordion({ clearStyle: true,collapsible: true,active:false });

		//accordion('activate',false);
	});

});
