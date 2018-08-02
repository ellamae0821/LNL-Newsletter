<?php  
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ALL );
	date_default_timezone_set('Pacific/Honolulu');

	require_once("resources/Email_Service.php");
 	$email_service = new Email_Service( "-", false, true ); 
 	$mailing_lists_location = array(
									134004 => "oahu",
									134006 => "maui",
									135971 => "kauai",
									135968 => "kona",
									135972 => "hilo"
							  );

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



if ($_SERVER['REMOTE_ADDR']=="-") {


	$email = $_GET["email"];
	$location_id = $_GET['locId'];


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


}


/*	$sunday = date( 'md', strtotime( 'sunday last week' ) );
	// print_pre($sunday);
	$location = strtolower($_GET["loc"]);
	if ( empty($location) ){
//	location defaults to oahu (in case, where user / designers directly visits http://longs.staradvertiser.com/thank-you.php)
		$location = "oahu";
	}
	// header('refresh:5; url=http://longs.staradvertiser.com/');*/


?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, width=device-width" />
	<meta name="description" content="&quot;Make Longs a Part of Your Day&quot;">
	<meta name="keywords" content="&quot;Make Longs a Part of Your Day&quot;">

	<title>&quot;Make Longs a Part of Your Day&quot; | Longs Hawaii</title>

	<link rel="stylesheet" href="style.css" type="text/css" media="screen" charset="utf-8">
	<link rel="stylesheet" href="ella_copy.css" type="text/css" media="screen" charset="utf-8">


	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>	

	<!-- Google Analytics  -->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-16272709-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<!-- AAM Site Certifier starts plowing -->

</head>

<body>

<div class="container" id="wrapper" style="height:100%;">
	<?php
		if ( $subscribe_result && $result_code == 200) { ?>
			<h1>Thank You for signing up! YEAY!</h1> <br>
			<p>Please wait while we redirect you ...</p>
	<?php }else {
		echo "nope buddy! but you!";
		include 'newsletter.php';
	} ?>
<!-- <div> -->

	<!-- <a href='http://longs.staradvertiser.com/<?php echo $location?>/<?php echo $sunday ?>/'><img src='http://longs.staradvertiser.com/<?php echo $location?>/<?php echo $sunday ?>/content/medium/page1.jpg' style ="width: 50%"></a> -->

</div>



	<footer>
		<!-- Start of StatCounter Code -->
<script type="text/javascript">
var sc_project=6148570; 
var sc_invisible=1; 
var sc_security="0da2004d"; 
</script>

<script type="text/javascript"
src="http://www.statcounter.com/counter/counter.js"></script><noscript><div
class="statcounter"><a title="godaddy web stats"
href="http://www.statcounter.com/godaddy_website_tonight/"
target="_blank"><img class="statcounter"
src="http://c.statcounter.com/6148570/0/0da2004d/1/"
alt="godaddy web stats" ></a></div></noscript>
<!-- End of StatCounter Code -->
		
		<a href="http://longs.staradvertiser.com" target="_blank"><img alt="longs now CVS" src="images/longs_now_cvs.jpg"/></a><br>
		<p>Problems viewing this page? Email us at <a style="color: white;" href="mailto:webmasters@staradvertiser.com">webmasters@staradvertiser.com</a></p>	

	</footer>
	
	
	</div>	
	
</body>
</html>

