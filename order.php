<?php

require_once( 'lib/woocommerce-api.php' );

$options = array(
    'ssl_verify'      => false
);

try {

    $client = new WC_API_Client('http://floresdecielito.com', 'ck_489f23cf911518cddd5845bcbcbd7ece0369ac30', 'cs_897caaa939fe45b71a050dc6c2cebf1201d91e39', $options);

} catch ( WC_API_Client_Exception $e ) {

    echo $e->getMessage() . PHP_EOL;
    echo $e->getCode() . PHP_EOL;

    if ( $e instanceof WC_API_Client_HTTP_Exception ) {

        print_r( $e->get_request() );
        print_r( $e->get_response() );
    }
}

$statuses = [
	'processing' => 'Processing ',
	'pending' => 'Pending Payment',
	// 'on-hold' => 'On Hold ',
	'completed' => 'Completed ',
	'cancelled' => 'Cancelled ',
	// 'refunded' => 'Refunded ',
	// 'failed' => 'Failed '
];

// fetch

$status = '';

$ID = $_GET['id'];

$params = [	'filter[meta]' => 'true'
];

$response = $client->orders->get($ID, $params);

header('Content-Type: application/json');
echo json_encode($response);

die;


// helpers

function getStatus($order) {
	global $statuses;
	return $statuses[$order->status];
}

function getOrder($order) {
	return "#$order->id by " . $order->customer->first_name . ' ' . $order->customer->last_name;
}

function getItems($order) {
	$items = count($order->line_items);
	return $items . ' item' . ($items == 1 ? '' : 's');
}

function getShipTo($order) {
	return $order->shipping_address->first_name . ' ' . $order->shipping_address->last_name . ', ' . $order->shipping_address->address_1 . ', ' . $order->shipping_address->city;
}

function getShippingLines($order) {
	$lines = [];
	foreach ($order->shipping_lines as $line) {
		$lines[] = $line->method_title;
	}
	return join(', ', $lines);
}

function getDates($order) {
	return date('Y/m/d', strtotime($order->created_at));
}

function getTotal($order) {
	return "$order->currency $order->total";
}

function getPaymentMethod($order) {
	return $order->payment_details->method_title;
}

function getDeliveryDate($order) {
	$om = (array) $order->order_meta;
	return $om['Delivery Date'];
}

function getPaginationLink($page) {
	$qs = '?';
	if (isset($_SERVER['QUERY_STRING'])) {
		
		// remove page parameter
		$str = explode('&', $_SERVER['QUERY_STRING']);
		$q = [];
		foreach ($str as $s) {
			$p = explode('=', $s);
			if ($p[0] != 'page')
				$q[] = $s;
		}

		$qs .= join('&', $q) . '&';
	}

	$qs .= "page=$page";

	return $qs;
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Sales Page</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.1.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body>

	<nav class="navbar navbar-default">
	  	<div class="container-fluid">
		    <div class="navbar-header">
		      	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
		        	<span class="sr-only">Toggle navigation</span>
		        	<span class="icon-bar"></span>
		        	<span class="icon-bar"></span>
		        	<span class="icon-bar"></span>
		      	</button>
		      	<a class="navbar-brand" href="">Floresdecielito</a>
		    </div>

		    <!-- Collect the nav links, forms, and other content for toggling -->
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		      	<ul class="nav navbar-nav">
		      		<?php foreach($statuses as $key => $value): ?>
		        	<li class="<?=($key == $status ? 'active' : '')?>"><a href="?status=<?=$key?>"><?=$value?></a></li>
		       		<?php endforeach;?>
		      	</ul>
		    </div><!-- /.navbar-collapse -->
	  	</div><!-- /.container-fluid -->
	</nav>
	<div class="container">
		
	</div>
</body>
</html>
