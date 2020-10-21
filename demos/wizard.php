<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\core\ConfigTrait;
use atk4\data\Persistence;
use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;
use atk4\schema\Migration;
use atk4\ui\App;
use atk4\ui\Console;
use atk4\ui\Form;
use atk4\ui\Form\Control\Dropdown;
use atk4\ui\Loader;
use atk4\ui\Message;
use atk4\ui\View;
use atk4\ui\Wizard;
use Throwable;

include '../vendor/autoload.php';
include 'db.php';

// App without authentication to be able to freely import data
$app = new class(['title' => 'Agile Toolkit - Wizard setup']) extends App {
    use ConfigTrait;

    public function dbConnectFromWizard()
    {
        $this->readConfig('config.php', 'php');
        $this->db = Persistence::connect($this->config['dsn']);
    }
};
$app->initLayout([\atk4\ui\Layout\Centered::class]);

$wizard = Wizard::addTo($app);

$wizard->addStep('Setup DB Credentials', function (View $page) {
    $getFormData = function (Form $form) {
        $jsFieldValues = [];
        foreach ($form->controls as $k => $f) {
            $jsFieldValues[$k] = $f->jsInput()->val();
        }

        return $jsFieldValues;
    };

    $form = Form::addTo($page);

    $loader = Loader::addTo($page, ['loadEvent' => 'false']);
    $form->addControl('type', [
        Dropdown::class,
        'values' => [
            'sqlite' => 'SQLite',
            'mysql' => 'MySQL',
            'pgsql' => 'PostgresSQL',
        ],
        'width' => 'four',
    ])->on('change', $loader->jsLoad($getFormData($form)));

    $line = $form->addGroup();
    $line->addControl('host', ['width' => 'six'])
        ->on('keyup', $loader->jsLoad($getFormData($form)));
    $line->addControl('port', ['width' => 'two'])
        ->on('keyup', $loader->jsLoad($getFormData($form)));

    $line->addControl('name', ['width' => 'four'])
        ->on('keyup', $loader->jsLoad($getFormData($form)));

    $line = $form->addGroup('DB Credentials');
    $line->addControl('user', ['width' => 'six'])
        ->on('keyup', $loader->jsLoad($getFormData($form)));
    $line->addControl('pass', ['width' => 'six'])
        ->on('keyup', $loader->jsLoad($getFormData($form)));

    $form->model->set('type', 'mysql');
    $form->model->set('host', 'localhost');
    $form->model->set('port', 3306);

    $form->model->set('name', 'atk4_login');

    $form->model->set('user', 'root');
    $form->model->set('pass', 'root');

    $form->onSubmit(function ($f) use ($page) {
        try {
            $dsn = $f->model->get('type') . '://';
            $dsn .= $f->model->get('user');
            $dsn .= ':';
            $dsn .= $f->model->get('pass');
            $dsn .= '@';
            $dsn .= '' . $f->model->get('host') . ':' . $f->model->get('port');
            $dsn .= '/';
            $dsn .= $f->model->get('name');

            Persistence::connect($dsn);
            $string_config = <<<EOD
<?php

return [
    'dsn'=>'{$dsn}'
];
EOD;
            file_put_contents('config.php', $string_config);
        } catch (Throwable $e) {
            return new Message(
                'Error on connection : ' . $e->getMessage(),
                'negative'
            );
        }

        return $page->jsNext();
    });

    $loader->set(function (Loader $loader) {
        $dsn = $loader->getApp()->stickyGet('type') . ':';
        $dsn .= $loader->getApp()->stickyGet('user');
        $dsn .= ':';
        $dsn .= $loader->getApp()->stickyGet('pass');
        $dsn .= '@';
        $dsn .= $loader->getApp()->stickyGet('host') . ':' . $loader->getApp()->stickyGet('port');
        $dsn .= '/';
        $dsn .= $loader->getApp()->stickyGet('name');

        View::addTo($loader)->set('DSN : ' . $dsn);
    });
});

$wizard->addStep('Quickly checking if database is OK', function (View $page) {
    $page->getApp()->dbConnectFromWizard();

    // do migration
    $console = \MigratorConsole::addTo($page);
    $console->migrateModels([User::class, Role::class, AccessRule::class]);
});

$wizard->addStep('Populate Sample Data', function (View $page) {
    $page->getApp()->dbConnectFromWizard();

    Console::addTo($page)->set(function (Console $c) {
        $c->notice('Populating data...');

        $rule = new AccessRule($c->getApp()->db);
        $rule->each(function ($m) {$m->delete(); });

        $role = new Role($c->getApp()->db);
        $role->each(function ($m) {$m->delete(); })
            ->import([
                ['name' => 'User Role'],
                ['name' => 'Admin Role'],
            ]);

        $user = new User($c->getApp()->db);
        $user->each(function ($m) {$m->delete(); })
            ->import([
                [
                    'name' => 'Standard User',
                    'email' => 'user',
                    'role' => 'User Role',
                    'password' => 'user',
                ],
                [
                    'name' => 'Administrator',
                    'email' => 'admin',
                    'role' => 'Admin Role',
                    'password' => 'admin',
                ],
            ]);

        $rule->import([
            [
                'role' => 'Admin Role',
                'model' => '\\atk4\login\\Model\\User',
                'all_visible' => true,
                'all_editable' => true,
            ],
            [
                'role' => 'User Role',
                'model' => '\\atk4\login\\Model\\Role',
                'all_visible' => true,
                'all_editable' => false,
            ],
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

$wizard->addFinish(function ($p) {
    $p->getApp()->redirect(['index']);
});
