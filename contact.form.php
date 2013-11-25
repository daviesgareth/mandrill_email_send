<?php 

	require_once('Mandrill.php');
	require_once('class.mail.php'); 
	require_once('class.validate.email.php');


	/*

	1) Get the post data
	2) Validate it
	3) Store it in the DB
	4) Send receipt emails

	*/

	// Database Information

		$dbhost = "localhost";
		$dbname = "dbname";
		$dbuser = "dbuser";
		$dbpass = "dbpass";

	// Admin Email

		$admin_email = "test@test.com";

	// Connect to database

		$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

		if (mysqli_connect_errno()) {
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}

	// Get POST data and escape it

		foreach($_POST as $key => $value) {
			if (isset($$key)) continue;
			$$key = $mysqli->real_escape_string($value);
			}

		$ip_address = $_SERVER['REMOTE_ADDR'];
		$time = date('l jS \of F Y h:i:s A');

	// Let's validate the POST

		// 1) Make sure POST data is there.

			if ($email == "") {
				$error_message .= "<p>Please enter an email address.</p>";
				}

			if ($first_name == "") {
				$error_message .= "<p>Please enter your first name.</p>";
				}

			if ($last_name == "") {
				$error_message .= "<p>Please enter your last name.</p>";
				}

			if ($country == "") {
				$error_message .= "<p>Please enter your last name.</p>";
				}

			if ($message == "") {
				$error_message .= "<p>Please enter a message.</p>";
				}		

	// See if email address is already in the database

	if ($stmt = $mysqli->prepare("SELECT id FROM contact_submissions WHERE email = ?")) {
		$stmt -> bind_param('s', $email);
		$stmt -> execute();
		$stmt -> store_result();
		$num_rows = $stmt->num_rows();
		
		// If email address already in database, don't insert. Display message.

		if($num_rows > 0){
			
			echo "<h2 id=\"response-error\">Error</h2>
				<p>You have already submitted a request.</p>
				<p>If you need to contact us urgently, please email us on test@test.com</p>";
		
		} else if($error_message == "") {

			echo "<h2 id=\"response-success\">Success</h2>
				<p>We have received your information. Thanks for contacting us.</p>
				<p>We will email you a receipt of your message.</p>";

		} else {

			echo "<h2 id=\"response-error\">Error</h2>";
			echo "<p>There were some problems with your form, please check the below messages and try again</p>";
			echo "<strong>".$error_message."</strong>";		

		}

		$post_info = "<br/><br/><table cellpadding=2' cellspacing='0' border='0'>
			<tr>
				<td><strong>Name:</strong></td>
				<td>".$title." ".$first_name." ".$last_name."</td>
			</tr>
			<tr>
				<td><strong>Email:</strong></td>
				<td>".$email."</td>
			</tr>
			<tr>
				<td><strong>Number:</strong></td>
				<td>".$contact_number."</td>
			</tr>
			<tr>
				<td><strong>Country:</strong></td>
				<td>".$country."</td>
			</tr>
			<tr>
				<td><strong>Message:</strong></td>
				<td>".$message."</td>
			</tr>
		</table>";

		$stmt -> close();

		if(($num_rows == 0) && ($error_message == "")) {
			
			if ($stmt = $mysqli->prepare("INSERT INTO contact_submissions (`title`, `first_name`, `last_name`, `email`, `country`, `phone`, `message`, `ip_address`) VALUES (?,?,?,?,?,?,?,?)")) {
				$stmt -> bind_param('ssssssss', $title, $first_name, $last_name, $email, $country, $contact_number, $message, $ip_address);
				$stmt -> execute();
				$stmt -> close();									
				}							

			// Send emails

			$mail_send = New send_mail();

			// Send submisson to admin
			$mail_send->construct_mail($admin_email, "confirmation", $ip_address, $time, $post_info);

			// Send receipt to user
			$mail_send->construct_mail($email, "receipt", $ip_address, $time, $post_info);

		}		
			
	}


?>
