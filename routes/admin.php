<?php

use Sisweb\Controller\UserController;
use Sisweb\DB\Database;
use \Sisweb\PageAdmin;
	use \Sisweb\Model\User;
	use \Sisweb\Model\Administrator;
	use Sisweb\Model\CashFlow;
	use \Sisweb\Model\Customer;
	use \Sisweb\Model\Request;
	use \Sisweb\Model\Provider;
	use \Sisweb\Model\Product;
	use \Sisweb\Model\Service;
	use \Sisweb\Model\Media;
	use \Sisweb\Model\Order;
	use \Sisweb\Model\Status;
	use Sisweb\Model\Technician;
	use \Sisweb\Model\UserType;
	use \Sisweb\Model\CashMovement;

	use \Sisweb\DB\Migrations\Migration;
	use \Sisweb\DB\Migrations\UserMigration;

	##Rota para atualizar DB
	$app->get("/admin/db", function(){
		$db = new Database();
		$migration = new Migration();

		$mu = new UserMigration();
		$mu->up();

	});

	## ROTA para o Fluxo de Caixa
	$app->get("/admin/cashFlow", function() {
		Administrator::validateAdmin();

		$cashflow = new CashFlow();

		$page = new PageAdmin();
		$page->setTpl("cashFlow", [
			"user"=>$_SESSION[User::SESSION]
		]);
	});

	## Rotas do Adminsitrador do sistema
	##Usuário master
	$app->get("/admin", function() {
		Administrator::validateAdmin();

		$page = new PageAdmin();
		$page->setTpl("index", [
			"user"=>$_SESSION[User::SESSION]
		]);

	});

	$app->get("/admin/registrations", function() {
	    
	    Administrator::validateAdmin();

		$page = new PageAdmin();
		$page->setTpl("registrations", [
			"user"=>$_SESSION[User::SESSION]
		]);

	});

	//Users
	$app->get('/admin/users', function() {
	    Administrator::validateAdmin();

	    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	    $pagination = User::getUsersPage($page);

	    $pages = [];

	    for ($i=1; $i <= (int)$pagination['pages'] ; $i++) { 
	    	array_push($pages, [
	    		'link'=>'/admin/users?page='.$i,
	    		'page'=>$i
	    	]);
		}

		$page = new PageAdmin();
		$page->setTpl("users",array(
			"data"=>$pagination['data'],
			"pages"=>$pages
		));
	});

	$app->get("/admin/users/create", function (){
		Administrator::validateAdmin();

		$status = new Status();
		$usertype = new UserType();
		$listStatus = $status->read();
		$userstypes = $usertype->read();
		$page = new PageAdmin();
		$page->setTpl("users-create", array(
			"userstypes"=>$userstypes,
			"status"=>$listStatus
		));
	});

	$app->post("/admin/users/create", function(){
		Administrator::validateAdmin();
		
		try{
			$uc = new UserController();
			$uc->create($_POST);
			header("Location: /admin/users");
			exit;
		}catch(Exception $e){
			User::setError($e->getMessage());
			header("Location: /admin/users/create");
		}
	});

	$app->get("/admin/users/:id/delete", function ($id){
		Administrator::validateAdmin();
		try {
			$user = new User();
			$user->setData((int)$id);
			$user->get();
			$user->delete();
			header("Location: /admin/users");
			exit;
		} catch(Exception $e) {
			User::setError($e->getMessage());
			header("Location: /admin/users");
		}
	});

	$app->get("/admin/users/:id", function ($id){
		Administrator::validateAdmin();
		$user = new User();
		$status = new Status();
		$usertype = new UserType();

		$user->setid((int)$id);
		$userstypes = $usertype->read();
		$listStatus = $status->read();
		$user->get();
		
		$page = new PageAdmin();
		$page->setTpl("users-update", array(
			"user"=>$user->getValues(),
			"userstypes"=>$userstypes,
			"status"=>$listStatus
		));
		exit;
	});

	$app->post("/admin/users/:id", function ($id){
		Administrator::validateAdmin();
		$user = new User();
		$user->getUser((int)$id);
		$user->setData($_POST);
		$user->updateUser();
		header("Location: /admin/users");
		exit;
	});

	//Fim dos métodos de Usuário

	//Solicitações

	$app->get("/admin/requests", function(){
		Administrator::validateAdmin();

		$requests = Request::listRequestsAdmin();
		$page = new PageAdmin();
		$page->setTpl("requests", array(
			"requests"=>$requests
		));
	});

	//fim das solicitações

	//Rotas para Ordens de Serviços
	$app->get("/admin/generate/order/:id", function($id){
		Administrator::validateAdmin();
		$request = new Request();
		$request->setid($id);
		$request->get();
		$page = new PageAdmin();
		$page->setTpl("generate-order", array(
			"request"=>$request->getValues(),
			"error"=>Order::getError()
		));
		exit;
	});

	$app->get("/admin/orders", function(){
		Administrator::validateAdmin();

		$order = new Order();
		$orders = $order->read();
		$page = new PageAdmin();
		$page->setTpl("orders",[
			"orders"=>$orders,
			"error"=>Order::getError(),
			"success"=>Order::getSuccess()
		]);
	});

	$app->get("/admin/orders/create/:id", function($idr){
		Administrator::validateAdmin();

		$customer = new Customer();
		$customer->getCustomer($idr);
		#$customer->setData($data);
		var_dump($customer);
	});

	$app->post("/admin/orders/create", function(){
		Administrator::validateAdmin();
		$request = new Request();
		try {
			$request->setid((int)$_POST["requestfk"]);
			$request->get();
			$request->setstatusfk((int)3);
			$order = new Order();
			$order->getMaxIdOrder();
			$order->setData($_POST);
			// var_dump($order);
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
		} catch (Exception $e) {
			Order::setError($e->getMessage());
			header("Location: /admin/requests/generate/order/".$request->getid());
		}
		exit;
	});

	$app->get("/admin/services", function(){
		Administrator::validateAdmin();

	    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	    $pagination = Service::getServicesPage($page);

	    $pages = [];

	    for ($i=1; $i <= (int)$pagination['pages'] ; $i++) { 
	    	array_push($pages, [
	    		'link'=>'/admin/services?page='.$i,
	    		'page'=>$i
	    	]);
	    }

		$page = new PageAdmin();
		$page->setTpl("services",array(
			"data"=>$pagination['data'],
			"pages"=>$pages
		));
	});

	$app->get("/admin/services/create", function(){
		Administrator::validateAdmin();
		$page = new PageAdmin();
		$page->setTpl("services-create");
	});

	$app->post("/admin/services/create", function(){
		Administrator::validateAdmin();
		$service = new Service();
		$service->getMaxId();
		$service->setData($_POST);
		$service->insert();
		header("Location: /admin/services");
		exit;
	});

	$app->get("/admin/services/update/:id", function($id){
		Administrator::validateAdmin();

		$service = new Service();
		$service->setid($id);
		$service->get();
		$page = new PageAdmin();
		$page->setTpl("services-update",[
			"service"=>$service->getValues()
		]);
	});

	$app->post("/admin/services/update/:id", function($id){
		Administrator::validateAdmin();
		$service = new Service();
		$service->setid($id);
		$service->get();
		$service->setData($_POST);
		$service->update();
		header("Location: /admin/services");
		exit;
	});

	$app->get("/admin/services/delete/:id", function($id){
		Administrator::validateAdmin();

		$service = new Service();
		$service->setid($id);
		$service->get();
		$service->delete();
		header("Location: /admin/services");
		exit;
	});

	$app->get("/admin/technical", function(){
		Administrator::validateAdmin();
		$page = new PageAdmin();
		$page->setTpl("technical");
	});

	$app->get("/admin/providers", function(){
		Administrator::validateAdmin();
		$providers = Provider::listAll();
		$page = new PageAdmin();
		$page->setTpl("providers", array(
			"providers"=>$providers
		));
	});

	$app->get("/admin/providers/create", function(){
		Administrator::validateAdmin();
		$page = new PageAdmin();
		$page->setTpl("providers-create");
	});

	$app->post("/admin/providers/create", function(){
		Administrator::validateAdmin();
		$provider = new Provider();
		$provider->getMaxId();
		$provider->getMaxIdOrder();
		$provider->settxcorporatename($_POST["txcorporatename"]);
		$provider->settxfantasyname($_POST["txfantasyname"]);
		$provider->setnucnpj((int)$_POST["nucnpj"]);
		$provider->setnustateregistration((int)$_POST["nustateregistration"]);
		$provider->setnumunicipalregistration((int)$_POST["numunicipalregistration"]);
		$provider->saveProvider();
		header("Location: /admin/providers");
		exit;
	});

	$app->get("/admin/products", function(){
		Administrator::validateAdmin();
		$product = Product::listAll();
		$page = new PageAdmin();
		$page->setTpl("products", array(
			"product"=>$product
		));
	});

	$app->get("/admin/products/create", function(){
		Administrator::validateAdmin();
		$page = new PageAdmin();
		$page->setTpl("products-create");
	});

	$app->get("/admin/financial", function(){
		Administrator::validateAdmin();
		$page = new PageAdmin();
		$page->setTpl("financial");
	});

	## Fim das rotas do ADMIN


 ?>