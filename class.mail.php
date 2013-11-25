<?php

class send_mail {	
	
	private $smtp_host;
	private $smtp_port;
	private $smtp_user;
	private $smtp_pass;
	private $smtp_from;
	private $query;
	private $result;

	public $mail_to;
	public $mail_body;
	public $mail_subject;
	public $mail_submit_ip_address;
	public $mail_submit_time;

	public $email_header;
	public $email_footer;
	public $email_post_info;


	public function get_message_body($type, $post_info) {	

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
	

		// Email header
		$this->email_header = "<html><body><div><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\"><tr><td><img src=\"http://domain.com/images/logo.jpg\"  height=\"58\" width=\"150\" alt=\"BEPO Logo\" /></td></tr><tr><td>";

		// Email
		$this->email_footer = sprintf("</td></tr><tr><td><p style=\"padding: 5px; background: #dfdfdf;\">This submission was sent from IP Address: <strong>%s on %s</strong>.</p></td></tr></table></div></body></html>", $this->mail_submit_ip_address, $this->mail_submit_time);

		$this->email_post_info = $post_info;

		// Get content from database

		if ($stmt = $mysqli->prepare("SELECT body_text, subject FROM email_body WHERE type = '".$type."' order by id desc limit 1")) {
			$stmt -> execute();
			$stmt -> bind_result($body_text, $subject);			
			
			while ($stmt->fetch()) {				
		        $this->mail_body = $this->email_header.$body_text.$this->email_post_info.$this->email_footer;
				$this->mail_subject = $subject;
	    	}

	    	$stmt->close();

		}
		
	}

	public function construct_mail($to, $type, $ip, $time, $post_info) {
		$this->mail_to = $to;
		$this->mail_submit_time = $time;
		$this->mail_submit_ip_address = $ip;
		$this->get_message_body($type, $post_info);
		// Email constructed. Send.
		$this->send_mail();
	}

	private function send_mail() {

		try {

		    $mandrill = new Mandrill('mandrill_key');
		    $message = array(
		        'html' => $this->mail_body,		        
		        'subject' => $this->mail_subject,
		        'from_email' => 'test@test.com',
		        'from_name' => 'Company Name',
		        'to' => array(
		            array(
		                'email' => $this->mail_to,
		                'name' => $this->mail_to,
		                'type' => 'to'
		            )
		        ),
		        'headers' => array('Reply-To' => 'test@test.com'),
		        'important' => false,
		        'auto_text' => true,
		        'auto_html' => null,
		        'inline_css' => null,
		        'url_strip_qs' => null,
		        'preserve_recipients' => null,
		        'view_content_link' => null,        
		        'tracking_domain' => null,
		        'signing_domain' => null,
		        'return_path_domain' => null,        
		    );
		    
		    $async = false;        
		    
		    $result = $mandrill->messages->send($message, $async);
		    
		} catch(Mandrill_Error $e) {		    
		    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();		    
		    throw $e;
		}

	}

	public function get_date(){
		$datetime = date("Y-m-d H:i:s"); 
		return $datetime;
	}

}