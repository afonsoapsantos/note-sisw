<?php 

	##Rotas para Técnicos
	use \Sisweb\PageTechnical;
	use \Sisweb\Model\User;
	use \Sisweb\Model\Technician;
	use \Sisweb\Model\Customer;
	use \Sisweb\Model\Order;
	use \Sisweb\Model\Media;
	use \Sisweb\Model\File;
	
	$app->get("/technical", function(){
		User::validateTechnical();
		$page = new PageTechnical();
		$page->setTpl("index");
	});

	$app->get("/technical/orders", function(){
		User::validateTechnical();
		$page = new PageTechnical();
		$page->setTpl("orders",[
			"orders"=>Order::listOrdersOpen(),
			"error"=>Order::getError(),
			"success"=>Order::getSuccess(),
			"fileerror"=>File::getError(),
			"filesuccess"=>File::getSuccess()
		]);
	});

	$app->get("/technical/orders/medias/:idorder", function($idorder){
		User::validateTechnical();
		$page = new PageTechnical();
		$page->setTpl("orders-medias",[
			"mediasOrders"=>Media::listAllByOrder($idorder),
			"error"=>Order::getError(),
			"success"=>Order::getSuccess()
		]);
	});

	$app->get("/technical/media/download/:idmedia", function($idmedia){
		User::validateTechnical();

		try {
			$media = new Media();
			$media->setidmedia($idmedia);
			$media->get();
			$caminho = $media->gettxlocalmedia();
			$name = $media->gettxnamemedia();
			$link = $caminho.DIRECTORY_SEPARATOR.$name;
			$file = new File();
			exec('wmic COMPUTERSYSTEM Get UserName', $user);
			$parse = parse_url($user[1]);
			$nameuser = basename($parse['path']);
			$file->download($link,$nameuser);
			header("Location: /technical/orders");
		} catch (Exception $e) {
			File::setError($e->getMessage());
			header("Location: /technical/orders");
		}
			exit;
	});

	$app->get("/technical/orders/start/:idorder", function($idorder){
		User::validateTechnical();
		$order = new Order();
		$order->setidorder($idorder);
		$order->getOrder();
		$technician = new Technician();
		$technician->getByUser($_SESSION[User::SESSION]["pkuser"]);

		$data = date("Y-m-d");
		$hora = date("H:i:s");

		$page = new PageTechnical();
		$page->setTpl("orders-start",[
			"data"=>$data,
			"hora"=>$hora,
			"technician"=>$technician->getValues(),
			"order"=>$order->getValues(),
			"error"=>Order::getError(),
			"success"=>Order::getSuccess()
		]);
	});

	$app->post("/technical/orders/start/:idorder", function($idorder){
		User::validateTechnical();
		try {
			$order = new Order();
			$order->setidorder($idorder);
			$order->read();
			$order->setData($_POST);
			$order->updateStart();
			header("Location: /technical/orders");
		} catch (Exception $e) {
			Order::setError($e->getMessage());
			header("/technical/orders/start/$idorder");
		}
		exit;
	});

	$app->get("/technical/technician/orders", function(){
		User::validateTechnical();
		$order = new Order();
		$technician = new Technician();
		$technician->getByUser($_SESSION[User::SESSION]["pkuser"]);
		$orders = $order->getOrderByTechnician($technician->getpktechnician());

		$page = new PageTechnical();
		$page->setTpl("orders-technicians",[
			"orders"=>$orders,
			"error"=>Order::getError(),
			"success"=>Order::getSuccess()
		]);
	});

	##Fim das rotas de Técnicos


 ?>