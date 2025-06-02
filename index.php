
<br/><br/>
<a href="create_fax.php" target="_blank">Create a fax to send</a>
<br/><br/><br/>

<?php
require_once('includes/ringcentral-php-functions.inc');

//show_errors();

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

	$startDate = date('Y-m-d\TH:i:s\Z', strtotime('-1 year'));
	$endDate = date('Y-m-d\TH:i:s\Z', strtotime('now'));

	$queryParams = array(
		'dateFrom' => $startDate,
		'dateTo' => $endDate,
		'availability' => 'Alive',  // Only non-deleted messages
		'direction' => 'Inbound',
		'messageType' => 'Fax',
	);

	// Fetch voicemail messages
	$response = $platform->get('/restapi/v1.0/account/~/extension/~/message-store', $queryParams);

	$faxes = $response->json()->records;

	// echo_spaces("Raw message list", $messages);
	$i = 0;
	if (!empty($faxes)) {
		foreach ($faxes as $fax) {
			if (!empty($fax->attachments)) {
				$i++;
				echo_spaces("=== Received FAX Information $i ===","", 0, false);
				echo_spaces("Fax from", $fax->from->name);
				echo_spaces("Fax #", $fax->from->phoneNumber);
				echo_spaces("Fax # of pages", $fax->faxPageCount, 1);
				foreach ($fax->attachments as $attachment) {
					if ($attachment->contentType == 'application/pdf') {
						$faxUri = $attachment->uri;
						// Display a viewable link
						echo "<a href='display_fax.php?uri=" . urlencode($faxUri) . "' target='_blank'>View fax in PDF format</a><br/>";
					}
				}
			}
		}
	} else {
		$startDate = date('F j, Y g:i a', strtotime($startDate));
		$endDate   = date('F j, Y g:i a', strtotime($endDate));
		echo_spaces("No Faxes found for provided date range from: $startDate to: $endDate", "", 0, false);
	}
} catch (Exception $e) {
	echo 'Error: ' . $e->getMessage();
}