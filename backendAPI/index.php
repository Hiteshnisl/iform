<?php
// For cross browser policy
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once(__DIR__ . '/vendor/autoload.php');

use Cloudinary\Cloudinary;

$cloudinary = new Cloudinary(
    [
        'cloud' => [
            'cloud_name' => 'dfxux4ost',
            'api_key'    => '853386181114476',
            'api_secret' => 'NvNN1_yA6_ssvKBoSxlsRXogPw4',
        ],
    ]
);

include_once 'config.php';
include_once 'IformController.php';
include_once 'Rules.php';


$iform = new IformController;
$validation = new Rules;

// Check posted data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Validate the data
	$validate = $validation->Validate($_REQUEST, $_FILES);
	
	if (!empty($validate)) {
		http_response_code(422);
		echo json_encode($validation->json_response(false, "Validation  fail", ['error' => $validate]));
		exit;
	}
	
	$img_data = $cloudinary->uploadApi()->upload(
		$_FILES["suggestion_image"]["tmp_name"]
	);

	$img_url = $img_data['url']; 
	
	// Upload media file
	$path_parts = pathinfo($_FILES["suggestion_image"]["name"]);
	$file_extension = $path_parts['extension'];
	
	// Array of request to pass to the API
	if ($img_url) {	
		$data = [
		    "name" => $_REQUEST['name'],
		    "email" => $_REQUEST['email'],
		    "address" => $_REQUEST['address'],
			"phone" => $_REQUEST['phone'],
			"delivery_date" => $_REQUEST['delivery_date'],
			"delivery_time" => $_REQUEST['delivery_time'],
			"gift_wrap" => ($_REQUEST['gift_wrap'] == '1' ? 1 : 0),
			"payment_type" => $_REQUEST['payment_type'],
		    "suggestion_image" => [
		    	"encoding" => "url",
		    	"extension" => $file_extension,
		    	"value" => $img_url,
		    ],
			"terms_and_conditions" => ($_REQUEST['terms_and_conditions'] == true ? 'yes' : 'no'),
		];

		// Save data
		$response = $iform->saveData($data);

		if ($response['status']) {
			// Get saved data
			$form_data = $iform->getData($response['id']);
			if ($form_data['status']) {
				//send response back to frontend
				http_response_code(200);
				echo json_encode($validation->json_response(true, "Data inserted successfully", ['form_data' => $form_data['data']]));
				exit;
			}
		}
	}	

	// API responce.
	http_response_code(500);
	echo json_encode($validation->json_response(false, $response['error']));
	exit;
}

// False methode call error
http_response_code(404);
echo json_encode($validation->json_response(false, 'Requested URL does not exist.'));
exit;