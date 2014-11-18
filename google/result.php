<?php
/**
 * BTECH
 *
 * Provides functionality to authorize in Google service
 * @category BTECH
 * @package  Google
 * @author   Chernov Aleksandr <adok@ukr.net>
 */
class Google_Result 
{
  private $code;
  /**
	 * RAW cURL response body
	 *
	 * @var string
   */

  private $raw_body;

  /**
	 * Array of the json_decode raw body
	 *
	 * @var array
   */
  private $body;

  /**
   * Associative array of response headers
   *
   * @var array
   */
  private $headers;

  /**
   * Associative array of errors
   *
   * @var array
   */
  private $errors = array();

  /**
   * @param int $code response code of the cURL request
   * @param string $body the raw body of the cURL response
   * @param string $headers raw header string from cURL response
   */
	public function __construct($code,$body,$headers)
	{
    $this->code     = $code;
    $this->headers  = $this->get_headers_from_curl_response($headers);
    $this->raw_body = $body;
    $this->body     = $body;
    $json           = json_decode($body);
    
    if (json_last_error() == JSON_ERROR_NONE) {
      $this->body = $json;
    }
    $array = explode("\n", $this->raw_body);

    if(count($array) > 1){
    	$body = array();
    	foreach($array as $string){
    		$params = explode("=", $string);
    		if(count($params) == 2){
    			 $body = array_merge($body, array($params[0]=>$params[1]));
    			 $this->body = is_array($this->body) ? array_merge($this->body,$body) :  $body;
    		}
    	}
    }
    $this->_check_error();
	}

  /**
   * Retrieve the RAW cURL response body
   * @param  string $headers header string from cURL response
   * @return array
   */
	public function get_raw(){
		return $this->raw_body;
	}

  /**
   * Retrieve the prepared response body
   * @return array
   */
	public function get_body(){
		return $this->body;
	}

  /**
   * Indicate if response has errors
   * @return boolean
   */
	public function is_error(){
		return !empty($this->errors);
	}

  /**
   * Associative array of errors
   * @return array
   */
	public function errors(){
		return $this->errors;
	}

  /**
   * Retrieve the cURL response headers from the
   * header string and convert it into an array
   * @param  string $headers header string from cURL response
   * @return array
   */
  private function get_headers_from_curl_response($headers)
  {
    $headers = explode("\r\n", $headers);
    array_shift($headers);
    
    foreach ($headers as $line) {
      if (strstr($line, ': ')) {
        list($key, $value) = explode(': ', $line);
        $result[$key] = $value;
      }
    }
    return $result;
  }


	/**
	 * Check valid response and set the errors
	 *
	 * @todo finish the functions 
	 * @return void
	 */
  private function _check_error(){
  	if(preg_match('/Error=/i',$this->raw_body)){
  		$this->errors = explode("=", $this->raw_body);
  	}
  }



}

?>