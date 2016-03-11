<?php

require_once( 'lib/woocommerce-api.php' );
require_once('../wp-load.php');

$enable_login = false;

if ($enable_login) {
	function redirect_to_login_page() {
		$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		header('Location: ../wp-login.php?redirect_to=' . urlencode($actual_link) . '&reauth=1');
	}

	if (is_user_logged_in() ) {
		global $current_user;
		get_currentuserinfo();
		if (array_search('shop_manager', $current_user->roles) === FALSE) {
			wp_logout();
			redirect_to_login_page();
		}
	}
	else {
		redirect_to_login_page();
	}
}
$client = null;

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
