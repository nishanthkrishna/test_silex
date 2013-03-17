<?php

namespace SilexF\Repository;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\CallbackAdapter;
use Doctrine\DBAL\Connection;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GenericRepository
 *
 * @author nish
 */
class GenericRepository {

    private $conn;
    private $table;

    public function __construct(connection $conn, $table) {
        $this->conn = $conn;
        $this->table = $table;
    }

    public function pagerFetch() {
       
        $adapter = new CallbackAdapter(array($this,'nbResultsCallback'),array($this,'getSlice'));
        $pagerfanta = new Pagerfanta($adapter);

     return $pagerfanta;
    }

    public function nbResultsCallback() {
       return $this->conn->fetchColumn("SELECT COUNT(*) FROM $this->table");
    }
    public function getSlice($offset,$length) {
        
        return  $this->conn->fetchAll("SELECT * FROM $this->table LIMIT $offset,$length");
        
        
    }

}

?>
