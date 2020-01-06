<?php
namespace atk4\login\demo;

use atk4\core\ConfigTrait;
use atk4\data\Persistence;
use atk4\data\ValidationException;
use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;
use atk4\schema\Migration;
use atk4\schema\MigratorConsole;
use atk4\ui\Console;
use atk4\ui\Exception;
use atk4\ui\Form;
use atk4\ui\FormField\DropDown;
use atk4\ui\FormField\Password;
use atk4\ui\jsExpression;
use atk4\ui\Loader;
use atk4\ui\Message;
use atk4\ui\View;
use atk4\ui\Wizard;
use atk4\ui\App;

include '../vendor/autoload.php';
//include 'db.php';

class AppWizard extends App {

    use ConfigTrait;

    public function dbConnectFromWizard() {
        $this->readConfig('config.php', 'php-inline');
        $this->dbConnect($this->config['dsn']);
    }
}

$app = new AppWizard(); // App without authentication to be able to freely import data
$app->initLayout('Centered');

/** @var Wizard $wizard */
$wizard=$app->add('Wizard');

$wizard->addStep('Setup DB Credentials', function(View $page) {

    $getFormData = function(Form $form) {
        $jsFieldValues = [];
        foreach($form->fields as $k => $f) {
            $jsFieldValues[$k] = $f->jsInput()->val();
        }
        return $jsFieldValues;
    };

    /** @var Form $form */
    $form = $page->add('Form');
    /** @var Loader $loader */
    $loader = $page->add(['Loader', 'loadEvent' => 'false']);
    $form->addField('type', [
        DropDown::class,
        'values' => [
            'sqlite' => 'SQLite',
            'mysql' => 'MySQL',
            'pgsql' => 'PostgresSQL',
        ],
        'width' => 'four'
    ])->on('change', $loader->jsLoad($getFormData($form)));

    $line = $form->addGroup();
    $line->addField('host', ['width' => 'six'])->on('keyup', $loader->jsLoad($getFormData($form)));
    $line->addField('port', ['width' => 'two'])->on('keyup', $loader->jsLoad($getFormData($form)));
    $line->addField('name', ['width' => 'four'])->on('keyup', $loader->jsLoad($getFormData($form)));

    $line = $form->addGroup('DB Credentials');
    $line->addField('user', ['width' => 'six'])->on('keyup', $loader->jsLoad($getFormData($form)));
    $line->addField('pass', ['width' => 'six'])->on('keyup', $loader->jsLoad($getFormData($form)));

    $form->model->set('type', 'mysql');
    $form->model->set('host', 'localhost');
    $form->model->set('port', 3306);

    $form->model->set('name', 'atk4_login');

    $form->model->set('user', 'root');
    $form->model->set('pass', 'root');

    $form->onSubmit(function($f) use ($page) {

        try {

            $dsn = $f->model->get('type') . '://';
            $dsn.= $f->model->get('user');
            $dsn.= ':';
            $dsn.= $f->model->get('pass');
            $dsn.= '@';
            $dsn.= '' . $f->model->get('host').':'.$f->model->get('port');
            $dsn.= '/';
            $dsn.= $f->model->get('name');

            Persistence::connect($dsn);
            $string_config = <<<EOD
<?php

return [
    'dsn'=>'{$dsn}'
];
EOD;
            file_put_contents('config.php', $string_config);

        } catch(\Throwable $e) {
            return new Message('Error on connection : ' . $e->getMessage(), 'negative');
        }

        return $page->jsNext();
    });

    $loader->set(function(Loader $loader) {

        $dsn = $loader->app->stickyGet('type') . ':';
        $dsn.= $loader->app->stickyGet('user');
        $dsn.= ':';
        $dsn.= $loader->app->stickyGet('pass');
        $dsn.= '@';
        $dsn.= $loader->app->stickyGet('host').':'.$loader->app->stickyGet('port');
        $dsn.= '/';
        $dsn.= $loader->app->stickyGet('name');

        $loader->add('View')->set('DSN : ' . $dsn);
    });
});

$wizard->addStep('Quickly checking if database is OK', function(View $page) {

    $console = $page->add(Console::class);

    /*
    $button = $page->add(['Button', '<< Back', 'huge wide blue'])
        ->addStyle('display', 'none')
        ->link(['index']);
    */
    $page->app->dbConnectFromWizard();

    //@todo migrateModels Is broken and need a fix
    //$console->migrateModels([User::class, Role::class, AccessRule::class]);

    //@todo imported code from migratedModels function - START
    $console->app->db = $page->app->db;

    $console->set(function($console) {

        $console->notice('Preparing to migrate models');

        $models = [User::class, Role::class, AccessRule::class];

        foreach ($models as $model) {
            $model = new $model($console->app->db);
            $m = Migration::getMigration($model);
            $result = $m->migrate();

            $console->debug('  '.get_class($m).': '.$model->table.' - '.$result);
        }

        $console->notice('Done with migration');
    });

    //@todo imported code from migratedModels function - END
});

$wizard->addStep('Populate Sample Data', function(View $page) {

    $page->app->dbConnectFromWizard();

    $page->add('Console')->set(function(Console $c) {

        $c->notice('Populating data...');

        (new AccessRule($c->app->db))
            ->each('delete');
        (new Role($c->app->db))
            ->each('delete')
            ->import(['User Role', 'Admin Role']);
        (new User($c->app->db))
            ->each('delete')
            ->import([
                ['name'=>'Standard User', 'email'=>'user', 'role'=>'User Role', 'password'=>'user'],
                ['name'=>'Administrator', 'email'=>'admin', 'role'=>'Admin Role', 'password'=>'admin'],
            ]);
        (new AccessRule($c->app->db))
            ->import([
                ['role'=>'Admin Role', 'model'=>'\\atk4\login\\Model\\User', 'all_visible'=>true, 'all_editable'=>true],
                ['role'=>'User Role', 'model'=>'\\atk4\login\\Model\\Role', 'all_visible'=>true, 'all_editable'=>false, /*'editable_fields'=>['a','b']*/],
            ]);

        $c->notice('User created!');
        $c->debug('Username : user');
        $c->debug('Password : user');

        $c->notice('User created!');
        $c->debug('Username : admin');
        $c->debug('Password : admin');

        $c->notice('Data imported');
    });
});

$wizard->addFinish(function($p) {
    $p->app->redirect(['index']);
});
