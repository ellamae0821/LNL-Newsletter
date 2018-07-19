<?php  
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ALL );
	date_default_timezone_set('Pacific/Honolulu');






if ($_SERVER['REMOTE_ADDR']=="-") {

	function print_pre($object) {
		?><pre><?php print_r($object); ?></pre><?php
	}

	$today = date("Y-m-d");
	print_pre($today);
	// $sunday = strtotime( "previous sunday", strtotime($today));
	$sunday = date( 'md', strtotime( 'sunday last week' ) );
	print_pre($sunday);

	$location = strtolower($_GET["loc"]);
	
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
	Thank You for signing up! <br>
	Click <a href='-'> HERE </a>to start saving! <br><br>
	<a href='http://longs.staradvertiser.com/<?php echo $location?>/<?php echo $sunday ?>/html5forpc.html?page=0'><img src='-' style ="width: 50%"></a>

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

<?php }