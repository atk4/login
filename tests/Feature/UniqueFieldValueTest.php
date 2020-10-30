<?php

declare(strict_types=1);

namespace atk4\login\tests\Feature;

use atk4\data\Model;
use atk4\data\ValidationException;
use atk4\login\Feature\UniqueFieldValue;
use atk4\login\tests\Generic;

class UniqueFieldValueTest extends Generic
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
            use UniqueFieldValue;

            public $table = 'test';

            protected function init(): void {
                parent::init();
                $this->addField('name');
                $this->setUnique('name');
            }
        };
        return new $c($this->db, 'test');
    }

    public function testBasic()
    {
        $this->setupDefaultDb();
        $m = $this->getTestModel();

        (clone $m)->save(['name' => 'Test2']);
        $this->assertSame(2, count($m->export()));

        $this->expectException(ValidationException::class);
        (clone $m)->save(['name' => 'Test1']);
    }
}
