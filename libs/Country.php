<?php
require_once (dirname(__FILE__) . "/components/CommonComponent.php");
require_once (dirname(__FILE__) . "/connector/MySQLConnector.php");

class Country extends CommonComponent implements JsonSerializable
{

    private $_config;

    private $_pdo;

    /**
     * Magic method that return pdo object
     *
     * @return MySQLConnector
     */
    public function getPdo()
    {
        return $this->_pdo;
    }

    /**
     * Magic method that return configuration information
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Construct method for current class
     *
     * @param array $config            
     */
    public function __construct($config)
    {
        // require_once(dirname(__FILE__) . "/MySQLConnector.php");
        $this->_config = $config;
    }

    public function getCountries()
    {
        try {
            if (! isset($this->pdo)) {
                $this->_pdo = new MySQLConnector($this->config['db']);
            }
            
            $sql = "select * from countries";
            
            $records = $this->pdo->query($sql, array());
            
            $countries = array();
            foreach ($records as $record) {
                $country = array(
                    'countryID' => $record['countryID'],
                    'countryName' => $record['countryName'],
                    'localName' => $record['localName'],
                    'iso_code' => $record['iso_code']
                );
                
                $countries[] = $country;
            }
            
            return $countries;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * This is a method of JsonSerializable interface, that help json_encode understand how to serialize an object to json string
     *
     * @return multitype:string array mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * This method is used to get data of current object as an array
     *
     * @return multitype:string array mixed
     */
    public function toArray()
    {
        return array(
            'a' => 'b'
        );
    }

    /**
     * This method is used to save error information to database
     *
     * @throws Exception
     */
    public function save()
    {
        try {
            if (! isset($this->pdo)) {
                $this->_pdo = new MySQLConnector($this->config['db']);
            }
            
            $sql = "insert into {$this->config['db']['table_name']} (id, code, message, file, line, uri, referrer, logtime, ip, request_method, user_agent, post_data, browser, traces) 
			values (NULL, :code, :message, :file, :line, :uri, :referrer, :logtime, :ip, :request_method, :user_agent, :post_data, :browser, :traces )";
            
            $ret = $this->pdo->exec($sql, array(
                ':code' => $this->code,
                ':message' => $this->message,
                ':file' => $this->file,
                ':line' => $this->line,
                ':uri' => $this->uri,
                ':referrer' => $this->referrer,
                ':logtime' => date("Y-m-d H:i:s"),
                ':ip' => $this->ip,
                ':request_method' => $this->request_method,
                ':user_agent' => $this->user_agent,
                ':post_data' => serialize($this->post_data),
                ':browser' => serialize($this->browser),
                ':traces' => serialize($this->traces)
            ));
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}