<?php

declare(strict_types=1);

namespace atk4\login\tests\Feature;

use atk4\data\Model;
use atk4\data\Persistence;
use atk4\login\Feature\PasswordManagement;

class PasswordManagementTest extends \atk4\core\AtkPhpunit\TestCase
{
    public function testGenerateRandomPassword()
    {
        $class = new class() extends Model {
            use PasswordManagement;
        };
        $model = new $class(new Persistence\Array_());
        $this->assertIsString($model->generate_random_password(4));
    }
}
