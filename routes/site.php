<?php

	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Sisweb\Page;
	use \Sisweb\Model\User; 

	$app->get('/', function($request, $response, $args) {

		$page = new Page([
			"header"=>false,
			"footer"=>false
		]);

		$page->setTpl('login', [
			'error'=>User::getError()
		]);

		return $response;

	});

	$app->get("/company", function() {
    
		$page = new Page();

		$page->setTpl("company");

	});

	$app->get("/agriculture", function() {
    
		$page = new Page();

		$page->setTpl("agriculture");

	});

	$app->get("/implements", function() {
    
		$page = new Page();

		$page->setTpl("implements");

	});

	$app->get("/products", function() {
    
		$page = new Page();

		$page->setTpl("products");

	});

	$app->get("/register", function() {
	    
		$page = new Page([
			"header"=>false,
			"footer"=>false
		]);
		$page->setTpl("register");

	});

	$app->get("/login", function() {
	    
		$page = new Page([
			"header"=>false,
			"footer"=>false
		]);

		$page->setTpl("login",[
			'error'=>User::getError()#,
			#'attempts'=>User::getAttempts()
		]);
	});

	$app->post("/login", function() {
		try {
			$user = new User();
			$user->login($_POST["txlogin"],(int)$_POST["txpassword"]);
			$fkusertype = $user->getfkusertype();
			switch ($fkusertype) {
				case 1:
					header("Location: /admin");
					break;
				case 2:
					header("Location: /manager");
					break;
				case 3:
					header("Location: /technical");
					break;
				case 4:
					header("Location: /customer");
					break;
				case 5:
					header("Location: /farmworker");
					break;
				case 0:
					header("Location: /login");
					break;
				default:
					header("Location: /login");
					break;
			}
			exit;
		} catch(Exception $e) {
			User::setError($e->getMessage());
			#$numTent = $numTent + 1;
			#User::setAttempts($numTent);
		}
		header("Location: /login");
		exit;
	});

	## Rota de logout geral do sistema
	$app->get("/logout", function() {

		User::logout();
		header("Location: /login");
		exit;

	});

	#Rota para recuperar senha
	$app->get("/forgot", function() {
	    
		$page = new Page([
			"header"=>false,
			"footer"=>false
		]);
		$page->setTpl("forgot");

	});

	$app->post("/forgot", function() {
	    
		//$user = User::getForgot($_POST["email"]);

		header("Location: /forgot/sent");

	});

	$app->get("/forgot/sent", function() {

		$page = new Page([
			"header"=>false,
			"footer"=>false
		]);
		$page->setTpl("forgot-sent");

	});
	## FIm da rota para recuperar senha

 ?>