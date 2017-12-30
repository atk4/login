Many projects require some sort of authentication before user can access an application. This add-on for Agile Toolkit implements a Login form:

![Login](./docs/login-demo.png)

### Installation

Composer require this (`composer require atk4\login`) then run the following:

``` php
$app->auth = $app->add(new \atk4\login\Login();
$app->auth->setModel(new \atk4\login\User($app->db));
```

### Usage

There are two usage modes - Automated and Manual. For automated you do not need to set up anything simply execute `auth->check()`.  **Only MANUAL mode is currently implemented**

For manual page you would need to manually create pages for signup, login, etc. An extended usage example is offered in `demos/` folder.

#### Adding sign-up form

``` php
$app->add(new \atk4\login\RegisterForm())
    ->setModel(new \atk4\login\Model\User($app->db));
```

Displays email and 2 passwords, which must match. If filled successfully will create new account. Will lowercase email before adding. You also make your own form, simply copy RegisterForm class into your own and tweak it.

#### Log-in form

``` php
$app->add([
  new \atk4\login\LoginForm(), 
  'auth'=>$app->auth,
  //'successLink'=>['dashboard'],
  //'forgotLink'=>['forgot'],
]);
```

Displays log-in form and associate it with $auth. When form is filled, will attempt to authenticate using $auth's model. If password is typed correctly, will redirect to "successLink" (which will be passed to $app->url()).

#### Dashboard

To check if user is currently logged in:

``` php
if ($app->auth->model->loaded()) {
  // logged-in
}
```

Auth model stores user model data in session, so if you delete user from database, he will not be automatically logged out. To log-out user explicitly, call `$app->auth->logout()`.

You may also access user data like this: `$app->auth->model['name']`;

#### Profile Form

This form would allow user to change user data (including password) but only if user is authenticated. To implement profile form use:

``` php
$app->add('Form')->setModel($app->auth->user);
```

Demos open profile form in a pop-up window, if you wish to do it, you can use this code:

``` php
$app->add(['Button', 'Profile', 'primary'])->on('click', $app->add('Modal')->set(function($p) {
    $p->add('Form')->setModel($p->app->auth->user);
})->show());
```



#### Password

Field 'password' is using a custom field class `Password`.  It appears as a regular password, but will be hashed before storing into the database. You can use this field in any model like this:

``` php
$model->addField('mypass', [new \atk4\login\Field\Password]);
```

Also the password will not be stored in session cache and will not be accessible directly. 

#### Custom User Model

Although a basic User model is supplied, you can either extend it or use your own user model.

#### User Admin

We include a slightly extended "Admin" interface which includes page to see user details and change their password. To create admin page use:

``` php
$app->add(new \atk4\login\UserAdmin())
    ->setModel(new \atk4\login\Model\User($app->db));
```

 ![Login](./docs/admin-demo.png)

This uses a standard CRUD interface, enhancing it with additional actions:

-   key button allows to change user password and offers random password generator. Uses "input" field for a visible password. You can also use regular "edit" button which will contain asterisk-protected field for the password.
-   eye button is designed to show user details, such as which group he belongs to. Currently this panel and groups are not implemented.

![Login](./docs/change-password.png)

#### Migrations

Use of migration is optional, but can help by populating initial structure of your user model. Look inside file `demos/wizard.php`. It simply adds a console component, which will execute migration of 'User' model. 

Migration relies on https://github.com/atk4/schema. 

When migration is executed it simply checks to make sure that table for 'user' exists and has all required fields. It will not delete or change existing fields or tables.





# OLD and OBSOLETE README. DO NOT READ, WILL BE REWRITTEN

``` php
$app->auth = $app->add(new \atk4\login\Auth(new \atk4\login\User($app->db)));
$app->auth->check();

// only authenticated users allowed
```

### Using your own user model

Login comes with a User model, but in most cases you should probably use your own User model. You do not have to extend the \atk4\login\User class as long as you use this:

``` php
class User extends \atk4\data\Model {
    public $table = 'user';
  
    public $login_field = 'email';
    public $password_field = 'password';
  
    function init() {
    	parent::init();
      
        $this->addField('email');
        $this->addField('password', ['type'=>'password']);
    }
}
```

The password will be stored encrypted using standard PHP encryption.

### Enabling Registration and Password Reminders

Registration and Password Reminder require you to define outbox - a way how to communicate with the user:

``` php
new \atk4\login\Auth([
  new User(),
  'outbox'=> $app->outbox,
  'register'=>true,
  'reminder'=>true
]);
```

The above will enable password reminders and registrations.

### Show user change password

When user is logged in, you can access it through `$auth->user`. You can use a built-in UI for user's password management:

``` php
$auth->addChangePassword($app->layout->menu_user);
```

This will add a new item into User Menu, which will allow user to change their current password.

### User Admin

For a privileged users, you can also use a component for managing users:

``` php
$user_admin = $layout->add(new \atk4\login\UserAdmin($auth));
```

This will show CRUD with list of users and provide you with options to change user passwords, send them reminders, disable users, etc.

UserAdmin extends CRUD so you can easily add more actions:

``` php
// Manually send a message to the user
$user_admin->addAction(new \atk4\sendgrid\Action\Message());
```

