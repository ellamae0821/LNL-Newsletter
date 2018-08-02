<?php 
	
	// ini_set( "display_errors", 1 );
	// ini_set( "display_startup_errors", 1 );
	// error_reporting( E_ALL );

	if($_SERVER['REMOTE_ADDR'] != "-"){
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

	$email = $_POST["email"];
	$locId = $_POST["listID"];

	header("Location: http://longs.staradvertiser.com/thank-you.php?email=" . $email."&locId=" . $locId);
	}

	##############################################
	########## MySQL Connection ##################
	
	require_once ( 'mysql.php' );
	 
	function saleDays($start, $end) {
		$start_ts = strtotime($start);
		$end_ts = strtotime($end);
		$diff = $end_ts - $start_ts;
		return round($diff / 86400) + 1;
	}

	//*************************************
	//   S P E C I A L  L O N G S   A D S
	//*************************************
	
	$special_start_date = "01/01/18"; // the start date of the special ad
	$special_end_date = "01/15/18"; // the end date of the special ad
	$special_ad = FALSE; // Change this to TRUE if the ad is not a weekly ad


	if(!mysql_connect($host, $user, $pass) || !mysql_select_db($db))
	{
		$message .= "No Database Connection!<br /><br />";
	}
	
	// DECLARE HAWAII TIMEZONE
	date_default_timezone_set ("Pacific/Honolulu");


	##############################################
	########## Page Variables ####################
	
	$dayOfWeek = date("w");
	$daysTilSat = (6 - $dayOfWeek);
	$sundayDateDisplay = (date("l")=="Sunday")?(date("n/d/Y")):(date("n/d/Y", strtotime("last Sunday")));
	$sundayDateFilename = (date("l")=="Sunday")?(date("md")):(date("md", strtotime("last Sunday")));
	$saturdayDateDisplay = (date("l")=="Saturday")?(date("n/d/Y")):(date("n/d/Y", strtotime("+$daysTilSat days")));
	$message = "";
	
	if ( true || !$special_ad ) {
		$displayDates = $sundayDateDisplay . " - " . $saturdayDateDisplay;
		$savings_days = 7;	
		$sale_days = "SUNDAY thru SATURDAY";
	} else { // It's Christmas time! Specials run from Sunday to Wednesday and Thursday thru Saturday
		$sundayToWed = array("Sunday","Monday","Tuesday");
		
		if (in_array(date("l"),$sundayToWed))
		{
			$startDay = "SUNDAY";
			$endDay = "TUESDAY";
			$daysTilWed = (3 - $dayOfWeek);
			$startDateDisplay = (date("l")=="Sunday")?(date("n/d/Y")):(date("n/d/Y", strtotime("last Sunday")));
			$endDateDisplay = (date("l")=="Wednesday")?(date("n/d/Y")):(date("n/d/Y", strtotime("+$daysTilWed days")));
			$mobileDateFilename = $sundayDateFilename;
			
		} else {
			$startDay = "WEDNESDAY";
			$endDay = "SATURDAY";
			$daysTilSat = (6 - $dayOfWeek);
			$startDateDisplay = (date("l")=="Wednesday")?(date("n/d/Y")):(date("n/d/Y", strtotime("last Wednesday")));
			$endDateDisplay = (date("l")=="Saturday")?(date("n/d/Y")):(date("n/d/Y", strtotime("+$daysTilSat days")));
			$mobileDateFilename = (date("l")=="Wednesday")?(date("md")):(date("md", strtotime("last Wednesday")));
		}
				
		$displayDates = $startDateDisplay . " - " . $endDateDisplay;
		$savings_days = saleDays($startDateDisplay,$endDateDisplay);
		$sale_days = "$startDay thru $endDay";
	}
	##############################################
	
	if ( isset($_POST["action"]) && $_POST["action"] == "do")
	{
		if ($_POST["zip"])
		{
			if (strlen($_POST["zip"]) < 5)
			{
				$message .= "Zip MUST be five characters in length.<br /><br />";
			} 
			 
			if (!is_numeric($_POST["zip"]))
			{
				$message .= "Zip MUST be all numeric characters.<br /><br />";
			}
			
			if (strlen($message)==0)
			{
				$sql = "SELECT chr_island FROM tbl_zip_codes WHERE num_zip = '".$_POST["zip"]."'";
				$result = mysql_query($sql);
				$data = mysql_fetch_assoc($result); 
				
				$island = $data["chr_island"];    
								
				if(strlen($island) > 0)
				{
					$special_start_date_time = strtotime( $special_start_date );
					$special_end_date_time = strtotime( $special_end_date );
					$today_time = time();

					if ($special_ad && ( $today_time >= $special_start_date_time && $today_time <= $special_end_date_time ) )
					{
						header("Location: choose.php?loc=$island");
					} else {
						header("Location: /$island/$sundayDateFilename/");
					}
					
				} else {
					$message .= "The zip code you've entered is not a Hawaii zip code. Please try again.";
				}
			}
			
		} else {
			$message = "You MUST enter a zip code.";
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

	<link rel="stylesheet" href="style-ricky.css" type="text/css" media="screen" charset="utf-8">
	<link href="custombox/dist/custombox.min-rr.css" rel="stylesheet">
	<link href="ricky.css" rel="stylesheet">
	<script src="custombox/dist/custombox.min.js"></script>
	<script src="custombox/dist/custombox.legacy.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>	
	<script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
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

	function setCookie(cname, cvalue, exdays) {
	    var d = new Date();
	    d.setTime(d.getTime() + (exdays*24*60*60*1000));
	    var expires = "expires="+ d.toUTCString();
	    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}



</script>

<!-- AAM Site Certifier starts plowing -->
<script type="text/javascript">
    ;(function(p,l,o,w,i,n,g){if(!p[i]){p.GlobalAamNamespace=p.GlobalAamNamespace||[];
    p.GlobalAamNamespace.push(i);p[i]=function(){(p[i].q=p[i].q||[]).push(arguments)
    };p[i].q=p[i].q||[];n=l.createElement(o);g=l.getElementsByTagName(o)[0];n.async=1;
    n.src=w;g.parentNode.insertBefore(n,g)}}(window,document,"script","//aamcftag.aamsitecertifier.com/aam.js","aamsitecertifier"));

    window.aamsitecertifier('newTracker', 'cf', 'aamcf.aamsitecertifier.com', {
        idWeb: '206'
    });

    window.aamsitecertifier('trackPageView');
</script>
<!-- AAM Site Certifier stops plowing -->
	
	


</head>

<body>

	<div class="container" id="wrapper">

		<script type="text/javascript">
			jQuery(function($){
				$( ".cross" ).hide();
				$( "#nav-mobile" ).hide();
				$( ".hamburger" ).click(function() {
				$( "#nav-mobile" ).slideToggle( "slow", function() {
				$( ".hamburger" ).hide();
				$( ".cross" ).show();
				});
				});

				$( ".cross" ).click(function() {
				$( "#nav-mobile" ).slideToggle( "slow", function() {
				$( ".cross" ).hide();
				$( ".hamburger" ).show();
				});
				});
			})
		</script>
		
		<!-- HAMBURGER MOBILE NAV -->
		<div id="header-mobile">
		<button class="hamburger">&#9776;</button>
		  <button class="cross">&#735;</button>
		  <a href="http://longs.staradvertiser.com"><img id="hamlogo" alt="hawaiijobs" src="images/longsheader.png"/></a>
			<div id="sevendays-mobile">
			<p id="days-mobile">7 DAYS OF SAVINGS!</p>
			<p id="sale-mobile">Sale <?php echo (!$special_ad)?("SUNDAY thru SATURDAY"):($sale_days); ?><br>
				<?php echo $displayDates; ?><br>
			</p>
				<a href="locations.php">STORE LOCATIONS</a>
			</div>
		</div>
		<div id="nav-mobile">
				<ul>
				  <li><a href="index.php">HOME</a></li>
				  <li><a href="locations.php">LOCATIONS</a></li>
				  <li><a href="https://www.cvs.com/pharmacy/pharmacy-homepage.jsp" target="_blank">PRESCRIPTION CENTER</a></li>
				  <li><a href="http://www.cvsphoto.com/home" target="_blank">PHOTO CENTER</a></li>
					<li><a href="http://jobs.cvshealth.com/" target="_blank">CAREERS</a></li>
				</ul>
		</div>
		<!--END-->	

		<div id="header">
			<a href="http://longs.staradvertiser.com"><img alt="hawaiijobs" src="images/longsheader.png"/></a>
		</div>

		
		<div id="sevendays">
			<p id="days"><?php echo $savings_days; ?> DAYS OF SAVINGS!</p>
			<p id="sale">Sale <?php echo (!$special_ad)?("SUNDAY thru SATURDAY"):($sale_days); ?><br>
				<?php echo $displayDates; ?><br>
			</p>
			<a href="locations.php">STORE LOCATIONS</a>
		</div>	

		<!--<div id="rightear">
			<img alt="hawaiijobs" src="images/hawaiicars-300x100-1.jpg"/>
		</div>-->

		<div id="nav">
	        <ul>
	          <li><a href="index.php">HOME</a></li>
	          <li><a href="locations.php">LOCATIONS</a></li>
	          <li><a href="https://www.cvs.com/pharmacy/pharmacy-homepage.jsp" target="_blank">PRESCRIPTION CENTER</a></li>
	          <li><a href="http://www.cvsphoto.com/home" target="_blank">PHOTO CENTER</a></li>
				<li><a href="http://jobs.cvshealth.com/" target="_blank">CAREERS</a></li>
	        </ul>
	    </div>

		
				
		<div id="rightimg">
			<img class="object" alt="right image" src="images/longs_side.jpg"/>
	     </div>
	     

		<div id="emailform">
			<?php 

				if ($message) 
				{ 
					?>
						<h4 id="errMsg"><?php echo $message; ?></h4>
					<?php 
				} 
			?>

			<form id="enterzip" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
				ENTER YOUR ZIP CODE:<br>

				<input type="hidden" name="action" value="do" />
			  	<input type="tel" name="zip" size="10" maxlength="5" value="<?php echo ( empty($_POST["zip"]) ? '' : $_POST["zip"] ); ?>">
			    <br>
			  <input type="submit" value="SUBMIT">
			</form> 
		</div>   
	    
	    <div id="modal" style="display: none;">

	    	<div class="close" onclick="Custombox.modal.close();">+</div>
    	  	<form action="" method="post" name="sign up for beta form" id="signup">
		      	<div class="nl_header">
		         	<p>Sign Up For Our Newsletter</p>
		      	</div>
		      	<div class="nl_description">
		        	<p style="font-family: 'Lato', sans-serif; font-size: 18px; line-height: 25px; margin: 0 10px;">Select your location and enter your email to start receiving your Longs ad every Sunday!</p>
		      	</div>
		      	<!-- span style="color: red; height: 20px"> <?php echo $locErr;?></span> <br> -->
				<span class="modal-err" id="redErr" style="color: red; font-size:14px"> </span> <br>
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
			  <span id="emErr" style="color: red; font-size:14px"></span>
		      <div class="input">
		        <input type="text" class="button" id="email" name="email" placeholder="NAME@EXAMPLE.COM">
		        <input type="submit" class="button" id="submit" name="submit" value="SIGN UP" onclick="return (check_radio() && check_email());">
		      </div>
		      
		      <div>
		      	<p style="font-size: 10px">By clicking sign up, you are agreeing to our <a href="http://www.oahupublications.com/privacy_policy/">Privacy policy</a> and <a href="http://www.oahupublications.com/terms-of-service/">Terms of Service.</a></p>
		      </div>
		    </form>
	    </div>
		<div  id="btn-subscribe">+Subscribe to our newletter</div>

	<footer>
		<!-- Start of StatCounter Code -->
<script type="text/javascript">
	var sc_project=6148570; 
	var sc_invisible=1; 
	var sc_security="0da2004d"; 


	$(window).on('load', function(){
		var myCookie = Cookies.get('modalShowed');
		if(myCookie){
			// alert('Modal is hidden ' + myCookie)
			console.log('must hide')
		}
		
		// var timeExp = new Date(new Date().getTime() + 1 * 60 * 1000);

		if(myCookie === undefined){
			Cookies.set('modalShowed', 'true' , {
				expires: 1
			});
			console.log('setting cookie , modal is shown' );
			new Custombox.modal({
				content: {
				effect: 'slide',
				target: '#modal',
				container: '#content',
				animateFrom: 'bottom',
				animateTo: 'bottom',
				positionX: 'right',
				positionY: 'bottom',
				speedIn: 1000,
				speedOut: 400,
				delay: 2000,
				},
				loader: {
					active: true,
				},
				overlay:{
					active: false,
				}
			}).open();
		}
	});


	function check_radio(){
		var radios = document.getElementsByName("listID");
		
		for(i=0; i<radios.length; i++){ 
			if(radios[i].checked == true)
				return true;
		}
		document.getElementById("redErr").innerHTML="* * Please select a location"
		return false;
	}
	function validateEmail(email) {
	    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    return re.test(String(email).toLowerCase());
	}

	function check_email(){
		var emailTxt = $("#email").val();

		if(validateEmail(emailTxt)){
			return true;
		} else {
			document.getElementById("emErr").innerHTML=" * * Please enter a valid email"
			return false;
		}
	}


</script>



<script type="text/javascript">
	document.getElementById('btn-subscribe').addEventListener('click', function() {
		var buttonModal = new Custombox.modal({
			content: {
			effect: 'slide',
			target: '#modal',
			container: '#content',
			animateFrom: 'bottom',
			animateTo: 'bottom',
			positionX: 'right',
			positionY: 'bottom',
			speedIn: 300,
			speedOut: 300,
			},
			loader: {
				active: true,
			},
			overlay:{
				active: false,
			}
			});
		buttonModal.open();
	});
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
