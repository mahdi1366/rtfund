<?php


//  this function is to get proper authority key from Parsian
function gotoParsian () {

  include("nusoap/nusoap.php");

  $soapclient = new soapclient('https://www.pec24.com/pecpaymentgateway/eshopservice.asmx?wsdl','wsdl');
  if (!$err = $soapclient->getError())
   $soapProxy = $soapclient->getProxy() ;

  if ( (!$soapclient) OR ($err = $soapclient->getError()) ) {
    $error .= $err . "<br />" ;
	echo $error ;
  } else {
    $amount = intval($_POST['Amount']) ;  // here is the posted amount
	$orderId = getResNum( .... ) ; // this function is internal which will get order id
	$authority = 0 ;  // default authority
	$status = 1 ;	// default status
    $callbackUrl = "payment/paid_parsian/" ; // site call back Url

    $params = array(
	 			'pin' => ... ,  // this is our PIN NUMBER
                'amount' => $amount,
                'orderId' => $orderId,
				'callbackUrl' => $callbackUrl,
				'authority' => $authority,
				'status' => $status
              );
	$sendParams = array($params) ;
    $res = $soapclient->call('PinPaymentRequest', $sendParams);

	$authority = $res['authority'];
	$status = $res['status'];

    if ( ($authority) and ($status==0) )  {
	   // this is a succcessfull connection
	   ...
	   ...
	   ...

	   $parsURL = "https://www.pec24.com/pecpaymentgateway/?au=" . $authority ;
       redirectToURL ($parsURL) ;

	   exit() ;
	   die() ;
	   return;

    } else {
	   // this is unsucccessfull connection
	  echo "<p dir=LTR>";
      if ($err=$soapclient->getError()) {
	   echo "ERROR = $err <br /> " ;
	  }
	  echo "$authority <br />" ;
	  echo "$status <br />" ;
	  echo "$orderId <br />" ;
	  echo "Couldn't get proper authority key from Parsian" ;
	  echo "</p>";

    }

  }
  .... // SHOW HTML PART

}

//  this function is to Validate Payment
function check_Payment_Parsian () {

  include("nusoap/nusoap.php");

  
  $authority = $_REQUEST['au'];
  $status = $_REQUEST['rs'];

  if ($authority) {
    // here we update our database
	...
  }

  if ( ($status==0) and (checkDataBase(...)) ) {
    $soapclient = new soapclient('https://www.pec24.com/pecpaymentgateway/eshopservice.asmx?wsdl','wsdl');

	if ( (!$soapclient) OR ($err = $soapclient->getError()) ) {
	   // this is unsucccessfull connection
      echo  $err . "<br />" ;

    } else {
	  $status = 1 ;   // default status
      $params = array(
	            'pin' => ... ,  // this is our PIN NUMBER
	 			'authority' => $authority,
                'status' => $status ) ; // to see if we can change it
	  $sendParams = array($params) ;
      $res = $soapclient->call('PinPaymentEnquiry', $sendParams);
	  $status = $res['status'];

	  if ($status==0) {
	   // this is a succcessfull payment
	   // we update our DataBase

	  } else {

	   // this is a UNsucccessfull payment
	   // we update our DataBase

	    echo  "Couldn't Validate Payment with Parsian "  ;

	  }

	}


  } else {
	   // this is a UNsucccessfull payment

  }

  .... // SHOW HTML PART

}


?>