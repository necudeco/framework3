<?php

$PHP=<<<EOT
<style>
.form div label {
	width: 150px !important;
}
</style>

<div id="dlg$modelName" class="dialog" title="$title">

<form class="form ajax" action="index.php/$moduleName/$controllerName/save">

EOT;

foreach( $fields as $k => $d ){
$label = ucwords($k);
$PHP.=<<<EOT

	<div class="field">
		<label>$label</label>
		<input class="textbox" type="text" name="$k" value="" />
	</div>
EOT;

}

$PHP.=<<<EOT

    <div>
			<input type="hidden" class="static" name="ajax" value="ajax" />
		    <input type="hidden" name="id$modelName" value="-1" />
		    <input class="submit right" type="submit" value="Save"  style="margin-right:115px;" />
    </div>

</form>

</div>
EOT;

?>
