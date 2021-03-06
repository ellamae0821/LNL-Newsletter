<?php  
	// ini_set( 'display_errors', 1 );
	// ini_set( 'display_startup_errors', 1 );
	// error_reporting( E_ALL );

	if($_SERVER['REMOTE_ADDR'] != "66.162.249.170"){
		header("Location: http://longs.staradvertiser.com/");
	}

	require_once("resources/Email_Service.php");
	// $email_service = new Email_Service( "8d8acc947a624660a8b41153b6593d29", false, true ); // LIVE
 	$email_service = new Email_Service( "-", false, true ); // SANDBOX

	$subscribe_url = "https://api.iterable.com/api/lists/subscribe?api_key=-";
	$unsubscribe_url = "https://api.iterable.com/api/lists/unsubscribe?api_key=-";

	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function print_pre($object) {
		?><pre><?php print_r($object); ?></pre><?php
	}

	function subscribe_to_list($list_ID, $subscriber_array, $url){
		$params = array();
		$params['listId'] = $list_ID; 
		$params['subscribers'] = $subscriber_array;
		$payload = json_encode($params);
		// print_pre(json_encode($params));
		
		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_URL, $url);
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		$result_details = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $result_details;
		// print_pre($result);
		// print_pre($result_details);
	}

 	$mailing_lists_location = array(
									134004 => "oahu",
									134006 => "maui",
									135971 => "kauai",
									135968 => "kona",
									135972 => "hilo"
							  );

	$emailErr = "";
	$email = "";
	$locErr = "";
	$location_id = "";
	$redErr = "";



	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if ( empty($_POST["email"]) ) {
			$emailErr = "Email is required";
		} else {
			$email = test_input($_POST["email"]);
			// check if e-mail address is well-formed
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  		$emailErr = "Invalid email format"; 
			}
		}

		if ( empty($_POST["listID"]) ) {
			$locErr = "Please choose one location";
		} else {
			$location_id = $_POST['listID'];
		}

//	GET Location name
		$location_name = $mailing_lists_location[$location_id];


		$user = $email_service->user_get_by_email( $email );
		$user_subscriptions = array();

		$subscriber[] = array(
				'email' => $email, 
				'dataFields' => new ArrayObject(),
				'userId' => ""
				);
		$subscriber_unsub = array(
				'email' => $email, 
				);

//	UNSUBSCRIBE from the current location
		if( !empty($user['content']['user']['dataFields']['longs']['location']) )
		{
			$existing_subscription = $user['content']['user']['dataFields']['longs']['location'];
			$list_to_unsub = array_search($existing_subscription, $mailing_lists_location);
			// var_dump($list_to_unsub);
			$unsubscribe_result = $email_service->list_unsubscribe($list_to_unsub, $subscriber_unsub, false);
			// var_dump($subscriber_unsub);
			// print_pre($unsubscribe_result);

		}

//	Update the user field on ITERABLE
		$add_longs = array('location' => $location_name);
		$update_result = $email_service->user_update_by_email($email,  array("longs" => $add_longs));
		$result_code = $update_result['response_code'];

//	Subscribe the user the ITERABLE List
		$subscribe_result =	subscribe_to_list( (int)$location_id, $subscriber, $subscribe_url);

		// print_pre($subscribe_result); 

		if ( $subscribe_result && $result_code == 200) {
			header("Location: http://longs.staradvertiser.com/thank-you.php");
		}else{
			$redErr = "Unable to process your request, please try again.";
		}
	}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, width=device-width" />
	<meta name="description" content="&quot;Make Longs a Part of Your Day&quot;">
	<meta name="keywords" content="&quot;Make Longs a Part of Your Day&quot;">

	<title>&quot;Make Longs a Part of Your Day&quot; | Longs Hawaii</title>

	<link rel="stylesheet" href="ella.css" type="text/css" media="screen" charset="utf-8">
	<link href='https://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
</head>

<body>

<div class="container" id="wrapper">
  	<form action="" method="post" name="sign up for beta form" id="signup">
      	<div class="nl_header">
         	<p>Sign Up For Our Newsletter</p>
      	</div>
      	<div class="nl_description">
        	<p style="font-family: 'Lato', sans-serif; font-size: 18px; line-height: 25px; margin: 0 10px;">Select your location and enter your email to start receiving your Longs ad every Sunday!</p>
      	</div>
      	<span style="color: red; height: 20px"> <?php echo $locErr;?></span> <br>
      	<div style="text-align: left; font-size: 21px; padding-left: 40%"> 
			<?php
				foreach ($mailing_lists_location as $subscription_list_id => $subscription_list_name) {
					?>
						<input type="radio" name="listID" value="<?php echo $subscription_list_id ?>" /> 
						<?php echo ucfirst($subscription_list_name); ?><br> 
					<?php
				}	
			?><br><br>
		</div>
		<span style="color: red; height: 20px"> <?php echo $emailErr;?></span> <br>
      <div class="input">
        <input type="text" class="button" id="email" name="email" placeholder="NAME@EXAMPLE.COM">
        <input type="submit" class="button" id="submit" name="submit" value="SIGN UP">
      </div>
      
      <div>
      	<p style="font-size: 10px">By clicking sign up, you are agreeing to our <a href="http://www.oahupublications.com/privacy_policy/">Privacy policy</a> and <a href="http://www.oahupublications.com/terms-of-service/">Terms of Service.</a></p>
      </div>
    </form>
</div>
	
</body>
</html>
