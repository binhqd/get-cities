<?php
require_once(dirname(__FILE__) . "/../components/ErrorRecord.php");
require_once(dirname(__FILE__) . "/MySQLConnector.php");

class MySQLErrorRecord extends ErrorRecord {
	private $_config;
	private $_pdo;
	
	/**
	 * Magic method that return pdo object
	 * @return MySQLConnector
	 */
	public function getPdo() {
		return $this->_pdo;
	}
	
	/**
	 * Magic method that return configuration information 
	 */
	public function getConfig() {
		return $this->_config;
	}
	
	/**
	 * Construct method for current class
	 * @param array $config
	 */
	public function __construct($config) {
		require_once(dirname(__FILE__) . "/MySQLConnector.php");
		$this->_config = $config;
	}
	
	/**
	 * This method return all errors and grouped by file and line
	 * @throws Exception
	 * @return array $errors
	 */
	public function groupErrorsByFileAndLine() {
		try {
			if (!isset($this->pdo)) {
				$this->_pdo = new MySQLConnector($this->config['db']);
			}
			
			$sql = "select * from {$this->config['db']['table_name']}";
			
			$records = $this->pdo->query($sql, array());
			
			$groups = array();
			$obj = new MySQLErrorRecord($this->config);
			foreach ($records as $record) {
				$obj->init($record);
				$file = $obj->file;
				$line = $obj->line;
				
				if (!isset($groups[$file])) {
					$groups[$file] = array();
				}
				if (!isset($groups[$file][$line])) {
					$groups[$file][$line] = array(
						'count'		=> 0,
						'errors'	=> array()
					);
				}
				$groups[$file][$line]['count'] = $groups[$file][$line]['count'] + 1;
				$groups[$file][$line]['errors'][] = $obj->toArray();
				//echo json_encode($obj);exit;
			}
			
			return $groups;
		} catch (Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	/**
	 * This method is used to remove all errors log by file and line
	 * @param string $file
	 * @param int $line
	 * @throws Exception
	 */
	public function removeErrorsByFileAndLine($file, $line) {
		try {
			if (!isset($this->pdo)) {
				$this->_pdo = new MySQLConnector($this->config['db']);
			}
			
			$sql = "delete from {$this->config['db']['table_name']} where line=:line and file=:file";
			
			$ret = $this->pdo->exec($sql, array(
				':line'	=> $line,
				':file'	=> $file
			));
			
		} catch (Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	/**
	 * This method is used to list all errors by file and line, and group by uri and request method
	 * @param string $file
	 * @param int $line
	 * @throws Exception
	 * @return array $errors
	 */
	public function listErrorsByFileAndLine($file, $line) {
		try {
			if (!isset($this->pdo)) {
				$this->_pdo = new MySQLConnector($this->config['db']);
			}
			
			$sql = "select * from {$this->config['db']['table_name']} where line = :line and file = :file";
			
			$records = $this->pdo->query($sql, array(
				':line'	=> $line,
				':file'	=> $file
			));
			
			$groups = array();
			$obj = new MySQLErrorRecord($this->config);
			foreach ($records as $record) {
				$obj->init($record);
				
				$uri = $obj->uri;
				$method = $obj->request_method;
				
				if (!isset($groups[$uri])) {
					$groups[$uri] = array();
				}
				if (!isset($groups[$uri][$method])) {
					$groups[$uri][$method] = array(
						'count'		=> 0,
						'errors'	=> array()
					);
				}
				
				$groups[$uri][$method]['count'] = $groups[$uri][$method]['count'] + 1;
				$groups[$uri][$method]['errors'][] = $obj->toArray();
				//echo json_encode($obj);exit;
			}
			
			return $groups;
		} catch (Exception $ex) {
			throw new Exception($ex->getMessage());
		}
		
	}
	
	/**
	 * This method is used to remove errors by file, line, uri and method
	 * @param string $file
	 * @param int $line
	 * @param string $uri
	 * @param string $method
	 * @throws Exception
	 */
	public function removeErrorsByGroup($file, $line, $uri, $method) {
		try {
			if (!isset($this->pdo)) {
				$this->_pdo = new MySQLConnector($this->config['db']);
			}
			
			$sql = "delete from {$this->config['db']['table_name']} where line=:line AND request_method = :request_method AND file=:file AND uri = :uri";
			
			$ret = $this->pdo->exec($sql, array(
				':line'		=> $line,
				':file'		=> $file,
				':request_method'	=> strtoupper($method),
				':uri'		=> $uri
			));
			
		} catch (Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
	/**
	 * This method is used to save error information to database
	 * @throws Exception
	 */
	public function save() {
		try {
			if (!isset($this->pdo)) {
				$this->_pdo = new MySQLConnector($this->config['db']);
			}
			
			$sql = "insert into {$this->config['db']['table_name']} (id, code, message, file, line, uri, referrer, logtime, ip, request_method, user_agent, post_data, browser, traces) 
			values (NULL, :code, :message, :file, :line, :uri, :referrer, :logtime, :ip, :request_method, :user_agent, :post_data, :browser, :traces )";
			
			$ret = $this->pdo->exec($sql, array(
				':code'				=> $this->code,
				':message'			=> $this->message,
				':file'				=> $this->file,
				':line'				=> $this->line,
				':uri'				=> $this->uri,
				':referrer'			=> $this->referrer,
				':logtime'			=> date("Y-m-d H:i:s"),
				':ip'				=> $this->ip,
				':request_method'	=> $this->request_method,
				':user_agent'		=> $this->user_agent,
				':post_data'		=> serialize($this->post_data),
				':browser'			=> serialize($this->browser),
				':traces'			=> serialize($this->traces)
			));
			
		} catch (Exception $ex) {
			throw new Exception($ex->getMessage());
		}
	}
	
}