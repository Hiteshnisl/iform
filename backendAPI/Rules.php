<?php 
class Rules {

	public function Validate($request, $files) {
		if (empty($request['name']) && $this->sanitize_string($request['name'])) { return 'Please provide valid Name'; }
		if (empty($request['email']) && $this->is_email($request['email'])) { return 'Please provide valid Email.'; }
		if (empty($request['address']) && $this->sanitize_string($request['address'])) { return 'Please provide valid Address'; }
		if (empty($request['phone']) && $this->is_number($request['phone'])) { return 'Please provide valid Phone.'; }
		if (empty($request['payment_type'])) { return 'Please select suitable Payment type.'; }
		if (empty($request['terms_and_conditions'])) { return 'Please Read and Check terms and conditions to move ahead.'; }
		if (empty($this->img_type($files['suggestion_image']['name']))) { return 'Suggestion Image must be a JPEG, JPG, PNG image.'; }
		if (empty($this->img_size($files['suggestion_image']['size'], 2000000))) { return 'Image size should be below 2mb '; }
	}

	public function sanitize_string ($string) {
		return filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	}

	private function is_email($email){ 
	    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	public function is_number ($number) {
		return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
	}

	public function img_type ($filename) {
		$allowed = ['jpeg', 'png', 'jpg'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		return (in_array($ext, $allowed));
	}

	public function img_size ($file_size, $allowed_size) {
		return ($file_size < $allowed_size);
	}

	public function json_response($status = false, $message = 'Something went wrong!', $data = []) {
		return [
			'STATUS' => $status,
			'MESSAGE' => $message,
			'DATA' => $data,
		];
	}
}
 ?>
