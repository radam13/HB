<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* SET PROTOCOL FOR REDIRECT */ if($use_https === TRUE){$protocol='https';}else{$protocol='http';}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth' 	=> TRUE,
		'db_main'	=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
	require_once('classes/phnx-user.class.php');
	require_once('libraries/stripe/Stripe.php');
//



/* PAGE VARIABLES */
	$currentpage = 'account/';
	$do = $_POST['do'];
//


$user = new phnx_user;
$user->checklogin(2);

/* <HEAD> */ $head='
	<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
	<script type="text/javascript">
		Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
	</script>
	<script type="text/javascript" src="/js/steve.js"></script>
'; // </HEAD>
/* PAGE TITLE */ $title='Account';

include 'layout/header.php';


if($user->login() === 0){
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/account/login/?redir=$currentpage",TRUE,303);
	ob_end_flush();
	exit;
}elseif($user->login() === 1){
	$user->regen();
	$db_auth->close();
	$db_main->close();
	header("Location: $protocol://$site/account/verify/?redir=$currentpage",TRUE,303);
	ob_end_flush();
	exit;
}elseif($user->login() === 2){
	$user->regen();
	
	
	
	
	$R_userdeets = $db_main->query("SELECT * FROM users WHERE userid = ".$user->id." LIMIT 1");
	if($R_userdeets !== FALSE){
		$userdeets = $R_userdeets->fetch_assoc();
		$R_userdeets->free();
		
		try {
		
			Stripe::setApiKey($apikey['stripe']['secret']);
		
			$cust = Stripe_Customer::retrieve($userdeets['stripeID']);
			
			if($cust['cards']['total_count'] !== 0){
				$card_info = $cust->cards->data;
				$card_num = '&#183;&#183;&#183;&#183; &#183;&#183;&#183;&#183; &#183;&#183;&#183;&#183; '.$card_info[0]['last4'];
				$brand = $card_info[0]['brand'];
				$exp_month = sprintf('%02d', $card_info[0]['exp_month']);
				$exp_year = $card_info[0]['exp_year'];
				$card_button_text = 'Update Card';
				$delete_disabled = false;
			}else{
				$card_button_text = 'Add Card';
				$delete_disabled = true;
			}
		} catch(Stripe_CardError $e) {
			
			// this still needs to show the form in case of expired cards that were already on the account
		
			$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception card error)';
		} catch (Stripe_InvalidRequestError $e) {
			$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception invalid request)';
		} catch (Stripe_AuthenticationError $e) {
			$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception authentication)';
		} catch (Stripe_ApiConnectionError $e) {
			$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception api connection)';
		} catch (Stripe_Error $e) {
			$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception general)';
		} catch (Exception $e) {
			$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception generic)';
		}
	}else{
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: user details fail)';
	}
	
	
	
	
	
	
	
	
	
	
	
	ob_end_flush();

	print'
	<div class="page-content">
		<div class="account">
			<h1 class="pagetitle">Account</h1>
		
			<div class="yourinfo">
				<h2>Your Info</h2>
				<dl>
					<dt>Username</dt>
					<dd>'.$user->username.'</dd>
					<dt>First Name</dt>
					<dd>'.$user->firstname.'</dd>
					<dt>Last Name</dt>
					<dd>'.$user->lastname.'</dd>
					<input type="button" value="Update Info" />
					<hr />
					<dt>Email</dt>
					<dd>'.$user->email.'</dd>
					<input type="button" value="Change Email" />
				</dl>
			</div>
		
			<div class="active-logins">
				<h2>Active Logins</h2>
				<ul>
		';
	
		foreach($user->get_active_logins() as $login){
			print'
					<li>
						Last accessed on <span>'.date("M j Y",$login['logintime']).'</span> at <span>'.date("g:ia",$login['logintime']).'</span><br />from IP address <span>'.$login['IP'].'</span> with <span>'.$login['browser']['parent'].'</span> on <span>'.$login['browser']['platform'].'</span>
						<input type="button" value="Log out device" />
					</li>
			';
		}

		print'
				</ul>
				<form action="logout/all/" method="post">
					<input type="submit" value="Invalidate all logins" />
				</form>
			</div>
			
			<div>
				<h2>Subscription</h2>
				<form action="" method="POST" id="payment-form">
					<div class="payment-errors" id="payment-errors">'.$msg.'</div>
					<label>Card Number</label>
					<input type="text" size="20" id="card_number" data-stripe="number" value="'.$card_num.'" />
					<label>CVC</label>
					<input type="text" size="4" id="cvc" data-stripe="cvc"/>
					<label>Expiration (MM/YYYY)</label>
					<input type="text" size="2" id="exp_month" data-stripe="exp-month" value="'.$exp_month.'"/>
					<span> / </span>
					<input type="text" size="4" id="exp_year" data-stripe="exp-year" value="'.$exp_year.'"/>
					<button id="add_update_card" type="submit">'.$card_button_text.'</button>
				</form>
				<button id="delete_card" onclick="deleteCard()"';if($delete_disabled){print' disabled';}print'>Delete Card</button>
			</div>
		</div>
	</div>
	';
	
	
}else{
	ob_end_flush();
	$db_auth->close();
	$db_main->close();
	print'You created a tear in the space time continuum.';
	exit;
}

$db_auth->close();
$db_main->close();

include 'layout/footer.php';

?>