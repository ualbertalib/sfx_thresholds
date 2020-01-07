<?php

namespace SFX;

class Database {

	private $host = DBHOST;
	private $post = PORT;
	private $dbname = DBNAME;
	private $user = DBUSER;
	private $pass = DBPASS;
    private $stmt;
    private $dbh;
    private $error;

    function __construct(){

        try{
			
            $this->dbh = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->dbName", $this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));  
		 	$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  	
			
        }// Catch any errors
        catch(PDOException $e){
            $this->error = $e->getMessage();
        }

    }
	

    public function query($query){
        $this->stmt = $this->dbh->prepare($query);
    }

    /**
     * Binds the parameters of the query
     * @param String $param is the placeholder value that we will be using in our SQL statement, example :name.
     * @param String $value is the actual value that we want to bind to the placeholder, example “John Smith”.
     * @param null $type is the datatype of the parameter, example string.
     */
    public function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * @description The next method we will be look at is the PDOStatement::execute. The execute method executes the prepared statement.
     * @return mixed
     */
    public function execute(){
        return $this->stmt->execute();
    }

    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resultset(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rowCount(){
        return $this->stmt->rowCount();
    }

    public function lastInsertId(){
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction(){
        return $this->dbh->beginTransaction();
    }

    public function endTransaction(){
        return $this->dbh->commit();
    }

    public function cancelTransaction(){
        return $this->dbh->rollBack();
    }

    public function debugDumpParams(){
        return $this->stmt->debugDumpParams();
    }


} 