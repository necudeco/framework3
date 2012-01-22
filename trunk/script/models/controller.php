<?php

$PHP=<<<EOT
<?php

class $controllerName extends ControllerBase{
	protected \$model = '$modelName';
	protected \$template = '$moduleName/index.html';
	
	public function indexAction(){
	
		\$this->view->assign('content', '$moduleName/$urlname/__list.html');
	}
}

?>
EOT;

?>
