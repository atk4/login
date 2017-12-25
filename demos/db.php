<?php
class App extends \atk4\ui\App {
    public $db;
    public $auth;

    function __construct($interface = 'front', $no_db_connect = false) {

        if (!$no_db_connect) {
            $this->db = \atk4\data\Persistence::connect('mysql://root:root@localhost/test');
        }

        parent::__construct('Auth Demo App');

        if ($interface == 'admin') {
            $this->initLayout('Admin');
        } else {
            $this->initLayout(new \atk4\login\Layout\Narrow());
        }

        $this->auth = $this->add(new \atk4\login\Login());
        $this->auth->setModel(new \atk4\login\Model\User($this->db));
    }
}
