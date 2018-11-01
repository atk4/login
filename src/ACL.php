<?php

namespace atk4\login;

/**
 * Access Control Layer. Create one and pass it to your Auth controller.
 */

class ACL {

    /**
     * References an auth controller, so we can look up who is logged
     * in and what their permissions are
     */
    public $auth;

    /**
     * Given a model, this will apply some restrictions on it
     */
    function applyRestrictions(\atk4\data\Persistence $p, \atk4\data\Model $m) {

        // Extend this method
        // if($m instanceof Model\User && !$this->auth->user['is_admin']) {
        //      $m->getElement('is_admin')->read_only = true;
        // }
    }
}
