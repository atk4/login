<?php
namespace atk4\login\Model;

use atk4\data\Model;

# Features of User model
use atk4\login\Feature\SetupModel;

/**
 * Example user data model.
 */
class UserLDAP extends Model
{
  use SetupModel;

  public $table = 'login_user_ldap';
  public $caption = 'User';
  public $title_field = 'username';

  public function init()
  {
    parent::init();

    $this->addField('username');
    $this->addField('name');
    $this->addField('email');

    // currently user can have only one role. In future it should be n:n relation
    $this->hasOne('role_id', [Role::class, 'our_field'=>'role_id', 'their_field'=>'id', 'caption'=>'Role'])->withTitle();

    // traits
    $this->setupUserModelLDAP();
  }
}
