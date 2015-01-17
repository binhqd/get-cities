<?php
require_once (dirname(__FILE__) . "/components/CommonComponent.php");
require_once (dirname(__FILE__) . "/connector/MySQLConnector.php");

class State extends CommonComponent implements JsonSerializable
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

    public function getStates($countryID)
    {
        try {
            if (! isset($this->pdo)) {
                $this->_pdo = new MySQLConnector($this->config['db']);
            }
            
            $sql = "select * from states where countryID = :countryID";
            
            $records = $this->pdo->query($sql, array(
                ':countryID'    => $countryID
            ));
            
            $states = array();
            foreach ($records as $record) {
                $state = array(
                    'stateID'   => $record['stateID'],
                    'stateName' => $record['stateName']
                );
                
                $states[] = $state;
            }
            
            return $states;
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
}