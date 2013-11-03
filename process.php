<?php
	require_once("includes/main.php");
	require_once("includes/indextank.php");
	require_once("includes/Cloudinary/Cloudinary.php");
	require_once("includes/Cloudinary/Uploader.php");
	
	$bookID = 0;
	$saleID = 0;
	
//Add the book to the books table
	$booksH = mysql_query("SELECT * FROM `books` WHERE `ISBN10` = '{$_POST['ISBN10']}' OR `ISBN13` = '{$_POST['ISBN13']}'");
	$books = mysql_fetch_assoc($booksH);
	
	if (!is_array($books)) {
		Cloudinary::config(array(
			"cloud_name" => "forwardfour",
			"api_key" => "211984672133855",
			"api_secret" => "nN9B1_7aKjq_PPR_2hRs8aZ7qnQ"
		));
	
		$cloudinary = \Cloudinary\Uploader::upload($_POST['ImageID']);
		
		mysql_query("INSERT INTO `books` (
						`BookID`, `ISBN10`, `ISBN13`, `Title`, `Author`, `Edition`, `ImageID`, `ImageState`
					) VALUES (
						NULL, '" . mysql_real_escape_string(stripslashes($_POST['ISBN10'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['ISBN13'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Title'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Author'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Edition'])) . "', '" . mysql_real_escape_string(stripslashes($cloudinary['public_id'] . "." . $cloudinary['format'])) . "', '" .  mysql_real_escape_string(stripslashes($_POST['ImageState'])) . "'
					)");
					
		$bookID = mysql_insert_id();
	} else {
		$bookID = $books['ID'];
	}
	
	echo "\n\n\n\n";

//Add the sale to the sales table
	if ($_POST['Merchant']) {
		mysql_query("INSERT INTO `sale` (
						`SaleID`, `BookID`, `Merchant`, `Upload`, `Sold`, `Price`, `Condition`, `Written`, `Comments`
					) VALUES (
						NULL, '" . $bookID . "', '" . mysql_real_escape_string(stripslashes($_POST['Merchant'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Upload'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Sold'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Price'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Condition'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Written'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Comments'])) . "'
					)");
					
		$saleID = mysql_insert_id();
	}
	
	echo "\n\n\n\n";
	
//Add the book course information to the bookcourses table
	if ($_POST['Merchant']) {
		$count = 0;
		
		foreach($_POST['Course'] as $course) {
			mysql_query("INSERT INTO `bookcourses` (
							`SaleID`, `Course`, `Number`, `Section`
						) VALUES (
							'" . $saleID . "', '" . mysql_real_escape_string(stripslashes($_POST['Course'][$count])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Number'][$count])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Section'][$count])) . "'
						)");
						
			echo "\n\n\n\n";
			$count++;
		}
	}
	
//Add the purchased information to the purcahses table
	if ($_POST['Sold']) {
		mysql_query("INSERT INTO `purchases` (
						`BookID`, `Price`, `Buyer`, `Merchant`, `Time`
					) VALUES (
						'" . $bookID . "', '" . mysql_real_escape_string(stripslashes($_POST['Price'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Buyer'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Merchant'])) . "', '" . mysql_real_escape_string(stripslashes($_POST['Time'])) . "'
					)");
	}
	
//Send information to IndexDen
	if ($_POST['Merchant'] && !$_POST['Sold']) {
		$client = new Indextank_Api("http://server:quzusytybybu@dujeze.api.indexden.com");
		$index = $client->get_index("sga_bookexchange");
		$index->add_document($saleID, array("Title" => stripslashes($_POST['Title']), "Author" => stripslashes($_POST['Author']), "ISBN10" => stripslashes($_POST['ISBN10']), "ISBN13" => stripslashes($_POST['ISBN13'])));
	}
	
//Mark the old data as "ported to the new tables"
	mysql_query("UPDATE `ffi_be_books` SET `Ported` = '1' WHERE `linkID` = '" . $_POST['linkID']. "'");
	
	header("Location: books.php");
	exit;
?>