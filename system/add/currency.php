<?php
// currency.php
// 
// Domain Manager - A web-based application written in PHP & MySQL used to manage a collection of domain names.
// Copyright (C) 2010 Greg Chetcuti
// 
// Domain Manager is free software; you can redistribute it and/or modify it under the terms of the GNU General
// Public License as published by the Free Software Foundation; either version 2 of the License, or (at your
// option) any later version.
// 
// Domain Manager is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
// implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
// 
// You should have received a copy of the GNU General Public License along with Domain Manager. If not, please 
// see http://www.gnu.org/licenses/
?>
<?php
session_start();

include("../_includes/config.inc.php");
include("../_includes/database.inc.php");
include("../_includes/software.inc.php");
include("../_includes/auth/auth-check.inc.php");
include("../_includes/timestamps/current-timestamp.inc.php");

$page_title = "Adding A New Currency";
$software_section = "currencies";

// Form Variables
$new_name = $_POST['new_name'];
$new_abbreviation = $_POST['new_abbreviation'];
$new_notes = $_POST['new_notes'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if ($new_name != "" && $new_abbreviation != "") {
		
		$from = $new_abbreviation;
		$to = $_SESSION['session_default_currency'];
		$full_url = "http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=" . $from . $to ."=X";
		$handle = @fopen($full_url, "r");
			 
		if ($handle) {

			$handle_result = fgets($handle, 4096);
			fclose($handle);

		}
			
		$data = explode(",",$handle_result);
		$value = $data[1];
			
		$sql = "INSERT INTO currencies
				(currency, name, conversion, notes, insert_time) VALUES 
				('" . mysql_real_escape_string($new_abbreviation) . "', '" . mysql_real_escape_string($new_name) . "', '$value', '" . mysql_real_escape_string($new_notes) . "', '$current_timestamp')";
		$result = mysql_query($sql,$connection) or die(mysql_error());
		
		$_SESSION['session_result_message'] = "Currency Added<BR>";
		
		header("Location: ../currencies.php");
		exit;

	} else {
	
		if ($new_name == "") $_SESSION['session_result_message'] .= "Please Enter The Currency Name<BR>";
		if ($new_abbreviation == "") $_SESSION['session_result_message'] .= "Please Enter The Currency Abbreviation<BR>";

	}

}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?=$software_title?> :: <?=$page_title?></title>
<?php include("../_includes/head-tags.inc.php"); ?>
</head>
<body onLoad="document.forms[0].elements[0].focus()";>
<?php include("../_includes/header.inc.php"); ?>
<?php 
$sql = "SELECT currency, name
		FROM currencies
		WHERE currency = '" . $_SESSION['session_default_currency'] . "'";
$result = mysql_query($sql,$connection);

while ($row = mysql_fetch_object($result)) {
	$default_currency = $row->currency;
	$default_name = $row->name;
}
?>
<form name="add_currency_form" method="post" action="<?=$PHP_SELF?>">
<strong>Name ("<em><?=$default_name?></em>"):</strong><BR><BR>
<input name="new_name" type="text" value="<?=$new_name?>" size="50" maxlength="255">
<BR><BR>
<strong>Abbreviation ("<em><?=$default_currency?></em>"):</strong><BR><BR>
<input name="new_abbreviation" type="text" value="<?=$new_abbreviation?>" size="50" maxlength="3">
<BR><BR>
<strong>Notes:</strong><BR><BR>
<textarea name="new_notes" cols="60" rows="5"><?=$new_notes?></textarea>
<BR><BR><BR>
<input type="submit" name="button" value="Add This Currency &raquo;">
</form>
<?php include("../_includes/footer.inc.php"); ?>
</body>
</html>