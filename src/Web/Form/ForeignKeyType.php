<?php

namespace Web\Form;

use Pimple;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\DBAL\Connection;

class ForeignKeyType extends AbstractType {

    private $conn;
    private $table;
    private $field;

    public function __construct(connection $conn, $table, $field) {
        $this->conn = $conn;
        $this->table = $table;
        $this->field = $field;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(array(
            'choices' => $this->getChoice()
        ));
    }

    public function getName() {
        return "ForeignKeyType_$this->table";
    }

    public function getParent() {
        return 'choice';
    }

    public function getChoice() {
        $colum_display_candidates = array("title", "name", "username");
        $schemaManager = $this->conn->getSchemaManager();
        $columns = $schemaManager->listTableColumns($this->table);
        $field_display = $this->field;
        foreach ($columns as $column) {
            if (in_array($column->getName(), $colum_display_candidates)) {
                $field_display = $column->getName();
                break;
            }
        }
        $results = $this->conn->fetchAll("SELECT * FROM  {$this->table}");
        $choices = array();
        foreach ($results as $row) {
            $choices[$row[$this->field]] = $row[$field_display];
        }
        return ($choices);
    }

}
