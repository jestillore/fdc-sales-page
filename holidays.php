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


// enable lockout date
if (get_option( 'orddd_lite_lockout_date_after_orders' ) > 1) {
	// yeah .. enabled already
}
else {
	// enable it
	update_option('orddd_lite_lockout_date_after_orders', 1);
}

$lockout_days_arr = array();
$lockout_days = get_option( 'orddd_lite_lockout_days' );
if ( $lockout_days != '' && $lockout_days != '{}' && $lockout_days != '[]' ) {
    $lockout_days_arr = json_decode( get_option( 'orddd_lite_lockout_days' ) );
}

$lockout_dates = [];

foreach ( $lockout_days_arr as $k => $v ) {
    if ( $v->o >= get_option( 'orddd_lite_lockout_date_after_orders' ) ) {
    	$lockout_dates[] = $v->d;
    }
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
		        	<li><a href="orders.php?status=<?=$key?>"><?=$value?></a></li>
		       		<?php endforeach;?>
		       		<li class="active"><a href="">Holidays</a></li>
		      	</ul>
		    </div><!-- /.navbar-collapse -->
	  	</div><!-- /.container-fluid -->
	</nav>
	<div class="container">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th></th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($lockout_dates as $date): ?>
					<tr>
						<td>
							<a href="delete-holiday.php?date=<?=$date?>">
								Delete
							</a>
						</td>
						<td>
							<?php
								$myDateTime = DateTime::createFromFormat('m-d-Y', $date);
								$newDateString = $myDateTime->format('m/d/Y');
								echo date('F d, Y', strtotime($newDateString));
							?>
						</td>
					</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<br />
		<form action="new-holiday.php" action="POST">
			<h3>Add New Holiday</h3>
		  	<fieldset class="form-group">
			    <label for="holiday">Holiday</label>
			    <input name="date" type="date" class="form-control" id="holiday" placeholder="Holiday">
		  	</fieldset>
		  	<input type="submit" class="btn btn-primary" />
		</form>
	</div>
</body>
</html>
