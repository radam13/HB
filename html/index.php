<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> TRUE,
	'db_main'	=> TRUE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$currentpage = '';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();

ob_end_flush();
/* <HEAD> */ $head=''; // </HEAD>

/* PAGE TITLE */ $title='Helmar Brewing Co';

/* HEADER */ require('layout/header0.php');

function getRandomImage() {
	$relImageDir = "/img/banner_images/";
	$defaultImg = "/img/did-you-know-img-1-1.png";
	$imageDir = $_SERVER['DOCUMENT_ROOT'].$relImageDir;
	$imageFmt = ".png";
	$images = array();

	foreach (scandir($imageDir) as $image) {
		if (strpos($image, $imageFmt)) {
			array_push($images, $image);
		}
	}

	if (count($images) == 0) {
		return $defaultImg;
	} else {
		$imageIndex = mt_rand(0, count($images) - 1);
		return $relImageDir.$images[$imageIndex];
	}	
}

print'

	    <div class="hero">
	    	<div class="hero-inner">
				<div class="hero-twocol">
					<div class="l">	

                        <img src="/img/tag-2.png" alt="Helmar Brewing" style="margin-bottom:0px; ">
    
                        <img src="/img/logo-2.png" alt="Helmar Brewing" style="margin-bottom:15px!important;">
                        
                        <div class="mobile_hide" style="width: 100%">
                            <h2 style="color:#ffff80;font-weight:500;font-size:48px; font-family:Arial, Helvetica, sans-serif">Auctions Every Tuesday</h2>
             
                            <a class="card" href="https://helmarbrewing.com/marketplace/">See Cards Wanted Now!</a>
                           
                            <p style="font-size:32px; font-family:Arial, Helvetica, sans-serif""> The #1 Community for Sports Art!</p>
                        </div>
					</div>
					<div class="r mobile_hide" style="margin-top:8%;">
						<img class="rev-img"src="'.getRandomImage().'" style="margin-left:21%;"><br><br>
					</div>
				</div>
	    	</div>
	    </div>
	    
	    <!-- Hide the mobile_hide class for screen sizes less than 747px. This is when the two column layout is merged two just one. -->
	    <style>
	        @media only screen and (max-width: 747px) {
	            .mobile_hide {
	                display: none;
	            }
	            
	            .hero-inner {
	                padding: 0;
	            }
	            
	            .hero {
	                padding-bottom: 0;
	            }
	        }
        </style>
';



/* HEADER */ require('layout/header1.php');
print'



	    <div class="auctions">
';


if(isset($user)){
	 if( $user->login() === 1 || $user->login() === 2 ){
		 	// if logged in, do nothing!
		}else{
			print'
				<h1><a href="subscription/"><img src="/img/join_free.png" alt="Helmar Brewing"></a></h1>
			';
		}
	}

//	<h1>Current Auctions</h1>
//	<p id="auction_end">Auctions end on Tuesday evenings</p>
//	<ul id="auction_list">
//	</ul>
//<button id="auction_button">show more</button>

print'
		<h1><a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/" target="_blank"><img src="/img/current_auctions_banner.jpg" alt="Helmar Brewing Current Auctions Banner Link"></a></h1>
		<p></p>
	    </div>

	';

	// <script>
	// $( document ).ready(function() {
	// 	auctions(1);
	// });
	// </script>


/* FOOTER */ require('layout/footer1.php');

$db_auth->close();
$db_main->close();
?>