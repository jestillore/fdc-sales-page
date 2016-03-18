<?php

require_once('./loader.php');

global $client;

$id = $_POST['id'];
$status = $_POST['status'];

$order = new WC_Order($id);
$order->update_status($status);

header("Location: order.php?id=$id");
