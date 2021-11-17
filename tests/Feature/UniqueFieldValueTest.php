<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Data\Model;
use Atk4\Data\ValidationException;
use Atk4\Login\Feature\UniqueFieldValueTrait;
use Atk4\Login\Tests\GenericTestCase;

class UniqueFieldValueTest extends GenericTestCase
{
    protected function setupDefaultDb()
    {
        $this->setDb([
            'test' => [
                1 => ['id' => 1, 'name' => 'Test1'],
            ],
        ]);
    }

    protected function getTestModel()
    {
        $c = new class() extends Model {
            use UniqueFieldValueTrait;

            public $table = 'test';

            protected function init(): void
            {
                parent::init();
                $this->addField('name');
                $this->setUnique('name');
            }
        };

        return new $c($this->db, ['table' => 'test']);
    }

    public function testBasic()
    {
        $this->setupDefaultDb();
        $m = $this->getTestModel();

        $entity = $m->createEntity();
        $entity->save(['name' => 'Test2']);
        $this->assertSame(2, count($m->export()));

        $this->expectException(ValidationException::class);

        $entity = $m->createEntity();
        $entity->save(['name' => 'Test1']);
    }
}
