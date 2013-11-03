<?php
//Include the database connection file
	require_once("includes/main.php");

//Fetch the top book in the books table
	//$stmtHandle = mysql_query("SELECT * FROM ffi_be_books WHERE `linkID` = (SELECT `linkID` FROM ffi_be_books WHERE Ported = '0' AND `id` = '1247' LIMIT 1)", $db);
	$stmtHandle = mysql_query("SELECT * FROM ffi_be_books WHERE `linkID` = (SELECT `linkID` FROM ffi_be_books WHERE Ported = '0' LIMIT 1)", $db);
	$stmt = mysql_fetch_assoc($stmtHandle);
	$stmtAll = array();
	array_push($stmtAll, $stmt);
	
	while ($stmtFetch = mysql_fetch_assoc($stmtHandle)) {
		array_push($stmtAll, $stmtFetch);
	}
	
//Detect whether the ISBN is 10 or 13
	$ISBN10 = "";
	$ISBN13 = "";
	$ISBN10V = "";
	$ISBN13V = "";
	$ISBN10G = "";
	$ISBN13G = "";

	if (strlen($stmt['ISBN']) == 10) {
		$ISBN10 = $stmt['ISBN'];
		$ISBN13 = toISBN13($ISBN10);

		$ISBN13G = " [GENERATED]";
	} else {
		$ISBN13 = $stmt['ISBN'];
		$ISBN10 = toISBN10($ISBN13);

		$ISBN10G = " [GENERATED]";
	}

	if (!ISBN10Valid($ISBN10)) {
		$ISBN10V = " <span style=\"color: #FF0000;\">[NOT VALID]</span>";
	}

	if (!ISBN13Valid($ISBN13)) {
		$ISBN13V = " <span style=\"color: #FF0000;\">[NOT VALID]</span>";
	}

//Display a table of how this data will be inserted into the new DB layout
	echo "<h2>Data I've Recieved:</h2>

<table>
<thead>
<tr>
<td>id</td>
<td>userID</td>
<td>upload</td>
<td>sold</td>
<td>linkID</td>
<td>ISBN10" . $ISBN10G . "</td>
<td>ISBN13" . $ISBN13G . "</td>
<td>title</td>
<td>author</td>
<td>edition</td>
<td>course</td>
<td>number</td>
<td>section</td>
<td>price</td>
<td>condition</td>
<td>written</td>
<td>comments</td>
<td>imageURL</td>
<td>awaitingImage</td>
<td>imageID</td>
</tr>
</thead>

<tbody>
";

	foreach($stmtAll as $stmtRecieved) {
		echo "<tr>
<td>" . $stmtRecieved['id'] . "</td>
<td>" . $stmtRecieved['userID'] . "</td>
<td>" . $stmtRecieved['upload'] . "</td>
<td>" . $stmtRecieved['sold'] . "</td>
<td>" . $stmtRecieved['linkID'] . "</td>
<td>" . $ISBN10 . "</td>
<td>" . $ISBN13 . "</td>
<td>" . $stmtRecieved['title'] . "</td>
<td>" . $stmtRecieved['author'] . "</td>
<td>" . $stmtRecieved['edition'] . "</td>
<td>" . $stmtRecieved['course'] . "</td>
<td>" . $stmtRecieved['number'] . "</td>
<td>" . $stmtRecieved['section'] . "</td>
<td>" . $stmtRecieved['price'] . "</td>
<td>" . $stmtRecieved['condition'] . "</td>
<td>" . $stmtRecieved['written'] . "</td>
<td>" . $stmtRecieved['comments'] . "</td>
<td><img src=\"" . $stmt['imageURL'] . "\" /></td>
<td>" . $stmtRecieved['awaitingImage'] . "</td>
<td>" . $stmtRecieved['imageID'] . "</td>
</tr>";
	}
	
	echo "
</tbody>
</table>";

//How will this data be handled?
	$nextAIHandle = mysql_query("SHOW TABLE STATUS LIKE 'books'", $db);
	$nextAI = mysql_fetch_assoc($nextAIHandle);
	$bookID = $nextAI['Auto_increment'] ? $nextAI['Auto_increment'] : 1;
	$imageURL = empty($stmt['awaitingImage']) ? $stmt['imageURL'] : $stmt['awaitingImage'];
	$verification = empty($stmt['awaitingImage']) ? "0" : "1";
	
	echo "<form action=\"process.php\" method=\"post\">
<input type=\"hidden\" name=\"linkID\" value=\"" . $stmt['linkID'] . "\" />
<h2>What I'm Doing With This Data:</h2>
<h3>books</h3>

