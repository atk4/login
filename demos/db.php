<?php

class TestACL extends \atk4\login\ACL {

    protected $permissions = [];

    /**
     * Gather permissions of currently logged in users for faster access
     */
    function cachePermissions() {

        $this->permissions = [
            'admin' => $this->auth->user->loaded() && $this->auth->user['is_admin']
        ];
    }

    /**
     * Call $app->acl->can('admin'); for example to find out if user is allowed to admin things.
     */
    function can($feature) {

        if (!$this->permissions) {
            $this->cachePermissions();
        }

        return $this->permissions[$feature];

    }


    /**
     * Will apply per-model modifications (after it's initialized) which will take permissions 
     * into account.
     */
    function applyRestrictions(\atk4\data\Persistence $p, \atk4\data\Model $m) {

        if($m instanceof \atk4\login\Model\User && !$this->can('admin')) {
             $m->getElement('email')->read_only = true;
             $m->getElement('is_admin')->read_only = true;
             $m->getElement('is_admin')->ui['visible'] = false;
        }

        if($m instanceof \atk4\login\Model\Admins && !$this->can('admin')) {
            throw Exception();
        }
        
    }
}

class App extends \atk4\ui\App {
    public $db;
    public $auth;
    public $title = 'Auth Demo App';

    public $dsn = 'mysql://root:root@localhost/test';

    function __construct($interface = 'front', $no_db_connect = false) {

        parent::__construct(include('config.php'));

        if (!$no_db_connect) {
            $this->dbConnect($this->dsn);
            //$this->db = \atk4\data\Persistence::connect($this->dsn);
        }


        if ($interface == 'admin') {
            $this->initLayout('Admin');
            $this->layout->leftMenu->addItem(['Demo Index', 'icon'=>'gift'], ['index']);
            $this->layout->leftMenu->addItem(['Admin', 'icon'=>'users'], ['admin']);
        } elseif ($interface == 'centered') {
            $this->initLayout('Centered');
        } else {
            $this->initLayout(new \atk4\login\Layout\Narrow());
        }

        $this->authenticate();
    } 

    function authenticate()
    {
        $this->auth = $this->add(new \atk4\login\Auth(['check'=>false]));
        $m = new \atk4\login\Model\User($this->db);
        $m->addCondition('is_admin', false);

        $this->auth->setModel(
            new \atk4\login\Model\User($this->db)
        );

        $this->auth->setACL(new TestACL(), $this->db);
    }
}
