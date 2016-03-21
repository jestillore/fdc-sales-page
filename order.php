<?php

require_once('./loader.php');

global $client;

$statuses = array(
	'processing' => 'Processing ',
	'pending' => 'Pending Payment',
	// 'on-hold' => 'On Hold ',
	'completed' => 'Completed ',
	'cancelled' => 'Cancelled ',
	// 'refunded' => 'Refunded ',
	// 'failed' => 'Failed '
);

// fetch

$status = '';

$ID = $_GET['id'];

$params = array(
	'filter[meta]' => 'true'
	);

$response = $client->orders->get($ID, $params);

$order = $response->order;

// $order = new WC_Order($ID);
// $order->update_status('completed');

// $order = $client->orders->update($ID, [
// 	'status' => 'processing'
// 	]);

// header('Content-Type: application/json');
// echo json_encode($order);
// die;

$images = array();

foreach ($order->line_items as $item) {
	$product = $client->products->get($item->product_id);
	foreach ($product->product->images as $image) {
		$images[] = $image;
	}
}


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

function getAddress($addr) {
	$address = $addr->address_1;
	
	if ($addr->address_2)
		$address .= ', ' . $addr->address_2;

	$address .= ', ' . $addr->city;	
	return $address;
}

function getShippingLines($order) {
	$lines = array();
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

function getMessage($order) {
	$om = (array) $order->order_meta;
	return $om['Order Message'];
}

function getPaginationLink($page) {
	$qs = '?';
	if (isset($_SERVER['QUERY_STRING'])) {
		
		// remove page parameter
		$str = explode('&', $_SERVER['QUERY_STRING']);
		$q = array();
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
		      		<li><a href="orders.php">All</a></li>
		      		<?php foreach($statuses as $key => $value): ?>
		        	<li class="<?=($key == $status ? 'active' : '')?>"><a href="orders.php?status=<?=$key?>"><?=$value?></a></li>
		       		<?php endforeach;?>
		      	</ul>
		    </div><!-- /.navbar-collapse -->
	  	</div><!-- /.container-fluid -->
	</nav>
	<div class="container">
		<div class="row">
			<div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2">
				<div class="row">
					<?php foreach($images as $image): ?>
						<div class="col-lg-4 col-md-4">
							<img width="100%" src="<?=$image->src?>" title="<?=$image->title?>" />
						</div>
					<?php endforeach;?>
				</div>
				<br />
				<div class="row">
					<form action="update-order.php" method="POST">
						<input type="hidden" name="id" value="<?=$ID?>" />
						<table class="table table-bordered">
							<tr>
								<td>Order Number: </td>
								<td><?=$order->order_number?></td>
							</tr>
							<tr>
								<td>Products: </td>
								<td>
									<ul class="list-unstyled">
										<?php foreach($order->line_items as $item): ?>
											<li><?=$item->quantity?>x <?=$item->name?></li>
										<?php endforeach;?>
									</ul>
								</td>
							</tr>
							<tr>
								<td>Amount: </td>
								<td><?=$order->currency?> <?=$order->total?></td>
							</tr>
							<tr>
								<td>Recipient's Name: </td>
								<td><?=$order->shipping_address->first_name?> <?=$order->shipping_address->last_name?></td>
							</tr>
							<tr>
								<td>Address Details: </td>
								<td><?=getAddress($order->shipping_address)?></td>
							</tr>
							<tr>
								<td>Delivery Date: </td>
								<td><?=getDeliveryDate($order)?></td>
							</tr>
							<tr>
								<td>Message: </td>
								<td><?=getMessage($order)?></td>
							</tr>
							<tr>
								<td>Delivery Instructions</td>
								<td><?=$order->note?></td>
							</tr>
							<tr>
								<td>Sender's Name: </td>
								<td><?=$order->billing_address->first_name?> <?=$order->billing_address->last_name?></td>
							</tr>
							<tr>
								<td>Address Details: </td>
								<td><?=getAddress($order->billing_address)?></td>
							</tr>
							<tr>
								<td>Sender Email and Contact Number: </td>
								<td>
									<ul class="list-unstyled">
										<li><?=$order->billing_address->email?></li>
										<li><?=$order->customer->customer_meta->billing_phone?></li>
										<li><?=$order->customer->customer_meta->billing_cel?></li>
									</ul>
								</td>
							</tr>
							<tr>
								<td>Recipient Contact Number: </td>
								<td>
									<ul class="list-unstyled">
										<li><?=$order->customer->customer_meta->shipping_phone?></li>
										<li><?=$order->customer->customer_meta->shipping_cel?></li>
									</ul>
								</td>
							</tr>
							<tr>
								<td>Date Ordered: </td>
								<td><?=date('d F, Y', strtotime($order->created_at))?></td>
							</tr>
							<tr>
								<td>Status: </td>
								<td>
									<select <?=($order->status === 'completed' || $order->status === 'cancelled') ? 'disabled' : ''?> class="form-control" name="status">
										<?php foreach($statuses as $key => $value): ?>
											<option <?=$key === $order->status ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
										<?php endforeach;?>
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<input type="submit" class="btn btn-primary" value="Save" />
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
