<?php

namespace SilexF\Admin\Page;

use Doctrine\DBAL\Connection;

class ContentTypes {

    private $conn;

    public function __construct(connection $conn) {
        $this->conn = $conn;
    }

    public function fetchAll() {
        $schemaManager = $this->conn->getSchemaManager();
        $tables = $schemaManager->listTables();
        $content_tables = array();

        foreach ($tables as $table) {
            if (strpos($table->getName(), "content_") !== false) {
                $content_tables[] = $table->getName();
            }
        }
        
        return $content_tables;
    }

}

