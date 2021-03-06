<?php

namespace SilexF\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Pimple;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Symfony\Component\Validator\Constraints as Assert;

Class TableType extends AbstractType {

    public function __construct(Pimple $container, $table) {
        $this->container = $container;
        $this->table = $table;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $schemaManager = $this->container['db']->getSchemaManager();
        $details = $schemaManager->listTableDetails($this->table);
        $foreignKeys = $schemaManager->listTableForeignKeys($this->table);
        $primary_keys = $this->getPrimaryKeys();
     
        $relations = array();
        foreach ($foreignKeys as $foreignKey) {
            $foreignTable = $foreignKey->getForeignTableName();
            $foreignColums = $foreignKey->getForeignColumns();
            $localColums = $foreignKey->getLocalColumns();
            $relations[$localColums[0]] = array("table" => $foreignTable, "field" => $foreignColums); // suppports only one level
        }



        foreach ($schemaManager->listTableColumns($this->table) as $column) {

            if (!empty($relations) and in_array($column->getName(), array_keys($relations))) {
                $relation_row = $relations[$column->getName()];
                $builder->add($column->getName(), new ForeignKeyType($this->container['db'], $relation_row['table'], $relation_row['field'][0]));
                continue;
            }
            if (false === strpos($column->getName(), 'system_')) { // dont  take  Column whth system_ prefix
                switch ($column->getType()) {
                    case 'Integer':
                        $builder->add($column->getName(), 'integer', array(
                            'read_only' => ( in_array($column->getName(), $primary_keys) or $column->getAutoincrement()) ? true : false,
                            'required' => $column->getNotNull(),
                            'constraints' => ( $column->getNotNull() and !$column->getAutoincrement()) ? array(new Assert\NotNull()) : array(), 
                        ));
                        break;

                    case 'Array':
                        $builder->add($column->getName(), 'choice', array('choices' => $choices, 'required' => $column->getNotNull()));
                        break;

                    case 'Boolean':
                        $builder->add($column->getName(), 'checkbox', array('required' => false));
                        break;

                    case 'String':
                        $builder->add($column->getName(), 'text', array('required' => $column->getNotNull()));
                        break;

                    case 'Text':
                        $builder->add($column->getName(), 'textarea', array('required' => $column->getNotNull()));
                        break;
                    case 'Date':
                        $builder->add($column->getName(), 'date', array('required' => $column->getNotNull()));
                        break;
                }
            }
        }
    }

    public function getName() {
        return 'Table' . ucfirst($this->table);
    }

     public function getPrimaryKeys() {
        $schemaManager = $this->container['db']->getSchemaManager();
        $table_details = $schemaManager->listTableDetails($this->table);
        $primary_key = $table_details->getPrimaryKey();
        $primary_colums = array();
        if ($primary_key) {
            $primary_colums = $primary_key->getColumns();
       
        }
        return $primary_colums;
    }

}