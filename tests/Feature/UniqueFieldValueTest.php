<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Data\Model;
use Atk4\Data\ValidationException;
use Atk4\Login\Feature\UniqueFieldValueTrait;
use Atk4\Login\Tests\GenericTestCase;

class UniqueFieldValueTest extends GenericTestCase
{
    #[\Override]
    protected function setupDefaultDb(): void
    {
        $this->setDb([
            'test' => [
                1 => ['id' => 1, 'name' => 'Test1'],
            ],
        ]);
    }

    protected function createTestModel(): Model
    {
        return new class($this->db, ['table' => 'test']) extends Model {
            use UniqueFieldValueTrait;

            public $table = 'test';

            protected function init(): void
            {
                parent::init();

                $this->addField('name');
                $this->setUnique('name');
            }
        };
    }

    public function testBasic(): void
    {
        $this->setupDefaultDb();
        $m = $this->createTestModel();

        $entity = $m->createEntity();
        $entity->save(['name' => 'Test2']);
        self::assertCount(2, $m->export());

        $this->expectException(ValidationException::class);

        $entity = $m->createEntity();
        $entity->save(['name' => 'Test1']);
    }
}
