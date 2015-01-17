<?php
require_once(dirname(__FILE__) . "/../components/CommonComponent.php");
/**
 * 
 * @author BinhQD
 *
 */
class ErrorRecord extends CommonComponent implements JsonSerializable {
	/**
	 * This protected field is used to list all available magic methods
	 * @var array $_f
	 */
	protected $_f = array(
		'code'	=> '',
		'message'	=> '',
		'file'	=> '',
		'line'	=> '',
		'uri'	=> '',
		'referrer'	=> '',
		'logtime'	=> '',
		'ip'	=> '',
		'user_agent'	=> '',
		'request_method'	=> ''
	);
	
	/**
	 * This protected fields is used to list all available magic methods which data will be serialized
	 * @var array $_serialized_f
	 */
	protected $_serialized_f = array(
		'post_data'	=> '',
		'browser'	=> '',
		'traces'	=> ''
	);
	
	/**
	 * This method is used to set data to an existing object
	 * @param array $data
	 */
	public function init($data) {
		$this->code			= $data['code'];
		$this->message		= $data['message'];
		$this->file			= $data['file'];
		$this->line			= $data['line'];
		$this->uri			= $data['uri'];
		$this->referrer		= $data['referrer'];
		$this->logtime		= $data['logtime'];
		$this->ip			= $data['ip'];
		$this->user_agent	= $data['user_agent'];
		$this->request_method = $data['request_method'];
		$this->post_data	= unserialize($data['post_data']);
		$this->browser		= unserialize($data['browser']);
		$this->traces		= unserialize($data['traces']);
	}
	
	/**
	 * This is a method of JsonSerializable interface, that help json_encode understand how to serialize an object to json string
	 * @return multitype:string array mixed
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
	
	/**
	 * This method is used to get data of current object as an array
	 * @return multitype:string array mixed
	 */
	public function toArray() {
		return array(
			'code'				=> $this->code,
			'message'			=> $this->message,
			'file'				=> $this->file,
			'line'				=> $this->line,
			'uri'				=> $this->uri,
			'referrer'			=> $this->referrer,
			'logtime'			=> date(DATE_ISO8601, strtotime($this->logtime)),
			'ip'				=> $this->ip,
			'user_agent'		=> $this->user_agent,
			'request_method'	=> $this->request_method,
			'post_data'			=> $this->post_data,
			'browser'			=> $this->browser,
			'traces'			=> $this->traces,
		);
	}
}