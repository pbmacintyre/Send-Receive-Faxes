<?php
require_once('includes/ringcentral-php-functions.inc');

show_errors();

require('includes/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/includes')->load();

$client_id = $_ENV['RC_APP_CLIENT_ID'];
$client_secret = $_ENV['RC_APP_CLIENT_SECRET'];
$jwt_key = $_ENV['RC_JWT_KEY'];

$server = 'https://platform.ringcentral.com';

// Initialize the RingCentral SDK
$rcsdk = new RingCentral\SDK\SDK($client_id, $client_secret, $server);
$platform = $rcsdk->platform();

// Authenticate using JWT
$platform->login(['jwt' => $jwt_key]);

// Prepare fax content
$attachments = [];
if (!empty($_POST['fax_text'])) {
	// Create a temp .txt file from user input
	$textPath = tempnam(sys_get_temp_dir(), 'fax') . '.txt';
	file_put_contents($textPath, $_POST['fax_text']);
	$attachments[] = [
		'content-type' => 'text/plain',
		'filename' => basename($textPath),
		'content' => fopen($textPath, 'r')
	];
}

if (!empty($_FILES['fax_file']['tmp_name'])) {
	$fileTmpPath = $_FILES['fax_file']['tmp_name'];
	$fileName = $_FILES['fax_file']['name'];
	$attachments[] = [
		'content-type' => 'application/pdf',
		'filename' => $fileName,
		'content' => fopen($fileTmpPath, 'r')
	];
}

if (empty($attachments)) {
	exit("Error: Please enter text or upload a file.");
}

$faxNumber = $_POST['fax_number'];

try {
	$response = $platform->post('/restapi/v1.0/account/~/extension/~/fax', [
		'to' => [['phoneNumber' => $faxNumber]],
		'faxResolution' => 'High',
		'coverPageText' => 'Fax from PHP API'
	], $attachments);

	echo "Fax sent successfully. Message ID: " . $response->json()->id;
} catch (Exception $e) {
	exit("Fax send failed: " . $e->getMessage());
}
?>
