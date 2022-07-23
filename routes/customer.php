<?php 
	use \Sisweb\PageCustomer;
	use \Sisweb\Model\User;
	use \Sisweb\Model\Customer;
	use \Sisweb\Model\Farmworker;
	use \Sisweb\Model\Request;
	use \Sisweb\Model\Implement;
	use \Sisweb\Model\Farm;
	use \Sisweb\Model\File;
	use \Sisweb\Model\Media;
	use \Sisweb\Model\Order;
	use \Sisweb\Model\Address;
	use \Sisweb\Model\State;
	use \Sisweb\Model\City;

	## Rotas do usuário CLIENTE no sistema
	$app->get("/customer", function() {
	    User::validateCustomer();

	    $customer = new Customer();
	    $customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
	    
		$page = new PageCustomer();
		$page->setTpl("index",[
			"name"=>$customer->gettxcorporatename()
		]);
	});

	$app->get("/customer/orders", function(){
		User::validateCustomer();

		$customer = new Customer();
		$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
		$orders = Order::listOrdersByCustomer($customer->getidcustomer());
		$page = new PageCustomer();
		$page->setTpl("orders",[
			"orders"=>$orders
		]);
		exit;
	});

	$app->get("/customer/farms", function(){
		User::validateCustomer();

		$customer = new Customer();
		$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
		$farms = Farm::getFarmByCustomer($customer->getidcustomer());
		$page = new PageCustomer();
		$page->setTpl("farms",[
			"farms"=>$farms,
			"error"=>Farm::getError(),
			"sucess"=>Farm::getSuccess()
		]);
		exit;
	});

	$app->get("/customer/farms/create", function() {
	    
	    User::validateCustomer();
	    $states = State::listAll();
	    $cities = City::listAll();
		$page = new PageCustomer();
		$page->setTpl("farms-create",[
			"states"=>$states,
			"cities"=>$cities,
			"error"=>Farm::getError(),
			"sucess"=>Farm::getSuccess()
		]);
		exit;
	});

	$app->post("/customer/farms/create", function(){
		User::validateCustomer();
		try {
			$farm = new Farm();
			$address = new Address();
			$customer = new Customer();
			$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
			
			$address->setData($_POST);
			$address->setuserfk((int)$_SESSION[User::SESSION]["pkuser"]);
			$address->insert();

			$farm->setData($_POST);
			$farm->setcustomerfk($customer->getidcustomer());
			$farm->setaddressfarmfk($address->getidaddress());
			$farm->insert();
			Farm::setSuccess("Fazenda registrada com Sucesso!");
			header("Location: /customer/farms/create");
			exit;
		} catch (Exception $e) {
			Farm::setError($e->getMessage());
			header("Location: /customer/farms/create");
			exit;
		}
		exit;
	});

	$app->get("/customer/farms/:idfarm", function($idfarm) {    
	    User::validateCustomer();

	    $farm = new Farm();
	    $farm->setidfarm($idfarm);
	    $farm->get();

		$page = new PageCustomer();
		$page->setTpl("farms-update",[
			"farm"=>$farm->getValues(),
			"error"=>Farm::getError(),
			"success"=>Farm::getSuccess()
		]);
	});

	$app->post("/customer/farms/:idfarm", function($idfarm) {    
	    User::validateCustomer();

	    try {
	    	$farm = new Farm();
		    $farm->setidfarm($idfarm);
		    $farm->get();
		    $farm->setData($_POST);
		    $farm->update();
		    header("Location: /customer/farms");
	    } catch (Exception $e) {
	    	Farm::setError($e->getMessage());
	    	header("Location: /customer/farms/update/$idfarm");
	    }
	    exit;
	});

	$app->get("/customer/farms/delete/:idfarm", function($idfarm) {    
	    User::validateCustomer();

	    try {
	    	$farm = new Farm();
	    	$address = new Address();
		    $farm->setidfarm($idfarm);
		    $address->setuserfk($_SESSION[User::SESSION]["pkuser"]);
		    $address->readByUser();
		    $farm->get();
		    $farm->delete();	
		    $address->delete();
	    	header("Location: /customer/farms");
	    } catch (Exception $e) {
	    	Farm::setError($e->getMessage());
	    	header("Location: /customer/farms/delete/$idfarm");
	    }
	    exit;
	});

	//Rotas para as Solicitações
	$app->get("/customer/requests", function(){
		User::validateCustomer();

		$customer = new Customer();
		$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
		$idcustomer = $customer->getidcustomer();
		$requests = Request::listRequests((int)$idcustomer);
		$page = new PageCustomer();
		$page->setTpl("requests", array(
			"requests"=>$requests,
			"error"=>Request::getError(),
			"success"=>Request::getSuccess()
		));
	});

	$app->get("/customer/requests/create", function(){
		User::validateCustomer();
		
		$user = new User();
		$user->getUser((int)$_SESSION[User::SESSION]["pkuser"]);
		
		$customer = new Customer();
		$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);

		$implements = Implement::getImplementByCustomer((int)$customer->getidcustomer());
		$farms = Farm::getFarmbyCustomer((int)$customer->getidcustomer());

		$page = new PageCustomer();
		$page->setTpl("requests-create", array(
			"user"=>$user->getValues(),
			"customer"=>$customer->getValues(),
			"implements"=>$implements,
			"farms"=>$farms,
			"reqError"=>Request::getError(),
			"reqSuccess"=>Request::getSuccess(),
			"medSuccess"=>Media::getSuccess(),
			"medError"=>Media::getError()
		));
	});

	$app->post("/customer/requests/create", function(){
		User::validateCustomer();
		
		try {
			$request = new Request();
			$request->getMaxId();
			$request->settxsituation($_POST["txsituation"]);
			$request->settxproblem($_POST["txproblem"]);
			$request->setcustomerfk((int)$_POST["idcustomer"]);
			$request->setfarmfk((int)$_POST["idfarm"]);
			$request->setimplementfk((int)$_POST["idimplement"]);
			$request->setstatusfk((int)$_POST["statusfk"]);
			$request->insertRequest();
			if ($_FILES['media']['name'][0] === '') {
				header("Location: /customer/requests/create");
				Request::setSuccess("Solicitação realizada com Sucesso!");
			} else {
				if ($_SERVER['REQUEST_METHOD'] === "POST") {
					$file = $_FILES["media"];
					$amount = count($file["name"]);
					for ($i=0; $i < $amount; $i++) {

						$dirUploadsCustomer = File::dirname($_POST["idcustomer"]);
						$upload = File::upload($_POST["idcustomer"], $request->getidrequest(), $_FILES["media"], $i,$dirUploadsCustomer);

						if ($upload === true) {
							$media = new Media();
							$media->getMaxIdRequest();
							$data = File::newName($file, $i, $_POST["idcustomer"], $media->getidmedia(), $request->getidrequest());
							File::renanmeUpload($file, $i,$dirUploadsCustomer, $data["newname"]);
							$media->settxnamemedia($data["newname"]);
							$media->settxlocalmedia($dirUploadsCustomer);
							$media->settxdescription($data["midia"]);
							$media->setdtmedia(date("Y-m-d"));
							$media->setcustomerfk((int)$_POST["idcustomer"]);
							$media->setrequestfk($request->getidrequest());
							$media->insertMediaRequest();
						}	
					}
				}	
			}
			Media::setSuccess("Arquivos inseridos!");
			Request::setSuccess("Solicitação realizada com Sucesso!");
			header("Location: /customer/requests/create");
		} catch (Exception $e) {
			Request::setError($e->getMessage());
			header("Location: /customer/requests/create");
		}
		exit;
	});

	$app->get("/customer/requests/media/:pkrequest", function($pkrequest){
		User::validateCustomer();
		$medias = Media::listAllByRequest($pkrequest);

		$page = new PageCustomer();
		$page->setTpl("request-medias", [
			"medias"=>$medias
		]);
	});

	$app->get("/customer/requests/update/:idrequest", function($idrequest) {    
	    User::validateCustomer();
	    $customer = new Customer();
	    $customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
		$request = new Request();
		$request->setidrequest($idrequest);
		$request->get();
		$farms = Farm::getFarmByCustomer($customer->getidcustomer());
		$implements = Implement::getImplementByCustomer($customer->getidcustomer());	    

		$page = new PageCustomer();
		$page->setTpl("requests-update",[
			"request"=>$request->getValues(),
			"farms"=>$farms,
			"implements"=>$implements,
			"error"=>Request::getError(),
			"success"=>Request::getSuccess()
		]);
	});

	$app->post("/customer/requests/update/:idrequest", function($idrequest){
		User::validateCustomer();
		$request = new Request();
		$request->setidrequest($idrequest);
		$request->get();
		$request->setData($_POST);
		$request->update();
		header("Location: /customer/requests");
		exit;
	});

	$app->get("/customer/requests/delete/:idrequest", function($idrequest) {    
	    User::validateCustomer();
	    
	    try {
	    	$customer = new Customer();
		    $customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
			$request = new Request();
			$request->setidrequest($idrequest);
			$request->get();
			$request->veryfyStatus();
			$media = new Media();
			$media->deleteMediaRequest($request->getidrequest());
			$request->delete();
			header("Location: /customer/requests");
	    } catch (Exception $e) {
	    	Request::setError($e->getMessage());
	    	header("Location: /customer/requests");
	    }
		exit;
	});
	//Fim das Rotas para as Solicitações

	$app->get("/customer/farmworker", function(){
		User::validateCustomer();
		$customer = new Customer();
		$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
		$farmworkers = FarmWorker::listAllByCustomer($customer->getidcustomer());
		$page = new PageCustomer();
		$page->setTpl("farmworker",[
			"farmworker"=>$farmworkers,
			"error"=>FarmWorker::getError(),
			"success"=>FarmWorker::getSuccess()
		]);
	});

	$app->get("/customer/farmworker/update/:pkfarmworker", function($pkfarmworker){
		User::validateCustomer();
		$farmworker = new FarmWorker();
		$farmworker->get($pkfarmworker);
		$page = new PageCustomer();
		$page->setTpl("farmworker-update",[
			"farmworker"=>$farmworker->getValues()
		]);
	});

	$app->post("/customer/farmworker/update/:pkfarmworker", function($pkfarmworker){
		User::validateCustomer();
		
		try {
			$customer = new Customer();
			$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
			$farmworker = new FarmWorker();
			$farmworker->get($pkfarmworker);
			$farmworker->setData($_POST);
			$farmworker->setcustomerfk($customer->getidcustomer());
			$farmworker->update();
			FarmWorker::setSuccess("Atualizado com Sucesso!");
			header("Location: /customer/farmworker");
		} catch (Exception $e) {
			FarmWorker::setError($e->getMessage());
			header("/customer/farmworker/update/$farmworker->getpkfarmworker()");
		}
		exit;
	});

	$app->get("/customer/farmworker/delete/:pkfarmworker", function($pkfarmworker){
		User::validateCustomer();
		try {
			$farmworker = new FarmWorker();
			$user = new User();
			$farmworker->get((int)$pkfarmworker);
			$user->getUser((int)$farmworker->getuserfk());
			$address = new Address();
			$address->setuserfk((int)$user->getpkuser());
			$address->readByUser();
			$farmworker->delete();
			$address->delete();
			$user->deleteUser();
			FarmWorker::setSuccess("Exclusão realizada com Sucesso!");
			header("Location: /customer/farmworker");
		} catch (Exception $e) {
			FarmWorker::setError($e->getMessage());
			header("Location: /customer/farmworker");
		}
		exit;
	});

	$app->get("/customer/farmworker/consult/:pkfarmworker", function($pkfarmworker){
		User::validateCustomer();
		$page = new PageCustomer();
	});

	$app->get("/customer/farmworker/create", function(){
		User::validateCustomer();
		$states = State::listAll();
	    $cities = City::listAll();
		$page = new PageCustomer();
		$page->setTpl("farmworker-create",[
			"states"=>$states,
			"cities"=>$cities,
			"error"=>FarmWorker::getError(),
			"success"=>FarmWorker::getSuccess()
		]);
	});

	$app->post("/customer/farmworker/create", function(){
		User::validateCustomer();
		try {
			$customer = new Customer();
			$customer->getCustomerUser($_SESSION[User::SESSION]["pkuser"]);

			$farmworker = new FarmWorker();
			$user = new User();
			$address = new Address();

			$user->setData($_POST);
			$farmworker->setData($_POST);
			$address->setData($_POST);

			$user->setfkusertype((int)5);
			$user->setfkstatususer((int)4);
			$user->setdtregisteruser(date("Y-m-d"));
			$user->saveUser();

			$farmworker->setcustomerfk($customer->getidcustomer());
			$farmworker->setuserfk($user->getpkuser());
			$farmworker->insert();

			$address->setuserfk($user->getpkuser());
			$address->insert();


			FarmWorker::setSuccess("Cadastro realizado com Sucesso!");
			header("Location: /customer/farmworker/create");

		} catch (Exception $e) {
			FarmWorker::setError($e->getMessage());
			header("Location: /customer/farmworker/create");
		}
		exit;
	});

	//Rotas para gerencia de implementos
	$app->get("/customer/implements", function(){
		User::validateCustomer();

		$customer = new Customer();
		$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);

		$implements = Implement::getImplementByCustomer($customer->getidcustomer());

		$page = new PageCustomer();
		$page->setTpl("implements",[
			"implements"=>$implements,
			"error"=>Implement::getError(),
			"sucess"=>Implement::getSuccess()
		]);
	});

	$app->get("/customer/implements/delete/:idimplement", function($idimplement){
		User::validateCustomer();

		try {
			$implement = new Implement();
			$implement->get((int)$idimplement);
			$implement->delete();
			Implement::setSuccess("Implemento deletado com Sucesso!");
			header("Location: /customer/implements");
		} catch (Exception $e) {
			Implement::setError($e->getMessage());
			header("Location: /customer/implements");
		}
		exit;
	});

	$app->get("/customer/implements/create", function(){
		User::validateCustomer();
		$page = new PageCustomer();
		$page->setTpl("implements-create",[
			"error"=>Implement::getError(),
			"sucess"=>Implement::getSuccess()
		]);
	});

	$app->post("/customer/implements/create", function(){
		User::validateCustomer();

		try {
			$customer = new Customer();
			$customer->getCustomerUser((int)$_SESSION[User::SESSION]["pkuser"]);
			$implement = new Implement();
			$implement->verifyPost($_POST);
			$implement->setData($_POST);
			$implement->setcustomerfk((int)$customer->getidcustomer());
			$page = new PageCustomer();
			$page->setTpl("implements-create-load",[
				"implement"=>$implement->getValues()
			]);
			$implement->insert();
			Implement::setSuccess("Implemento cadastrado com Sucesso!");
			header("Location: /customer/implements/create");
			
		} catch (Exception $e) {
			Implement::setError($e->getMessage());
			header("Location: /customer/implements/create");
		}
		exit;
	});

	$app->get("/customer/implements/update/:idimplement", function($idimplement){
		User::validateCustomer();

		$implement = new Implement();
		$implement->get((int)$idimplement);

		$page = new PageCustomer();
		$page->setTpl("implements-update",[
			"implement"=>$implement->getValues()
		]);
	});

	$app->post("/customer/implements/update/:idimplement", function($idimplement){
		User::validateCustomer();
		$implement = new Implement();
		$implement->get((int)$idimplement);
		$implement->setData($_POST);
		$implement->update();
		header("Location: /customer/implements");
		exit;

	});




	## Fim das Rotas do CLIENTE


 ?>