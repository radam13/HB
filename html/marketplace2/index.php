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
$currentpage = 'marketplace2/';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();


ob_end_flush();
/* <HEAD> */ $head='
<script src="https://helmarbrewing.com/marketplace2/_marketplace.js"></script>
<script src="https://cdn.ckeditor.com/4.9.1/standard/ckeditor.js"></script>
<script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery-1.12.4.js"></script>
<script language="javascript" type="text/javascript" src="https://helmarbrewing.com/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://helmarbrewing.com/js/jquery.dataTables.min.css">
'; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');



print'
	<div class="artwork">
		<h4>Marketplace</h4>
		<h1>Helmar Marketplace</h1>
		<p>Welcome to the Helmar Brewing Marketplace. The Marketplace is where you can reach out to other users to buy, sell, and trade Helmar Brewing cards!</p>
';

/* setup code if 1) user logged in with no subscription, 2) user logged in with subscription, 3) user not logged in */

if(isset($user)){
    if( $user->login() === 1 || $user->login() === 2 ){
		/* do this code if user is logged in */


		// buying
		print '
		<div class="auctions">
            <h2>Marketplace Items Wanted</h2>
		    <p>The following users are interested in the items listed below. Click on the items if you would like to trade or reach out to that user!</p>
				';



		// get unique users, want the user who has the farthest end date (newest card listed)
		$R_cards = $db_main->query("
		    SELECT marketWishlist.userid, users.*
		    from marketWishlist
		    LEFT JOIN users ON users.userid = marketWishlist.userid
		    where expired = 'N' and $user->id <> marketWishlist.userid
		    GROUP BY marketWishlist.userid
			ORDER BY max(endDate) DESC
		");


		if($R_cards !== FALSE){

    		// grab card info --- need to left join on the card list table on series and card num, sory by series, card num
    		$R_cards2 = $db_main->query("
    		    SELECT marketWishlist.*, cardList.*
    		    FROM marketWishlist
    		    LEFT JOIN cardList ON marketWishlist.series = cardList.series and marketWishlist.cardnum = cardList.cardnum
				WHERE marketWishlist.expired = 'N' and $user->id <> marketWishlist.userid
				LIMIT 8
    		");

			print '

			<ul id="auction_list">';
			
			$R_cards->data_seek(0);
			while($card = $R_cards->fetch_object()){

				$R_cards2->data_seek(0);
				while($card2 = $R_cards2->fetch_object()){
					if($card->state == ""){
						if($greetings=$card->firstname == ""){
							$greetings='User';
						}else{
							$greetings=$card->firstname;
						}
						
					}else{
						if($greetings=$card->firstname == ""){
							$greetings='User from '.$card->state;
						}else{
							$greetings=$card->firstname.' from '.$card->state;
						}
						
					}

					if($card->userid === $card2->userid){

                        // define the pictures
						$frontpic = '/images/cardPics/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$frontthumb = '/images/cardPics/thumb/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$backpic  = '/images/cardPics/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';
						$backthumb  = '/images/cardPics/thumb/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';
						$frontlarge = $protocol.$site.'/images/cardPics/large/'.$card2->series.'_'.$card2->cardnum.'_Front.jpg';
						$backlarge  = '/images/cardPics/large/'.$card2->series.'_'.$card2->cardnum.'_Back.jpg';

		
		
		
						print'
							<li>
								<a style="background:url(\''.$frontlarge.'\'); background-size: cover; background-position: center center;background-repeat: repeat;" href="'.$frontlarge.'" data-lightbox="'.$card2->series.'_'.$card2->cardnum.'" >
									<span>
										<figure style="background:url(\''.$frontlarge.'\'); background-size: contain;background-position: center center;background-repeat: no-repeat;"></figure>
									</span>
								</a>
								<p class="nameplate item-wanted" data-send-to-user-id="'.$card->userid.'">
									<i class="fa fa-envelope-o"></i> '.$greetings.'<br>
								</p>
								<p class="nameplate card-info" data-send-to-user-id="'.$card->userid.'">
									<i class="fa fa-info"></i> Click for Card Info<br>
								</p>
							</li>
						';

		//				'.$card2->series.', 
		//				'.$card2->cardnum.'
		//				<br>
		//				'.$card2->player.'<br>
		//				'.$card2->team.'<br>



						// print the back pic if exists
						if(file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge)){
							print'
								<a href="'.$protocol.$site.'/'.$backlarge.'" data-lightbox="'.$card2->series.'_'.$card2->cardnum.'" >
									<img src="'.$protocol.$site.$backthumb.'" style="display:none">
								</a>
							';
						}
					

//  class="item-wanted" data-send-to-user-id="'.$card->userid.'"

//style=”display:none”







			//			print'
		//					<tr>
		//						<td class="item-wanted" data-send-to-user-id="'.$card->userid.'"><i class="fa fa-envelope-o"></i></td>
		//					    <td class="item-wanted" data-send-to-user-id="'.$card->userid.'">'.$greetings.'</td>
		//					    <td class="item-wanted" data-send-to-user-id="'.$card->userid.'">'.$card2->series.'</td>
		//					    <td class="item-wanted" data-send-to-user-id="'.$card->userid.'">'.$card2->cardnum.'</td>
		//						<td class="item-wanted" data-send-to-user-id="'.$card->userid.'">'.$card2->player.'</td>
		//						<td class="item-wanted" data-send-to-user-id="'.$card->userid.'">'.$card2->description.'</td>
		//						<td class="item-wanted" data-send-to-user-id="'.$card->userid.'">'.$card2->team.'</td>
		//						<td>
         //               ';


//						//check if either pic exists
//						if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) || file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){
//
//							// print the front pic if exists
//		//					if(file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge)){
//								print'
//															<a href="'.$protocol.$site.'/'.$frontlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="'.$protocol.$site.$frontthumb.'"></a>
//								';
//							}
//
//							// insert space
//							if( file_exists($_SERVER['DOCUMENT_ROOT'].$frontlarge) && file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge) ){
//								print'&nbsp;&nbsp;';
//							}
//
//							// print the back pic if exists
//							if(file_exists($_SERVER['DOCUMENT_ROOT'].$backlarge)){
//								print'
//															<a href="'.$protocol.$site.'/'.$backlarge.'" data-lightbox="'.$card->series.'_'.$card->cardnum.'" ><img src="'.$protocol.$site.$backthumb.'"></a>
//								';
//
//							}
//
//						// neither pic exists print message instead
//						}else{
//							print'<i>no picture</i>';
//						}

					}
				}
			}

			print '
			</ul></div>';

			$R_cards->free();
			$R_cards2->free();

		}else{
			print'
				could not get list of cards
			';
		}


		/* END code if user is logged in, but not paid subscription */
    }else{
		/* do this if user is not logged in */
		print 'You must be logged in to use the Marketplace. You can log in or sign up for a free account today!';
	}
}else{
	/* do this if user is not logged in */
	print 'You must be logged in to use the Marketplace. You can log in or sign up for a free account today!';
}

print'</div>';
?>



<div class="modal-holder" id="user-to-user">
    <div class="modal-wrap">
        <div class="modal">
            <h1>Helmar Brewing Marketplace Messaging</h1>
            <fieldset>
                <label for="name">From Name</label>
                <input id="name" type="text">
                <label>From Email</label>
                <input id="email" type="text" disabled>
                <p><a href="/account/">Need to update your email?</a> <a href="/account/">Account Settings</a></p>
                <label>To</label>
                <input id="to" type="text" disabled>
                <label for="subject">Subject</label>
                <input id="subject" type="text">
            </fieldset>
            <p id="error_no_message_body" class="inline_error">You must enter a message.</p>
            <textarea id="message_body"></textarea>
            <div class="disclaimer">
                <input id="disclaimer" type="checkbox">
                <p>You understand that you are contacting another member via the Helmar Brewing Marketplace because you have an interest in a card listed on the Marketplace. You will be respectful for other members and not send obscene or explicit communication, else your account may be terminated. After the communication is sent via the Marketplace, the receiving user may or may not respond to you. We have policies in place to disallow spamming of communication. The receiving party will have your email listed in your helmarbrewing.com account. If they choose to respond, you are free to communicate with the other party as you wish. This will take place outside of the helmarbrewing.com website and you will not hold Helmar Brewing responsible for any issues that may occur outside of the helmarbrewing.com website.</p>
                <p>Helmar Brewing recommends safe trading practices.</p>
                <p>In order to send your message, you must agree to the above terms.</p>
            </div>
            <div class="buttons">
                <button id="send" disabled>Send</button>
                <button id="cancel">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal-holder" id="market-card-info">
    <div class="modal-wrap">
        <div class="modal">
            <h1>Card Information</h1>
            <fieldset>
                <label id="name">Player</label>
                <input id="name" type="text">
                <label>Stance/Position</label>
                <input id="email" type="text" disabled>

                <label>Team</label>
				<input id="to" type="text" disabled>
				<label>Last Sold Date</label>
				<input id="to" type="text" disabled>
				<label>Max eBay Sell Price</label>
                <input id="to" type="text" disabled>

            </fieldset>
            <div class="buttons">
                <button id="exit">Close Info</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready( function(){
        $('#disclaimer').on('change', function(){
            userToUserAcceptDisclaimer();
        });
        $('#cancel').on('click', function(){
            userToUserCancel();
		});
		$('#exit').on('click', function(){
            exitCardInfo();
        });
    });
    $(document).ready( function(){
        $('.item-for-sale').on('click', function(){
            var send_to_user_id = this.getAttribute('data-send-to-user-id');
            userToUser(send_to_user_id);
        });
    });
    $(document).ready( function(){
        $('.item-wanted').on('click', function(){
            var send_to_user_id = this.getAttribute('data-send-to-user-id');
            userToUser(send_to_user_id, true);
        });
	});
	$(document).ready( function(){
        $('.card-info').on('click', function(){
            var send_to_user_id = this.getAttribute('data-send-to-user-id');
            getCardInfo(send_to_user_id, true);
        });
    });
</script>
<script>
    CKEDITOR.replace( 'message_body', {
        toolbar: [
            ['Bold', 'Italic']
        ]
    });
</script>



<?php
/* FOOTER */ require('layout/footer1.php');
$db_auth->close();
$db_main->close();
?>