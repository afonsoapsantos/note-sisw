<?php 

	use \Hcode\Model\User;

	function formatDate($date){
		return date('d/m/Y', strtotime($date));
	}

	function formPrice($vlprice){
		if (!$vlprice > 0) $vlprice = 0;
		return number_format($vlprice, 2, ",", ".");
	}

 ?>