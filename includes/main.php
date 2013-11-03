<?php
//Connect to the database
	$host = "localhost";
	$user = "root";
	$pass = "Oliver99";
	$database = "sga";

	$db = mysql_connect($host, $user, $pass);
	mysql_select_db($database, $db);

//Validate an ISBN
	function ISBN10Valid($ISBN10){
		if(strlen($ISBN10) != 10)
			return false;

		$a = 0;
		for($i = 0; $i < 10; $i++){
			if ($ISBN10[$i] == "X"){
				$a += 10*intval(10-$i);
			} else {//running the loop
				$a += intval($ISBN10[$i]) * intval(10-$i); }
		}
		return ($a % 11 == 0);
	}

	function ISBN13Valid($n){
		$check = 0;
		for ($i = 0; $i < 13; $i+=2) $check += substr($n, $i, 1);
		for ($i = 1; $i < 12; $i+=2) $check += 3 * substr($n, $i, 1);
		return $check % 10 == 0;
	}

//Functions to convert between ISBN10 and ISBN13
	function clean($isbn) {
		return preg_replace("/[^0-9X]+/", '', $isbn);
	}

	function toISBN10($isbn) {
		$isbn = clean($isbn);

		if (strlen($isbn) == 10) {
			return $isbn;
		} else if (strlen($isbn) != 13 || substr($isbn, 0, 3) != '978') {
			return false;
		}

		$i = substr($isbn, 3);
		$sum = $i[0]*1 + $i[1]*2 + $i[2]*3 + $i[3]*4 + $i[4]*5
		+ $i[5]*6 + $i[6]*7 + $i[7]*8 + $i[8]*9;

		$check = $sum % 11;
		if ($check == 10) {
			$check = "X";
		}

		return substr($isbn, 3, 9) . $check;

	}

	function toISBN13($isbn) {
		$isbn = clean($isbn);

		if (strlen($isbn) == 13) {
			return $isbn;
		} else if (strlen($isbn) != 10) {
			return false;
		}

		$i = "978" . substr($isbn, 0, -1);
		$sum = 3*($i[1] + $i[3] + $i[5] + $i[7] + $i[9] + $i[11])
		+ $i[0] + $i[2] + $i[4] + $i[6] + $i[8] + $i[10];

		$check = $sum % 10;
		if ($check != 0) {
			$check = 10 - $check;
		}

		return $i . $check;
	}

//Get the extension of a file
	function extn($file) {
		$parts = explode(".", $file);
		return $parts[sizeof($parts) - 1];
	}
?>