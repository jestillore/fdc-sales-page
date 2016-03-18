<?php

require_once('./loader.php');

global $client;

$statuses = [
	'processing' => 'Processing ',
	'pending' => 'Pending Payment',
	// 'on-hold' => 'On Hold ',
	'completed' => 'Completed ',
	'cancelled' => 'Cancelled ',
	// 'refunded' => 'Refunded ',
	// 'failed' => 'Failed '
];


// page
$current = isset($_GET['page']) ? $_GET['page'] : 1;


// fetch

$status = isset($_GET['status']) ? $_GET['status'] : '';

$params = [
	'status' => $status,
	'page' => $current,
	'filter[meta]' => 'true'
];

$response = $client->orders->get(null, $params);


$orders = $response->orders;

// pagination
$offset = 3;
$totalPages = $response->header['X-WC-TotalPages'];

$pages = [$current];
for ($i = 1; $i <= $offset; $i++) {
	$left = $current - $i;
	if ($left > 0)
		$pages[] = $left;

	$right = $current + $i;
	if ($right <= $totalPages)
		$pages[] = $right;
}

sort($pages);


// helpers

function getStatus($order) {
	global $statuses;
	return $statuses[$order->status];
}

function getOrder($order) {
	return "<a href='order.php?id=$order->id'>#$order->id</a>";
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

function getDateOrdered($order) {
	return date('d F, Y', strtotime($order->created_at));
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
			if ($s && $p[0] != 'page')
				$q[] = $s;
		}

		if (count($q) > 0)
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
		      		<li class="<?=($status == '' ? 'active' : '')?>"><a href="orders.php">All</a></li>
		      		<?php foreach($statuses as $key => $value): ?>
		        	<li class="<?=($key == $status ? 'active' : '')?>"><a href="orders.php?status=<?=$key?>"><?=$value?></a></li>
		       		<?php endforeach;?>
		       		<li class=""><a href="holidays.php">Holidays</a></li>
		      	</ul>
		    </div><!-- /.navbar-collapse -->
	  	</div><!-- /.container-fluid -->
	</nav>
	<div class="container">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Order No.</th>
					<th>Status</th>
					<th>Sender</th>
					<th>Recipient</th>
					<th>Items</th>
					<th>Date Ordered</th>
					<th>Delivery Date</th>
					<th>Total Amount</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($orders as $order): ?>
					<tr>
						<td><?=getOrder($order)?></td>
						<td><?=getStatus($order)?></td>
						<td><?=$order->billing_address->first_name?> <?=$order->billing_address->last_name?></td>
						<td><?=$order->shipping_address->first_name?> <?=$order->shipping_address->last_name?></td>
						<td>
							<ul class="list-unstyled">
								<?php foreach($order->line_items as $item): ?>
									<li><?=$item->quantity?>x <?=$item->name?></li>
								<?php endforeach;?>
							</ul>
						</td>
						<td><?=getDateOrdered($order)?></td>
						<td><?=getDeliveryDate($order)?></td>
						<td><?=getTotal($order)?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<nav style="text-align: center;">
		  	<ul class="pagination">

		  	<?php if($current == 1): ?>
		  		<li class="disabled">
			    	<a aria-label="Previous">
			    		<span aria-hidden="true">&laquo;</span>
			    	</a>
			    </li>
		  	<?php else: ?>
		  		<li>
			    	<a href="<?=getPaginationLink($current - 1)?>" aria-label="Previous">
			    		<span aria-hidden="true">&laquo;</span>
			    	</a>
			    </li>
		  	<?php endif;?>
		    
		    <?php foreach($pages as $page): ?>
		    	<?php if ($current == $page): ?>
		    		<li class="active">
			    		<a><?=$page?></a>
		    		</li>
		    	<?php else: ?>
		    		<li>
			    		<a href="<?=getPaginationLink($page)?>"><?=$page?></a>
			    	</li>
		    	<?php endif;?>
		    <?php endforeach; ?>

		    <?php if($current == $totalPages): ?>
		  		<li class="disabled">
			    	<a aria-label="Next">
			        	<span aria-hidden="true">&raquo;</span>
			      	</a>
			    </li>
		  	<?php else: ?>
		  		<li>
			    	<a href="<?=getPaginationLink($current + 1)?>" aria-label="Next">
			        	<span aria-hidden="true">&raquo;</span>
			      	</a>
			    </li>
		  	<?php endif;?>

		 	</ul>
		</nav>
	</div>
</body>
</html>
