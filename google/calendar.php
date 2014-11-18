<?php
/**
 * BTECH
 *
 * Provides functionality to access Google calendar service
 * @category BTECH
 * @package  Google
 * @author   Chernov Aleksandr <adok@ukr.net>
 */
class Google_Calendar extends Google_Service
{

  /**
   * Service name of the API module
   */
	const SERVICE = "calendar";

  /**
   * Google object used to access auth tokens
   *
   * @var Google
   */
	private $google;

  /**
   * Retrieves the default Google_Calendar.
   *
   * @return Google
   * @throws Google_Exception
   */
	public function __construct(Google $google)
	{
		$this->google = $google;
	}

	/**
	 * Perform request url
	 *
	 * @param  string $sufix| contact to base service url
	 * @return string
	 */
	private function perform_url($sufix){
		return Google::url().static::SERVICE."/".Google::version()."/".$sufix;
	}

}

?>