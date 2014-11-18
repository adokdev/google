<?php
/**
 * BTECH
 *
 * Provides functionality to authorize in Google service
 * @category BTECH
 * @package  Google
 * @author   Chernov Aleksandr <adok@ukr.net>
 */
class Google_Auth 
{
  /**
   * The target of the initial request
   */
	const ENDPOINT = "https://www.google.com/accounts/ClientLogin";

  /**
   * Service name to authorize
   */
	const SERVICE = "xapi";

  /**
   * Request object used to communicate
   *
   * @var Google_Request
   */
  private $request;

  /**
   * Authentication token
   *
   * @var string
   */
  private $token;

  /**
   * Retrieves the default Auth instance with already setting Google_Request.
   *
   * @return Auth
   * @throws Google_Exception
   */
	public function __construct(array $config)
	{
		$this->request = new Google_Request(static::ENDPOINT);
		foreach($config as $k=>$v){
			$this->request->set_post_param($k,$v);
		}
		$this->request->set_post_param("accountType","HOSTED_OR_GOOGLE");
		$this->request->set_post_param("service",static::SERVICE);
		$result = $this->request->send("POST");
		if(!$result->is_error()){
			$body = $result->get_body();
			$this->token = $body["Auth"];
		}
		else{
			throw new Google_Exception("Auth Failed: ".implode(",", $result->errors()));
		}
	}

  /**
   * Retrieves the authentication token
   *
   * @return string
   */
	public function get_token(){
		return $this->token;
	}

}

?>