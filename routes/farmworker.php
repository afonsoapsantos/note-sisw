<?php 
	
	##Rotas do usuário Funcionario fazenda
	use \Sisweb\PageFarmworker;
	use \Sisweb\Model\User;
	use \Sisweb\Model\farmWorker;
	use \Sisweb\Model\Request;
	use \Sisweb\Model\File;
	use \Sisweb\Model\Media;

	$app->get("/farmworker", function(){
		User::validateFarmWorker();

		$page = new PageFarmworker();
		$page->setTpl("index");
	});

 ?>