<table>
<tr>
<td><u>BookID</u></td>
<td>" . $bookID . "</td>
</tr>
<tr>
<td>ISBN10</td>
<td><input type=\"text\" name=\"ISBN10\" value=\"" . htmlentities(stripslashes($ISBN10)) . "\">" . $ISBN10V . $ISBN10G . "</td>
</tr>
<tr>
<td>ISBN13</td>
<td><input type=\"text\" name=\"ISBN13\" value=\"" . htmlentities(stripslashes($ISBN13)) . "\">" . $ISBN13V . $ISBN13G . "</td>
</tr>
<tr>
<td>Title</td>
<td><input type=\"text\" name=\"Title\" value=\"" . htmlentities(stripslashes($stmt['title'])) . "\"></td>
</tr>
<tr>
<td>Author</td>
<td><input type=\"text\" name=\"Author\" value=\"" . htmlentities(stripslashes($stmt['author'])) . "\"></td>
</tr>
<tr>
<td>Edition</td>
<td><input type=\"text\" name=\"Edition\" value=\"" . htmlentities(stripslashes($stmt['edition'])) . "\"></td>
</tr>
<tr>
<td>ImageID</td>
<td>
<img src=\"" . htmlentities(stripslashes($imageURL)) . "\" />
<br>
URL being sent to Cloudinary: <input type=\"text\" name=\"ImageID\" value=\"" . htmlentities(stripslashes($imageURL)) . "\">
</td>
</tr>
<tr>
<td>ImageState</td>
<td><input type=\"hidden\" name=\"ImageState\" value=\"" . $verification . "\">" . $verification . "</td>
</tr>
</table>";

	$nextAIHandle = mysql_query("SHOW TABLE STATUS LIKE 'sale'", $db);
	$nextAI = mysql_fetch_assoc($nextAIHandle);
	$saleID = $nextAI['Auto_increment'] ? $nextAI['Auto_increment'] : 1;

	if ($stmt['written'] == "Yes") {
		$written = "1";
	} else {
		$written = "0";
	}

	switch($stmt['condition']) {
		case "Excellent" :
			$condition = "5";
			break;

		case "Very Good" :
			$condition = "4";
			break;

		case "Good" :
			$condition = "3";
			break;

		case "Fair" :
			$written = "2";
			$condition;

		case "Poor" :
			$condition = "1";
			break;
	}
	
//Update the ID of the user
	$WPMerchantHandle = mysql_query("SELECT * FROM `wp_users` WHERE `id` = '{$stmt['userID']}'", $db);
	$WPMerchant = mysql_fetch_assoc($WPMerchantHandle);
	$MerchantIDText = $WPMerchant['ID'] ? ($WPMerchant['ID'] . " [name: " . $WPMerchant['display_name'] . "]") : "<span style=\"color:#FF0000;\">DNE [ID: " . $stmt['userID'] . "]</span>"; 
	$MerchantIDVal = $WPMerchant['ID'] ? $WPMerchant['ID'] : "0";
	
	echo "<h3>sale</h3>

<table>
<tr>
<td><u>SaleID</u></td>
<td>" . $saleID . "</td>
</tr>
<tr>
<td>BookID</td>
<td>" . $bookID . "</td>
</tr>
<tr>
<td>Merchant</td>
<td><input type=\"hidden\" name=\"Merchant\" value=\"" . htmlentities(stripslashes($MerchantIDVal)) . "\">" . $MerchantIDText . "</td>
</tr>
<tr>
<td>Upload</td>
<td><input type=\"hidden\" name=\"Upload\" value=\"" . htmlentities(date("Y-m-d H:i:s", stripslashes($stmt['upload']))) . "\">" . date("Y-m-d H:i:s", stripslashes($stmt['upload'])) . "</td>
</tr>
<tr>
<td>Sold</td>
<td><input type=\"hidden\" name=\"Sold\" value=\"" . htmlentities(stripslashes($stmt['sold'])) . "\">" . $stmt['sold'] . "</td>
</tr>
<tr>
<td>Price</td>
<td><input type=\"hidden\" name=\"Price\" value=\"" . htmlentities(stripslashes($stmt['price'])) . "\">" . $stmt['price'] . "</td>
</tr>
<tr>
<td>Condition</td>
<td><input type=\"hidden\" name=\"Condition\" value=\"" . htmlentities(stripslashes($condition)) . "\">" . $condition . "</td>
</tr>
<tr>
<td>Written</td>
<td><input type=\"hidden\" name=\"Written\" value=\"" . htmlentities(stripslashes($written)) . "\">" . $written . "</td>
</tr>
<tr>
<td>Comments</td>
<td><input type=\"hidden\" name=\"Comments\" value=\"" . htmlentities(stripslashes($stmt['comments'])) . "\">" . stripslashes($stmt['comments']) . "</td>
</tr>
</table>";

