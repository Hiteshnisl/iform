<?php  
 class IformController {
    
    private $JWToken;

    public function __construct() {
        // Generate access token when call is init.
        $this->JWToken = $this->generateToken();  
    }

    // Encode the URL
    private function base64url_encode($data) { 
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 


    // Generate access token
	public function generateToken(){
		$header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

		// Returns the JSON representation of the header
		$header = json_encode($header);

		// Encodes the $header with base64.	
		$header = base64_encode($header);
		
		$CLIENT_KEY = CLIENT_KEY;
		$AUD_VALUE = AUD_VALUE;
		$CLIENT_SECRET = CLIENT_SECRET;
		$nowtime = time();
		$exptime = $nowtime + 599;
		
		$payload = "{
			\"iss\": \"$CLIENT_KEY\",
		    \"aud\": \"$AUD_VALUE\",
		    \"exp\": $exptime,
		    \"iat\": $nowtime}";	
		$payload = $this->base64url_encode($payload);
		
		$signature = $this->base64url_encode(hash_hmac('sha256',"$header.$payload",$CLIENT_SECRET, true));
		$assertionValue = "$header.$payload.$signature";
		
		$grant_type = "urn:ietf:params:oauth:grant-type:jwt-bearer";
		$grant_type = urlencode($grant_type);
		$postField= "grant_type=".$grant_type."&assertion=".$assertionValue;	
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_URL, AUD_VALUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS,"$postField");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  "Content-Type: application/x-www-form-urlencoded",
		  "cache-control: no-cache"
		));
		$response = curl_exec($ch);
		curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);
		
		$tokenArray = json_decode($response,true);
		return $token = $tokenArray['access_token'];
	}

    // Store the form details using CURL
    public function saveData($data){
        $name = $data['name'];
        $email = $data['email'];
        $address = $data['address'];
        $phone = $data['phone'];
        $delivery_date = $data['delivery_date'];
        $delivery_time = $data['delivery_time'];
        $gift_wrap = $data['gift_wrap'];
        $payment_type = $data['payment_type'];  
        $img_encoding = $data['suggestion_image']['encoding'];  
        $img_extension = $data['suggestion_image']['extension'];          
        $img_value = $data['suggestion_image']['value'];  
        $terms_and_conditions = $data['terms_and_conditions'];

        $jsonPostFields = "[{
            \"fields\":[{
                \"element_name\": \"name\",
                \"value\": \"$name\"
            },
            {
                \"element_name\": \"email\",
                \"value\": \"$email\"
            },
            {
                \"element_name\": \"address\",
                \"value\": \"$address\"
            },
            {
                \"element_name\": \"phone\",
                \"value\": \"$phone\"
            },
            {
                \"element_name\": \"expecteddelivery_date\",
                \"value\": \"$delivery_date\"
            },
            {
                \"element_name\": \"expected_delivery_time\",
                \"value\": \"$delivery_time\"
            },
            
            {
                \"element_name\": \"gift_wrap\",
                \"value\": \"$gift_wrap\"
            },
            {
                \"element_name\": \"payment_type\",
                \"value\": \"$payment_type\"
            },
            {
                \"element_name\": \"suggestion_image\",
                \"encoding\": \"$img_encoding\",
                \"extension\": \"$img_extension\",
                \"value\": \"$img_value\"
            },
            {
                \"element_name\": \"terms_and_conditions\",
                \"value\": \"$terms_and_conditions\"
            }
            ]
        }]";
        
        $ch1 = curl_init();
        
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch1, CURLOPT_URL, RECORDURL);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_HEADER, false);

        curl_setopt($ch1, CURLOPT_POST, TRUE);

        curl_setopt($ch1, CURLOPT_POSTFIELDS, $jsonPostFields);

        curl_setopt($ch1, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "cache-control: no-cache",
            "Authorization: Bearer $this->JWToken"
        ]);

        $response = curl_exec($ch1);
       
        curl_close($ch1);
 
        $response = json_decode($response); 
       
        $response_data['status'] = false;
        $response_data['error'] = 'Something went wrong';
        if(is_array($response)){
            $value = $response[0];
            if(property_exists($value, 'id')){
                $response_data['status'] = true;
                $response_data['id'] = $value->id;
                return $response_data; 
            }
            else if(property_exists($value, 'error_message')){
                $response_data['error'] = $value->error_message;
                return $response_data; 
            }
            else{
                return $response_data;
            }
        }else{
            return $response_data;
        }		
	}

    // Getting data back from Iform
    public function getData($id) {
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch2, CURLOPT_URL, RECORDURL .'/'. $id);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HEADER, false);

        curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "cache-control: no-cache",
          "Authorization: Bearer $this->JWToken"
        ));

        $response = curl_exec($ch2);
        $response = json_decode($response);
        
        curl_close($ch2);
        
        $response_data['status'] = false;
        $response_data['error'] = 'Something went wrong';
        if($response){
            $response_data['status'] = true;
            $response_data['data'] = $response;
            return $response_data; 
        }else{
            return $response_data; 
        }
    }
 }
?>
