<?php
class Helper_ECS_API {
	// https://github.com/wa-research/api/wiki/ECS-API
	// Example script for 'list-members' operation
	// $Community variable is the path to a community from site root, for example '/parent1/community'
	// The API also accepts the community in the URL: https://dgroups.org/parent1/communit/__api/v1/operation
	const KEY 		= '';
	const SECRET 	= '';

	private static $_HOST		= ''; //e.g. preventionweb.net
	private static $_TIME 		= ''; //e.g. gmdate("Y-m-d\TH:i:s")
	private static $_COMMUNITY 	= ''; //e.g. '/pwandsubsites/pwsearch'
	private static $_OPERATION 	= ''; //e.g. add-member, list-threads, create-user-profile, list-members
	private static $_HTTP_HEADER = ''; //e.g. 'HTTP/1.1 404 Not Found', 

	function __construct($p_host, $p_community, $p_operation) {
		if (empty($p_host) || empty($p_operation) || empty($p_community)) throw new Exception('Required fields missing.');
		self::$_TIME 		= gmdate("Y-m-d\TH:i:s");
		self::$_COMMUNITY 	= $p_community;
		self::$_OPERATION 	= $p_operation;
		self::$_HOST 		= $p_host;
	}

	/**
	 * This function will add new member to the community
	 * 
	 * @param array $p_data_array 
	 * @return string HTTP raw return value
	 */
	public function add_new_member($p_data_array) {
		if (empty($p_data_array) || !is_array($p_data_array)) throw new Exception('Required array fields  missing.');

		$p_data_array['scope'] = self::$_COMMUNITY;
		
		$return = $this->execute($p_data_array);
		if ($this->get_header() == 'HTTP/1.1 404 Not Found') { //email address doesn't exists in cc space
			$this->set_operation('create-user-profile');
			$return = $this->execute($p_data_array);
		}

		return $return;
	}

	/**
	 * @return string HTTP header
	 */
	public function get_header() {
		return self::$_HTTP_HEADER;
	}

	/**
	 * @param string set new value for operation
	 */
	public function set_operation($p_operation) {
		self::$_OPERATION = $p_operation;
	}

	public function execute($data_to_send = array()) {
		$url_conf = array();
		$url_conf["signature"]     	= $this->get_signature();
		$url_conf["request-time"]  	= self::$_TIME;
		$url_conf["site-id"]       	= self::KEY;

		$url = "/api.axd?verbose";
		$url_query = '&site-id=' 	. urlencode($url_conf["site-id"]) .
			'&request-time=' 		. urlencode($url_conf["request-time"]) .
			'&signature=' 			. urlencode($url_conf["signature"]);

		$url = self::$_COMMUNITY . '/__api/v1/' . self::$_OPERATION . '?verbose&random=' . rand() . $url_query;

		return $this->post(self::$_HOST, $url, $data_to_send);
	}

	private function get_signature() {
		return $this->sign(
		self::$_OPERATION,
		self::KEY,
		self::SECRET,
		self::$_TIME
		);
	}

	/**
	 * Sign the API request
	 */
	private function sign($p_operation, $p_community, $p_secret, $p_time) {
		$sigsource = sprintf('%s%s%s%s', $p_operation, $p_community, $p_secret, $p_time);
		return sha1($sigsource);
	}

	/**
	 * Send the API request to the host server
	 */
	private function post($host, $path, $data_to_send) {
		//$fp = fsockopen('ssl://'.$host, 443, $errno, $errstr);
		$fp = fsockopen($host, 80, $errno, $errstr);
		if (!$fp) {
			echo "errno: $errno \n";
			echo "errstr: $errstr\n";
			return;
		}

		$data_to_send_cleaned = http_build_query($data_to_send);
		$out  = "POST {$path} HTTP/1.1\r\n";
		$out .= "Host: {$host}\r\n";
		$out .= "User-Agent: Mozilla/5.0 (Windows NT 6.1) - UNISDR Automated Script\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-length: " . strlen($data_to_send_cleaned) . "\r\n";
		$out .= "Connection: close\r\n\r\n";
		$out .= $data_to_send_cleaned . "\r\n\r\n";
		fwrite($fp, $out); //send to server

		$return_array = array();
		$return = '';
		while (!feof($fp)) {
			$return .= fgets($fp, 1024);
		}
		if (!empty($return)) $return_array = explode("\r\n", $return); 

		if (isset($return_array[0])) {
			self::$_HTTP_HEADER = $return_array[0]; //e.g. 'HTTP/1.1 200 OK'
		}

		return $return;
	}

}













