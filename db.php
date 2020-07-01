<?php

class NewDB {

    const DB_NAME = 'orders.db';
    private $_db = null;

    function __get($name){
        if($name == "db")
            return $tihs->_db;
        throw new Exception('Unknown property!');
    }

    function __construct(){
        $this->_db = new SQLite3(self::DB_NAME);
        if(filesize(self::DB_NAME) == 0){
            try{
                $sql = "CREATE TABLE orders(
								id INTEGER PRIMARY KEY AUTOINCREMENT,
								purpose TEXT,
								sessionId INTEGER UNIQUE,
								sum INTEGER,
								result TEXT,
								datetime INTEGER)";
                if(!$this->_db->exec($sql))
                    throw new Exception($this->$_db->lastErrorMsg());
            } catch(Exception $e){
                //$e->getMessage();
                echo 'Всё плохо.';
            }
        }
    }

    function __destruct(){
        unset($this->_db);
    }

     function saveOrder($purpose, $sessionId, $sum){
        $now_date = new DateTime(date('Y-m-d H:i'));
        $datetime = $now_date->format('Y-m-d H:i');
        $sql = "INSERT INTO orders(purpose, sessionId, `sum`, result, datetime)
								VALUES('$purpose', '$sessionId', '$sum', 'noPaid', '$datetime')";
        return $this->_db->exec($sql);
    }


     function getOrder($sessionId){
        $sql = "SELECT id, purpose, sessionId, sum, datetime FROM orders WHERE sessionId = ". $sessionId;
        $res = $this->_db->query($sql);
        if(!$res) return false;
        return $this->db2Arr($res);
    }

    function condition($sessionId){
        $sql = 'UPDATE orders SET result = "paid" WHERE sessionId = '. $sessionId;
        return $this->_db->exec($sql);

    }



    private function db2Arr($data){
        $arr = [];
        while($row = $data->fetchArray(SQLITE3_ASSOC))
            $arr[] = $row;
        return $arr;
    }

}