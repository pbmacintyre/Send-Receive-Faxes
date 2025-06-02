<?php

require('includes/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/includes')->load();

$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];
$jwt_key = $_ENV['RC_JWT_KEY'];

$server = 'https://platform.ringcentral.com';

try {
	// Initialize the RingCentral SDK
	$rcsdk = new RingCentral\SDK\SDK($client_id, $client_secret, $server);
	$platform = $rcsdk->platform();
	// Authenticate using JWT
	$platform->login(['jwt' => $jwt_key]);

	$uri = $_GET['uri']; // Make sure to validate this in production

	$response = $platform->get($uri);

	header('Content-Type: application/pdf');
	header('Content-Disposition: inline; filename="fax.pdf"');
	echo $response->raw();

} catch (Exception $e) {
	echo 'Error: ' . $e->getMessage();
}


