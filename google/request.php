<?php
/**
 * BTECH
 *
 * Request supports basic features like sending different HTTP requests and handling
 * redirections.
 * @category BTECH
 * @package  Google
 * @author   Chernov Aleksandr <adok@ukr.net>
 */
class Google_Request 
{
  /**
   * HTTP request methods
   */
  const GET     = 'GET';
  const POST    = 'POST';
  const PUT     = 'PUT';
  const HEAD    = 'HEAD';
  const DELETE  = 'DELETE';
  const TRACE   = 'TRACE';
  const OPTIONS = 'OPTIONS';
  const CONNECT = 'CONNECT';

  /**
   * HTTP protocol versions
   */
  const HTTP = '1.1';

  /**
   * Flag value to make curl verify the peer's certificate
   */
  const SSL_VERIFYPEER = true;

  /**
   * Set maximum time the request is allowed to take
   */
  const TIMEOUT = null;

  /**
   * POST data encoding methods
   */
  const ENC_URLENCODED = 'application/x-www-form-urlencoded';
  const ENC_FORMDATA   = 'multipart/form-data';

  /**
   * Associative array of GET parameters
   *
   * @var array
   */
  private $params_Get = array();

  /**
   * Assiciative array of POST parameters
   *
   * @var array
   */
  private $params_Post = array();

  /**
   * Associative array of request headers
   *
   * @var array
   */
  protected $headers = array();

  /**
   * Request body content type (for POST requests)
   *
   * @var string
   */
  protected $enctype = null;

  /**
   * Request URI
   *
   * @var string
   */
  protected $uri;

  /**
   * Contructor method. Will create a new Google_Request. Accepts the target URL
   *
   * @param string $uri
   * @throws Google_Exception
   */
  public function __construct($uri = false){
  	if(is_array($this->validate_uri($uri))){
  		throw new Google_Exception("URL is novalid: ".implode(",", $this->validate_uri()));
  	}
  	$this->uri = $uri;
  }


 /**
   * Set a GET parameter for the request.
   *
   * @param string|array $name
   * @param string $value
   * @return Google_Request
   */
  public function set_get_param($name, $value = null)
  {
      if (is_array($name)) {
          foreach ($name as $k => $v)
              $this->_setParameter('GET', $k, $v);
      } else {
          $this->_setParameter('GET', $name, $value);
      }

      return $this;
  }

	/**
	 * Set a POST parameter for the request.
	 *
	 * @param string|array $name
	 * @param string $value
	 * @return Google_Request
	 */
  public function set_post_param($name, $value = null)
  {
      if (is_array($name)) {
          foreach ($name as $k => $v)
              $this->_set_param('POST', $k, $v);
      } else {
          $this->_set_param('POST', $name, $value);
      }

      return $this;
  }

	/**
	 * Set one or request headers
	 *
	 * @param string $name
	 * @param string $value
	 * @return Google_Request
	 */
	public function set_header($name, $value = null){
		$name = is_array($name) ? (string)current($name) : $name;
		$this->headers[strtolower($name)] = array($name, $value);
		return $this;
	}

	/**
	 * Delete one or request headers
	 *
	 * @param string $name
	 * @return Google_Request
	 */
	public function delete_header($name){
		$name = is_array($name) ? (string)current($name) : $name;
		if(isset($this->headers[strtolower($name)])){
			unset($this->headers[strtolower($name)]);
		}
		return $this;
	}

	/**
	 * Clear request headers
	 *
	 * @return Google_Request
	 */
	public function clear_headers(){
		$this->headers = array();
		return $this;
	}

  /**
   * Set the next request's method
   *
   * Validated the passed method and sets it. If we have files set for
   * POST requests, and the new method is not POST, the files are silently
   * dropped.
   *
   * @param string $method
   * @return Google_Request
   * @throws Google_Exception
   */
  public function set_method($method = self::GET)
  {
      $regex = '/^[^\x00-\x1f\x7f-\xff\(\)<>@,;:\\\\"\/\[\]\?={}\s]+$/';
      if (! preg_match($regex, $method)) {
     		throw new Google_Exception("'{$method}' is not a valid HTTP request method.");
      }

      if ($method == self::POST && $this->enctype === null){
      	$this->enctype = self::ENC_URLENCODED;
      }
          
      $this->method = $method;

      return $this;
  }

  /**
   * Send the HTTP request and return an HTTP response object
   *
   * @param string $method
   * @return Google_Response
   * @throws Google_Exception
   */
  public function send($method = self::GET){
  	$this->set_method($method);

  	$headers = array();
  	foreach($this->headers as $header){
  		$headers[] = self::get_header(current($header),end($header));
  	}
    
    if (!array_key_exists("user-agent", $headers)) {
        $headers[] = "user-agent: google_request-php/1.1";
    }
    if (!array_key_exists("expect", $headers)) {
        $headers[] = "expect:";
    }
    
    $ch = curl_init();
    if ($this->method != static::GET) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params_Post);
    } 
    $this->uri .= (strpos($this->uri, '?') !== false) ? "&" : "?";
    $this->uri .= urldecode(http_build_query($this->params_Get));
    
    curl_setopt($ch, CURLOPT_URL, $this->uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, static::SSL_VERIFYPEER);
    curl_setopt($ch, CURLOPT_ENCODING, ""); // If an empty string, "", is set, a header containing all supported encoding types is sent.
    if (static::TIMEOUT != null && is_numeric(static::TIMEOUT)) {
        curl_setopt($ch, CURLOPT_TIMEOUT, static::TIMEOUT);
    }
    
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    if ($error) {
        throw new Google_Exception($error);
    }
    
    // Split the full response in its headers and body
    $curl_info   = curl_getinfo($ch);
    $header_size = $curl_info["header_size"];
    $header      = substr($response, 0, $header_size);
    $body        = substr($response, $header_size);

    return new Google_Result($curl_info["http_code"], $body, $header);
  }

  /**
   * Set a GET or POST parameter - used by set_get_param and set_post_param
   *
   * @param string $type GET or POST
   * @param string $name
   * @param string $value
   * @return null
   */
  protected function _set_param($type, $name, $value)
  {
      $parray = array();
      $type = strtolower($type);
      switch ($type) {
          case 'get':
              $parray = &$this->params_Get;
              break;
          case 'post':
              $parray = &$this->params_Post;
              break;
      }

      if ($value === null) {
          if (isset($parray[$name])) unset($parray[$name]);
      } else {
          $parray[$name] = $value;
      }
  }
	/**
	 * Check valid format for requested url
	 *
	 * @todo finish the functions 
	 * @return null if everything is OK array if url is not valid
	 */
  private function validate_uri($uri){
  	$errors = array();
  	return count($errors) ? $errors : null;
  }

	/**
	 * Perform a valid http header string
	 *
   * @param string $key name of http header
   * @param string $val value of http header
	 * @return string
	 */
  private static function get_header($key, $val)
  {
      $key = trim(strtolower($key));
      return $key . ": " . $val;
  }


}

?>