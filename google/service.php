<?php
/**
 * BTECH
 *
 * Base functionality of google services
 * @category BTECH
 * @package  Google
 * @author   Chernov Aleksandr <adok@ukr.net>
 */
abstract class Google_Service
{

  /**
   * Service name of the API module
   */
	const SERVICE;
  /**
   * Headers that must be present in all requests to services
   * @var array
   */
	protected $access_headers = array(
		"Content-type"  => "application/atom+xml",
		"Authorization" => "GoogleLogin auth=your-authentication-token"
	);
	
  public function __construct(Google $google)
  {
      if (!defined('static::SERVICE'))
      {
          throw new Google_Exception('Constant SERVICE is not defined on subclass ' . get_class($this));
      }
  }
	/**
	 * Perform request url
	 *
	 * @param  string $sufix| contact to base service url
	 * @return string
	 */
	abstract private function perform_url($sufix){}
}

?>
