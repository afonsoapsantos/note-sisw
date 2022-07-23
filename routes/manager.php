<?php  

	##Rotas do GERENTE
	use \Sisweb\PageManager;
	use \Sisweb\Model\Person;
	use \Sisweb\Model\User;
	use \Sisweb\Model\Customer;
	use \Sisweb\Model\Manager;
	use \Sisweb\Model\Technician;
	use \Sisweb\Model\Farmworker;
	use \Sisweb\Model\Request;
	use \Sisweb\Model\Provider;
	use \Sisweb\Model\Product;
	use \Sisweb\Model\File;
	use \Sisweb\Model\Media;
	use \Sisweb\Model\Order;

	$app->get("/manager", function(){
		Manager::validateAdmin()();

		$page = new PageManager();
		$page->setTpl("index");
	});

	##Rotas das Solicitações
	$app->get("/manager/requests", function(){
		Manager::validateAdmin()();
		$requests = Request::listRequestsAdmin();
		$page = new PageManager();
		$page->setTpl("requests",[
			"requests"=>$requests
		]);
	});

	$app->get("/manager/requests/generate/order/:idrequest", function($idrequest){
		Manager::validateAdmin()();
		$request = new Request();
		$request->setidrequest($idrequest);
		$request->get();
		$page = new PageManager();
		$page->setTpl("generate-order", array(
			"request"=>$request->getValues(),
			"error"=>Order::getError()
		));
		exit;
	});

	$app->get("/manager/orders", function(){
		Manager::validateAdmin()();
		$orders = Order::listAll();
		$page = new PageManager();
		$page->setTpl("requests",[
			"orders"=>$orders,
			"error"=>Order::getError(),
			"success"=>Order::getSucess()
		]);
	});

	$app->post("/manager/orders/create", function(){
		Manager::validateAdmin()();
		try {
			$idrequest = (int)$_POST["requestfk"];
			$request = new Request();
			$request->setidrequest($idrequest);
			$request->read();
			$request->setstatusfk((int)3);
			$order = new Order();
			$order->getMaxIdOrder();
			$order->setData($_POST);
			$order->insert();
			$request->update();
			$medias = Media::listAllByRequest($order->getrequestfk());
			$amount = count($medias);
			for ($i=0; $i < $amount; $i++) { 
				$media = new Media();
				$media->getMaxIdorder();
				$media->setData($medias[$i]);
				$media->setorderfk($order->getidorder());
				$media->setdtmedia(date("Y-m-d"));
				$media->insertMediaOrder();
			}
			header("Location: /admin/orders");
			Order::setError("");
		} catch (Exception $e) {
			Order::setError($e->getMessage());
			header("Location: /admin/requests/generate/order/$idrequest");
		}
		exit;
	});

	$app->get("/manager/users", function(){
		Manager::validateAdmin()();
		$users = User::listAllManagers();
		$page = new PageManager();
		$page->setTpl("users",[
			"users"=>$users
		]);
	});

	$app->get("/manager/users/managers/create", function(){
		Manager::validateAdmin()();
		$page = new PageManager();
		$page->setTpl("users-managers-create");
	});

	$app->get("/manager/users/technical", function(){
		Manager::validateAdmin()();
		$users = User::listAllTechnical();
		$page = new PageManager();
		$page->setTpl("users-technical",[
			"users"=>$users
		]);
	});

	$app->get("/manager/users/technical/create", function(){
		Manager::validateAdmin()();
		$page = new PageManager();
		$page->setTpl("users-technical-create");
	});

	$app->get("/manager/users/customers", function(){
		Manager::validateAdmin()();
		$users = User::listAllCustomers();
		$page = new PageManager();
		$page->setTpl("users-customers",[
			"users"=>$users
		]);
	});

	$app->get("/manager/users/farmworker", function(){
		Manager::validateAdmin()();
		$users = User::listAllFarmWorker();
		$page = new PageManager();
		$page->setTpl("users-farmworker",[
			"users"=>$users
		]);
	});

	//Rotas para Fornecedores
	$app->get("/manager/providers", function(){
		Manager::validateAdmin()();
		$providers = Provider::listAll();
		$page = new PageManager();
		$page->setTpl("providers", array(
			"providers"=>$providers,
			"error"=>Provider::getError(),
			"success"=>Provider::getSuccess()
		));
	});

	$app->get("/manager/providers/create", function(){
		Manager::validateAdmin()();
		$page = new PageManager();
		$page->setTpl("providers-create");
	});

	$app->post("/manager/providers/create", function(){
		Manager::validateAdmin()();
		$provider = new Provider();
		$provider->getMaxId();
		$provider->setData($_POST);
		$provider->insert();
		header("Location: /manager/providers");
		exit;
	});

	$app->get("/manager/providers/delete/:idprovider", function($idprovider){
		Manager::validateAdmin()();

		try {
			$provider = new Provider();
			$provider->setidprovider($idprovider);
			$provider->get();
			$provider->delete();
			header("Location: /manager/providers");
		} catch (Exception $e) {
			Provider::setError($e->getMessage());
			header("Location: /manager/providers");
		}
		exit;
	});

	$app->get("/manager/providers/update/:idprovider", function($idprovider){
		Manager::validateAdmin()();

		$provider = new Provider();
		$provider->setidprovider($idprovider);
		$provider->get();

		$page = new PageManager();
		$page->setTpl("providers-update",[
			"provider"=>$provider->getValues(),
			"error"=>Provider::getError(),
			"success"=>Provider::getSuccess()
		]);
	});

	$app->post("/manager/providers/update/:idprovider", function($idprovider){
		Manager::validateAdmin()();
		try {
			$provider = new Provider();
			$provider->setidprovider($idprovider);
			$provider->get();
			$provider->setData($_POST);
			$provider->update();
			$id = $provider->getidprovider();
			header("Location: /manager/providers");
		} catch (Exception $e) {
			Provider::setError($e->getMessage());
			header("Location: /manager/providers/update/$id");
		}
		exit;
	});

	##Fim das rotas do GERENTE

?>