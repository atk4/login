<?php
include '../vendor/autoload.php';
include 'db.php';

class Admin extends App 
{
    function authenticate()
    {
        $this->auth = $this->add(new \atk4\login\Auth());
        $this->auth->setModel((new \atk4\login\Model\User($this->db))->addCondition('is_admin', true));
    }
}

$app = new Admin('admin');

$app->add(new \atk4\login\UserAdmin())
    ->setModel(new \atk4\login\Model\User($app->db));