//Display purchase info
	echo "<h3>bookcourses</h3>
	
";

	foreach($stmtAll as $course) {
		$codeHandle = mysql_query("SELECT `CourseID`, `Code` FROM `courses` WHERE `Code` = (SELECT `course` FROM `ffi_be_bookcategories` WHERE `id` = '{$course['course']}')");
		$code = mysql_fetch_assoc($codeHandle);
		
		echo "<table>
<tr>
<td>SaleID</td>
<td>" . $saleID . "</td>
</tr>
<tr>
<td>Course</td>
<td><input type=\"hidden\" name=\"Course[]\" value=\"" . htmlentities(stripslashes($code['CourseID'])) . "\">" . $code['CourseID'] . " [" . $code['Code'] . ", former ID: " . $course['course'] . "]</td>
</tr>
<tr>
<td>Number</td>
<td><input type=\"hidden\" name=\"Number[]\" value=\"" . htmlentities(stripslashes($course['number'])) . "\">" . $course['number'] . "</td>
</tr>
<tr>
<td>Section</td>
<td><input type=\"hidden\" name=\"Section[]\" value=\"" . htmlentities(stripslashes($course['section'])) . "\">" . $course['section'] . "</td>
</tr>
</table>
";
	}

//Display the purchase info
	if ($stmt['sold']) {
		$purchaseHandle = mysql_query("SELECT * FROM `ffi_be_purchases` WHERE `bookID` = '{$stmt['id']}'");
		$purchase = mysql_fetch_assoc($purchaseHandle);
		
		$WPBuyerHandle = mysql_query("SELECT * FROM `wp_users` WHERE `user_login` = (SELECT `emailAddress1` FROM `ffi_be_users` WHERE `id` = '{$purchase['buyerID']}')", $db);
		$WPBuyer = mysql_fetch_assoc($WPBuyerHandle);
		$BuyerIDText = $WPBuyer['ID'] ? ($WPBuyer['ID'] . " [name: " . $WPBuyer['display_name'] . ", former ID: " . $purchase['buyerID'] . "]") : "<span style=\"color:#FF0000;\">DNE [ID: " . $purchase['buyerID'] . "]</span>"; 
		$BuyerIDVal = $WPBuyer['ID'] ? $WPBuyer['ID'] : "0";
		
		echo "<h3>purchases</h3>
		
<table>
<tr>
<td>BookID</td>
<td>" . $bookID . "</td>
</tr>
<tr>
<td>Price</td>
<td>" . $stmt['price'] . "</td>
</tr>
<tr>
<td>Buyer</td>
<td><input type=\"hidden\" name=\"Buyer\" value=\"" . htmlentities(stripslashes($BuyerIDVal)) . "\">" . $BuyerIDText . "</td>
</tr>
<tr>
<td>Merchant</td>
<td>" . $MerchantIDText . "</td>
</tr>
<tr>
<td>Time</td>
<td><input type=\"hidden\" name=\"Time\" value=\"" . htmlentities(date("Y-m-d H:i:s", stripslashes($purchase['time']))) . "\">" . date("Y-m-d H:i:s", stripslashes($purchase['time'])) . "</td>
</tr>
</table>
";
	} else {
		echo "<h3 style=\"color:#FF0000;\">This book has not been purchased</h3>";
	}
	
//Display information sent to IndexDen
	echo "<h2>Information being sent to IndexDen:</h2>\n";

	if ($stmt['sold']) {
		echo "<p style=\"color:#FF0000;\">Nothing, this book has been purchased</p>";
	} else {
		echo "<table>
<tr>
<td><u>doc_id</u></td>
<td>" . $saleID . "</td>
</tr>
<td>variables</td>
<td>";

		print_r(array("Title" => stripslashes($stmt['title']), "Author" => stripslashes($stmt['author']), "ISBN10" => $ISBN10, "ISBN13" => $ISBN13));
		
		echo "</td>
</tr>
</table>";
	}
	
	echo "<input type=\"submit\" value=\"Submit\"/>
</form>";

//Display all avaliable book cover images
	echo "<h2>More covers:</h2>\n";

	$imgHandle = mysql_query("SELECT * FROM ffi_be_books WHERE ISBN = '{$ISBN10}' OR ISBN = '{$ISBN13}'", $db);

	while ($img = mysql_fetch_assoc($imgHandle)) {
		echo "<img src=\"" . stripslashes($img['imageURL']) . "\" />\n";
	}
?>