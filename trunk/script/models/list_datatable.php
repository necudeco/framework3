<?php

$PHP=<<<EOT
<style>
.form div label {
	width: 150px !important;
}
</style>

<script>
$(document).ready(function(){
    var table$modelName  = $("table#$modelName");
    var dlg$modelName    = $("div#dlg$modelName.dialog");
    
    $(table$modelName).dataTable();
    
    $(table$modelName).bind('delete', function(e,p){
        confirmBox("", "" , function(response){
            if(response == true){
                getData({ url:p.url, data:p.data }, function(response){
                        $(p.tr).remove();
                });
            }
	    });
    });
    
    $(table$modelName).bind('edit', function(e,p){
         
         $(dlg$modelName).dialog("open");
         loadForm($(dlg$modelName).find("form").get(0), p.obj);

    });
    
    $("a#add$modelName").click(function(e){
        e.preventDefault();
        
        $(dlg$modelName).dialog("open");
        var form = $(dlg$modelName).find("form").get(0);
        $(form).find("input.textbox").val(null);
        $(form).find("textarea").val(null);
        $(form).find("input[type=hidden]:not(.static)").val(null);
        
    });
    
    
    $(dlg$modelName).find("form").bind("success", function(){
        $(table$modelName).dataTable("loadData");
        $(dlg$modelName).dialog("close");
    });
});
</script>

<link href="css/$modelName/$controllerName/__list.css" type="text/css" rel="stylesheet" />
<script src="js/$modelName/$controllerName/__list.js" > </script>

<h2 class="moduleTitle"> </h2>

<a href="#" class="" id="add$modelName" ><img src="images/add.png" /></a>

<table class="dataTable" id="$modelName">
<thead>
<tr>
<th>
	<a href="index.php/$moduleName/$controllerName/list?" class="onload" root="data" />
</th>
EOT;

foreach( $fields as $k => $d ){
$label = ucwords($k);
$PHP.=<<<EOT

	<th>$label</th>
EOT;

}

$PHP.=<<<EOT
<th></th>
</tr>
</thead>
<tfoot>

</tfoot>
<tbody>
<tr class="model" data-pk="" >
	<td></td>
EOT;

foreach( $fields as $k => $d ){
$PHP.=<<<EOT

	<td class="$k"></td>
EOT;

}

$PHP.=<<<EOT
	<td>
		<a class="edit" href="$moduleName/$controllerName/save" > <img src="images/edit.png" /></a>
		<a class="delete" href="$moduleName/$controllerName/delete" > <img src="images/delete.png" /></a>
	</td>
</tr>
</tbody>
</table>

</div>

{include file="$moduleName/$filename/__form.html"}

EOT;

?>
