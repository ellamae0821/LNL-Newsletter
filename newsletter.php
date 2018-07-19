<?php  
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ALL );

		require_once("resources/Email_Service.php");
		// $email_service = new Email_Service( "-", false, true ); // LIVE
	 	$email_service = new Email_Service( "-", false, true ); // SANDBOX

		$subscribe_url = "h-";


		function test_input($data) {
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
			return $data;
		}
		function print_pre($object) {
			?><pre><?php print_r($object); ?></pre><?php
		}

	 	$mailing_lists_location = array(
										134004 => "oahu",
										134006 => "maui",
										135971 => "kauai",
										135968 => "kona",
										135972 => "hilo"
								  );

		// define variables and set to empty values
		$emailErr = "";
		$email = "";
		$locErr = "";
		$chosen_location = "";

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
				$chosen_location = $_POST['listID'];
			}
	//	GET Location name
			$location_name = $mailing_lists_location[$chosen_location];

	//	Update the user field on ITERABLE
			$add_longs = array('location' => $location_name);
			$update_result = $email_service->user_update_by_email($email,  array("longs" => $add_longs));
			$result_code = $update_result['response_code'];
			echo "update_result : $result_code<br>";

			if ( $result_code == 200) {
				header("Location: http://longs.staradvertiser.com/thank-you.php?loc=".$location_name);
			}else{
				$redirectErr = "Unable to process your request this time, please try again.";
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

	<link rel="stylesheet" href="style.css" type="text/css" media="screen" charset="utf-8">


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

<div class="container" id="wrapper">


<form action="" method="post">
	Signup for our Newsletter<br>
	<hr>

	<input type="text" name="email" placeholder="Email Address">
	<span style="color: red">* <?php echo $emailErr;?></span> <br>
	<?php
		foreach ($mailing_lists_location as $key => $value) {
			?>
				<input type="radio" name="listID" value="<?php echo $key ?>" /> 
				<?php echo ucfirst($value); ?><br> 
			<?php
		}	
	?>
	<span style="color: red"> <?php echo $locErr;?></span> <br>
	<input type="submit" name="submit" value="Sign up" /> <br>
	By clicking sign up, you are agreeing to our privacy policy and terms of use. 
</form>



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
