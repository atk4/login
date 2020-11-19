<?php

declare(strict_types=1);

namespace atk4\login\demo;

use atk4\login\Acl;
use atk4\login\Auth;
use atk4\ui\Layout;

/**
 * Example implementation of your Authenticated application.
 */
class App extends AbstractApp
{
    public $title = 'Demo App';

    protected function init(): void
    {
        parent::init();

        $this->initLayout([Layout\Admin::class]);

        // iframe for demo app
        $i = \atk4\ui\View::addTo($this, ['green' => true, 'ui' => 'segment'])
            ->setElement('iframe')
            ->setStyle(['width' => '100%', 'height' => '800px']);
        $i->js(true)->hide();

        // Construct menu
        $this->layout->menuLeft->addItem(['Dashboard', 'icon' => 'info'], ['index']);
        $this->layout->menuLeft->addItem(['Setup demo database', 'icon' => 'cogs'], ['admin-setup']);

        $g = $this->layout->menuLeft->addGroup(['Forms']);
        $g->addItem(['Sign-up form', 'icon' => 'edit'], ['form-register']);

        $x = $g->addItem(['Login form', 'icon' => 'edit'])->on('click', [
            $i->js()->show(),
            $i->js()->attr('src', $this->url('form-login')),
        ]);

        $g->addItem(['Forgot password form', 'icon' => 'edit'], ['form-forgot']);

        $g = $this->layout->menuLeft->addGroup(['ACL']);
        $g->addItem(['Client list (for testing)', 'icon' => 'table'], ['acl-clients']);

        $g = $this->layout->menuLeft->addGroup(['Admin']);
        $g->addItem(['User Admin', 'icon' => 'users'], ['admin-users']);
        $g->addItem(['Role Admin', 'icon' => 'tasks'], ['admin-roles']);
    }
}
