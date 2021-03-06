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
$currentpage = 'dashboard/csv/users.csv';

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(2);

switch($user->login()){
    case 0:
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/login/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 1:
        $user->regen();
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/verify/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 2:
        $user->regen();
        break;
    default:
        $db_auth->close();
        $db_main->close();
        ob_end_flush();
        print'You created a tear in the space time continuum.';
        exit;
        break;
}




// test for user type *****


// build the stripe array - need to add handling for more than 100 customers


$i = 1;
$custs = \Stripe\Customer::all(array("limit" => 100));



do{

	foreach ($custs->data as $cu){
	    $sub_response = $cu['subscriptions']->data;

	    $list[$i]['id'] = $cu['id'];
	    if(empty($sub_response)){
	        $list[$i]['subscription'] = array(
	            'status' => 'none'
	        );
	    }else{

			$coupon_per = (isset($sub_response[0]->discount['coupon']['percent_off']) && $sub_response[0]->discount['coupon']['percent_off'] !== null) ? $sub_response[0]->discount['coupon']['percent_off'] : '';
			$coupon_amt = (isset($sub_response[0]->discount['coupon']['amount_off']) && $sub_response[0]->discount['coupon']['amount_off'] !== null) ? $sub_response[0]->discount['coupon']['amount_off'] : '' ;


	        $list[$i]['subscription'] = array(
	            'status' => $sub_response[0]['status'],
	            'sub_id' => $sub_response[0]['id'],
	            'cancel_at_period_end' => $sub_response[0]['cancel_at_period_end'],
	            'current_period_end' => $sub_response[0]['current_period_end'],
				'coupon_percent' => $coupon_per,
				'coupon_amount' => $coupon_amt






	        );
	    }

	    $i++;
	}

	$starting_after = $cu['id'];
	$custs = \Stripe\Customer::all(array("limit" => 100, "starting_after" => $starting_after));

}while(!empty($custs->data));











   // set the name of the files to be downloaded
   $file = 'users.csv';

   // open a file on the server to be written
   $fp = fopen($file, 'w');

   // set the headers for the spreadsheet,
   $csvheaders = array('username','name','email','Stripe ID','ebayID','firmname','building','address','city','state','zip','subscription status','renewal date', 'coupon_percent', 'coupon_amount');

   // write the headers to the file
   fputcsv($fp, $csvheaders);




    $result =$db_main->query("SELECT * FROM users");
    if($result != FALSE){
        $result->data_seek(0);
        while($ulist = $result->fetch_object()){
            foreach($list as $l){
				unset($sub);
                if($ulist->stripeID == $l['id']){
                    $sub = $l['subscription'];
					break;
                }
            }

            if($sub['current_period_end'] > 1){
                $d = date("m/d/Y",$sub['current_period_end']);
            }else{
                $d = '';
            }

            $csvline = array(
                $ulist->username,
                $ulist->firstname.' '.$ulist->lastname,
                $ulist->email,
                $ulist->stripeID,
								$ulist->ebayID,
								$ulist->firmname,
								$ulist->unit,
                $ulist->address,
                $ulist->city,
                $ulist->state,
                $ulist->zip5.'-'.$ulist->zip4,
                $sub['status'],
                $d,
				$sub['coupon_percent'],
				$sub['coupon_amount']
            );
            fputcsv($fp, $csvline);
        }
    }




    // close the connection to file you are writign to
    fclose($fp);

    // close database connections
    $result->free();


    // send the HTTP headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    // clear the output buffer
    ob_clean();
    ob_end_flush();
    flush();

    // send the file to the browser
    readfile($file);

    // delete the file from the server
    unlink($file);



	//  /* DEBUGGING */ print'<pre style="font-family:monospace;background-color:#444;padding:1em;color:white;">';var_dump($custs);print'</pre>';


?>
