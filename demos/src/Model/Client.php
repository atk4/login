<?php

declare(strict_types=1);

namespace Atk4\Login\Demo\Model;

use Atk4\Data\Model;

class Client extends Model
{
    public $table = 'demo_client';
    public $caption = 'Client';

    protected function init(): void
    {
        parent::init();

        $this->addField('name', ['required' => true]);
        $this->addField('vat_number');
        $this->addField('balance', ['type' => 'money']);
        $this->addField('active', ['type' => 'boolean', 'default' => true]);

        // custom action
        $this->addUserAction('test', function ($m) {
            return 'Test action run for ' . $m->getTitle() . ' !';
        });
    }
}
