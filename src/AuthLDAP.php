<?php

namespace atk4\login;

use atk4\core\Exception;

/**
 * Authentication controller for LDAP authentication.
 */
class AuthLDAP extends Auth
{
  /**
   * Which field to look up user by.
   *
   * @var string
   */
  public $fieldLogin = 'username';

  /**
   * Caption of the first field on the Login Form.
   *
   * @var string
   */
  public $fieldLoginCaption = 'Username';

  /**
   * LDAP server URL
   * Example: 'ldap://ldap.acme.com/'
   *
   * @var string
   */
  public $ldapUrl = null;

  /**
   * LDAP server proxy user to use in order to connect to directory and look for DN of username required to bind
   * Example: 'cn=admin,ou=it,o=acme'
   *
   * @var string
   */
  public $ldapProxyUser = null;

  /**
   * LDAP server proxy user password
   *
   * @var string
   */
  public $ldapProxyPassword = null;

  /**
   * LDAP Base DistinguishedName: where to start the search for the provided username
   * Example: 'o=acme'
   *
   * @var string
   */
  public $ldapBaseDn = null;

  /**
   * LDAP Object filter: Return only users whose attributes match this filter
   * Example: ['objectClass', 'Person'];
   *
   * @var array
   */
  public $ldapObjFilter = null;

  /**
   * LDAP CommonName attribute name: the name of the attribute in which the username exists
   *
   * @var string
   */
  public $ldapCnAttr = 'cn';

  /**
   * LDAP attribute storing the full name of the user
   * Example: 'fullname'
   *
   * @var string
   */
  public $ldapFullNameAttr = null;

  /**
   * LDAP attribute storing the email address of the user
   * Example: 'mail'
   *
   * @var string
   */
  public $ldapEmailAttr = null;

  /**
   * LDAP attribute storing the ATK role id of the user
   *
   * @var string
   */
  public $ldapAtkRoleAttr = null;

  /**
   * Default role assigned to the user upon first login into ATK
   *
   * @var string
   */
  public $ldapUserDefaultRole = null;

  /**
   * Constructor.
   *
   * @param array $options
   *
   * @throws Exception
   */
  public function __construct($options = [])
  {
    parent::__construct($options);
    if (!extension_loaded('ldap')) {
      throw new Exception(['Maybe you should enable PHP LDAP extension before using LDAP authentication']);
    }
  }

  /**
   * Call this method to verify credentials.
   *
   * In this child class of User, we first set a few values and then let the parent class do its job.
   *
   * @throws Exception
   */
  public function check()
  {
    // The following fields are loaded from LDAP and not editable.
    // Setting them read-only nicely removes them from the Preferences screen.
    // Note: We do show the email address in parentheses in the subheader if it was retrieved from LDAP.
    if ($this->user->loaded()) {
      $this->user->getField($this->fieldLogin)->read_only = true;
      if ($this->ldapFullNameAttr) {
        $this->user->getField('name')->read_only = true;
      }
      if ($this->ldapEmailAttr) {
        $this->user->getField('email')->read_only = true;
      }
      if ($this->ldapAtkRoleAttr) {
        $this->user->getField('role_id')->read_only = true;
      }
    }

    parent::check();
  }

   /**
   * Try to log in user using LDAP.
   * This function completely replaces the one from the parent class.
   *
   * @param string $username
   * @param string $password
   *
   * @throws Exception
   *
   * @return bool
   */
  public function tryLogin($username, $password)
  {
    // It is imperative to check this because LDAP might successfully bind
    // in "unauthenticated" mode when password is empty
    if (empty($password)) {
      return false;
    }

    /*
     * LDAP authentication happens in two steps: 
     * 1) From the provided username, try to retrieve the user DN. This can be done anonymously, or via a proxy user.
     * 2) With the found DN try to bind using the password specified by the user.
     */

    $ldapConn = ldap_connect($this->ldapUrl);
    if ($this->ldapProxyUser) {
      ldap_bind($ldapConn, $this->ldapProxyUser, utf8_encode($this->ldapProxyPassword));
    }
    $sr = ldap_search($ldapConn,
      $this->ldapBaseDn,
      sprintf('(&(%s=%s)(%s=%s))', $this->ldapCnAttr, $username, $this->ldapObjFilter[0], $this->ldapObjFilter[1]),
      array("dn", $this->ldapFullNameAttr, $this->ldapEmailAttr, $this->ldapAtkRoleAttr));
    $info = ldap_get_entries($ldapConn, $sr);
    if ($this->ldapProxyUser) {
      ldap_unbind($ldapConn);
    }
    if ($info['count']==1) {
      if (ldap_bind($ldapConn, $info[0]['dn'], utf8_encode($password))) {
        ldap_unbind($ldapConn);

        $user = clone $this->user;
        $user->unload();
        $user->tryLoadBy($this->fieldLogin, $username);
        if (!$user->loaded()) {
          $user->insert([
            $this->fieldLogin => $username,
            'name' => isset($this->ldapFullNameAttr)
              ? $info[0][$this->ldapFullNameAttr][0] ?? ''
              : '',
            'email' => isset($this->ldapEmailAttr)
              ? $info[0][$this->ldapEmailAttr][0] ?? ''
              : '',
            'role_id'=> isset($this->ldapAtkRoleAttr)
              ? $info[0][$this->ldapAtkRoleAttr][0] ?? $this->ldapUserDefaultRole
              : $this->ldapUserDefaultRole,
          ]);
          $user->tryLoadBy($this->fieldLogin, $username);
        } else {
          if (isset($this->ldapFullNameAttr) && $info[0][$this->ldapFullNameAttr][0]) {
            $user['name'] = $info[0][$this->ldapFullNameAttr][0];
          }
          if (isset($this->ldapEmailAttr) && $info[0][$this->ldapEmailAttr][0]) {
            $user['email'] = $info[0][$this->ldapEmailAttr][0];
          }
          if (isset($this->ldapAtkRoleAttr) && $info[0][$this->ldapAtkRoleAttr][0]) {
            $user['role'] = $info[0][$this->ldapAtkRoleAttr][0];
          } else {
            $user['role'] = $this->ldapUserDefaultRole;
          }
          $user->save();
        }
        $this->hook('loggedIn', [$user]);
        $this->getSessionPersistence()->update($user, 1, $user->get());
        return true;
      }
    }
    return false;
  }
}
