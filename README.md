[ATK UI](https://github.com/atk4/ui) implements a high-level User Interface for Web App - such as **Admin System**. One of the most common things for the Admin system is a log-in screen.

Although you can implement log-in form easily, this add-on does everything for you!

## Installation

Install through composer `composer require atk4/login`

Then add `Auth` into your app and set appropriate user controller:

```php
$app = new \atk4\ui\App();
$app->initLayout('Admin');
$app->dbConnect($dsn);

// ADD THIS CODE:
$app->add(new \atk4\login\Auth())
    ->setModel(new User($app->db));

// The rest of YOUR UI code will now be protected
$app->add('CRUD')->setModel(new Client($app->db));
```

(If you do not have User model yet, you can extend or use \atk4\login\Model\User).

![Login](./docs/login-form.png)

## Features

Here are all the classes implemented:

-   Full transparent authentication
    -   Populates user menu with name of current user
    -   Adds log-out link
    -   Adds Preferences page
-   Field\Password - password hashing, safety, generation and validation
-   Model\User - basic user entity that can be extended
-   LoginForm - username/password login form
-   RegisterForm - registration form
-   Auth - authentication controller, verify and record logged state
-   UserAdmin - UI for user administration
-   Layout\Narrow - SemanticUI-based narrow responsive layout login/registration forms
-   Templates for forms an messages
-   Demos for all of the above

When used default installation, it will relay on various other components (such as LoginForm), however you can also use those components individually.

## Advanced Usage

There are two modes of operation - Automated and Manual. Automated handles display of forms based on currently logged state automatically. It was already presented in the "Installation" section above.

For a more advanced usage, you can either tweak Automated mode or use individual components manually.

### Tweaking Automated Mode

When you initialize 'Auth' class you may inject property values:

```php
$app->auth = $app->add(new \atk4\login\Auth([
    'hasPreferences' => false, // do not show Preferences page/form
    'pageDashboard' => 'dashboard', // name of the page, where user arrives after login
    'pageExit' => 'goodbye', // where to send user after logout
    
    // Oter options:
    // 'hasUserMenu' => false,  // will disable interaction with Admin Layout user menu
]));
$app->auth->setModel(new User($app->db));
```

### Using Manual Mode

In the manual mode, no checks will be performed, and you are responsible for authenticating user yourself. This works best if you have a more complex auth logic.

``` php
$app->auth = $app->add(new \atk4\login\Auth([
    'check' => false
]));
$app->auth->setModel(new User($app->db));


// Now manually use login logic
if (!$app->auth->user->loaded()) {
  $app->add([new \atk4\login\LoginForm(), 'auth'=>$app->auth]);
}
```

#### Adding sign-up form

``` php
$app->add(new \atk4\login\RegisterForm())
    ->setModel(new \atk4\login\Model\User($app->db));
```

Displays email and 2 password fields (for confirmation). If filled successfully will create new record for `\atk4\login\Model\User`. Will cast email to lowercase before adding. Things to try:

-   Extend or use your own User class
-   Add more fields to registration form
-   Decorate registration form with message and links
-   Use multi-column form layout

#### Log-in form

![Login](./docs/login-form.png)

``` php
$app->add([
  new \atk4\login\LoginForm(), 
  'auth'=>$app->auth,
  //'successLink'=>['dashboard'],
  //'forgotLink'=>['forgot'],
]);
```

Displays log-in form and associate it with $auth. When form is filled, will attempt to authenticate using $auth's model. If password is typed correctly, will redirect to "successLink" (which will be passed to $app->url()). Things to try:

-   Redirect to login page if not authenticated
-   Add 3rd party authentication (authenticate using 3rd party lib, look up connected account, store into auth persistence)
-   Implement two factor authentication (store flag in auth persistence indicating if 2nd factor is carried out, if not display it)
-   Implement password verification delay after several unsuccessful attempts
-   Ask user to change password if it is about to expire

#### Dashboard

To check if user is currently logged in:

``` php
if ($app->auth->model->loaded()) {
  // logged-in
}
```

Auth model stores user model data in session, so if you delete user from database, he will not be automatically logged out. To log-out user explicitly, call `$app->auth->logout()`.

You may also access user data like this: `$app->auth->model['name']`; Things to try:

-   Explicitly load user record from database instead of cache only
-   Store last login / last access time in database
-   Move auth cache to MemCache

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

Things to try:

-   Ask user to verify old password before changing settings
-   Send SMS notification / email if any user setting has bees changed
-   Store user settings in multiple tables (join)

#### Password

Field 'password' is using a custom field class `Password`.  It appears as a regular password, but will be hashed before storing into the database. You can use this field in any model like this:

``` php
$model->addField('mypass', [new \atk4\login\Field\Password]);
```

Also the password will not be stored in session cache and will not be accessible directly. 

Things to try:

-   Add complexity validation
-   Add password recovery form
-   use CAPCHA when recovering password

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

Things to try:

-   Add additional information on details modal.
-   Add audit log for user actions (login, change password etc)

#### Migrations

Use of migration is optional, but can help by populating initial structure of your user model. Look inside file `demos/wizard.php`. It simply adds a console component, which will execute migration of 'User' model. 

Migration relies on https://github.com/atk4/schema. 

When migration is executed it simply checks to make sure that table for 'user' exists and has all required fields. It will not delete or change existing fields or tables.

## Roadmap

Generally we wish to keep this add-on clean, but very extensible, with various tutorials on how to implement various scenarios (noted above under "Things to try"). 

For some of those features we would like to add a better support in next releases:

-   [1.0] - add "$auth->check()" - for Automated authentication checks
-   [1.1] - add Password Reminder form and tutorial on integration with Email / SMS sending
-   [1.2] - add Password strength verification (and indicator)

If you would like to propose other features, please suggest them by opening ticket here:

-   https://github.com/atk4/login/issues
