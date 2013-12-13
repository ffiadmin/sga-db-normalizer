<?php
//Connect to the database
	$host = "localhost";
	$user = "root";
	$pass = "Oliver99";
	$database = "wordpress";

	$db = mysql_connect($host, $user, $pass);
	mysql_select_db($database, $db);
	
//Get the data
	$number = 0;
	
	while(true) {
		$data = mysql_query("SELECT * FROM `a_ffi_be_old_purchases` ORDER BY `TimeC` ASC LIMIT " . $number . ", 1");
		
		$fetch = mysql_fetch_array($data);
		++$number;
		
		if (!$fetch) {
			break;
		}
		
		mysql_query("UPDATE `a_ffi_be_purchases` SET `MerchantID` = " . $fetch['sellerID'] . " WHERE `Time` = '" . $fetch['TimeC'] . "' AND `BuyerID` = '" . $fetch['buyerID'] . "'");
	}
?>