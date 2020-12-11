<?php

 class Database{
	private static $host='localhost';
	private static $user='root';
	private static $pass='p0rtali70s';
	private static $db='erpgruposii_DES';
	private $conn;
	protected $rows;

    public function __construct() {
    }

    private function openConexion(){
		$this->conn=new mysqli(self::$host,self::$user,self::$pass, self::$db);
    }

    private function closeConexion(){
    	$this->conn->close();
    }

    public function executeQuery($query){
//     	echo "query: ".$query;
    	$this->openConexion();
    	$result=$this->conn->query($query);
    	$this->closeConexion();
    	return mysqli_fetch_array($result);
    }

    public function getConnection(){
    	return mysqli_connect(self::$host,self::$user,self::$pass, self::$db, '3306');;
    }
}

?>