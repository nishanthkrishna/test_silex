<?php

namespace Web\Admin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Pimple;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;


Class TableType extends AbstractType {

    public function __construct(Pimple $container, $table) {
        $this->container = $container;
        $this->table = $table;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $schemaManager = $this->container['db']->getSchemaManager();

        $details = $schemaManager->listTableDetails($this->table);

        $foreignKeys = $schemaManager->listTableForeignKeys($this->table);
   
        foreach ($foreignKeys as $foreignKey) {
            $foreignTable=$foreignKey->getForeignTableName();
            $foreignColums=$foreignKey->getForeignColumns();
            $localColums=$foreignKey->getLocalColumns();
            
             print "<pre>";
        print_r($foreignKey->getForeignTableName());
        print_r($foreignKey->getForeignColumns());
        print_r($foreignKey->getLocalColumns());
       // $foreignKey->getLocalTableName();
        print "</pre>";
        }

       

        foreach ($schemaManager->listTableColumns($this->table) as $column) {


            // here we try to display nicely database relations w/ foreign keys
            /*
              if (false !== strpos($column->getName(), '_id')) {
              $table = str_replace('_id', '', $column->getName());

              $dataMap = new DataMap($this->container['db'], $schemaManager);
              $choices = $dataMap->mapForeignKeys($table, $column);

              if (is_array($choices)) {
              $column->setType(Type::getType('array'));
              }
              }
             */

            if (false === strpos($column->getName(), 'system_')) { // dont  take  Column whth system_ prefix
                switch ($column->getType()) {
                    case 'Integer':
                        $builder->add($column->getName(), 'integer', array(
                            'read_only' => $column->getName() === 'id' ? true : false,
                            'required' => $column->getNotNull(),
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

}