<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Login\Acl;
use Atk4\Login\Auth;

class AclTest extends GenericTestCase
{
    protected function setupDefaultDb(): void
    {
        parent::setupDefaultDb();

        $clientModel = (new AclTestClient($this->db));
        $this->createMigrator($clientModel)->create();
        $clientModel->import([
            ['name' => 'John Doe', 'vat_number' => 'GB1234567890', 'balance' => 1234.56, 'active' => true],
            ['name' => 'Jane Doe', 'vat_number' => null, 'balance' => 50, 'active' => true],
            ['name' => 'Pokemon', 'vat_number' => 'LV-13141516', 'balance' => 100.65, 'active' => true],
            ['name' => 'Captain Jack', 'vat_number' => null, 'balance' => -600, 'active' => false],
        ]);

        // update ACL setup for our demo client model
        $this->createAccessRuleModel()->delete(2);
        $this->createAccessRuleModel()->load(1)->save(['model' => AclTestClient::class]);
    }

    protected function createAuthAndLogin(string $user): Auth
    {
        $auth = new Auth(['check' => false]);

        $auth->setModel($this->createUserModel());
        $auth->tryLogin($user, $user === 'admin' ? 'admin' : 'user');
        $this->assertTrue($auth->isLoggedIn());

        $auth->setAcl(new Acl(), $this->db);

        return $auth;
    }

    protected function invokeAndAssertAclException(\Closure $fx): void
    {
        $e = null;
        try {
            $fx();
        } catch (\Exception $e) {
        }

        $this->assertInstanceOf(\Atk4\Core\Exception::class, $e); // TODO should be specific ACL exception
    }

    public function testAclBasic(): void
    {
        $this->setupDefaultDb();

        $this->createAuthAndLogin('user');

        // "user" user can edit client.vat_number field
        $clientEntity = (new AclTestClient($this->db))->load(1);
        $this->assertTrue($clientEntity->getField($clientEntity->fieldName()->vat_number)->isEditable());
        $clientEntity->save([$clientEntity->fieldName()->vat_number => 'new']);
        $this->assertSame($clientEntity->vat_number, 'new');

        // but not client.balance field
        $this->assertFalse($clientEntity->getField($clientEntity->fieldName()->balance)->isEditable());
        $clientEntity = (new AclTestClient($this->db))->load(1);
//        // TODO ACL currently work on UI level only, reject edit on data model layer
//        $this->invokeAndAssertAclException(function () use ($clientEntity) {
//            $clientEntity->save([$clientEntity->fieldName()->balance => 100]);
//        });
//        $this->assertSame($clientEntity->balance, 1234.56);

        // must work also for extended Model
        $clientEntity = (new class($this->db) extends AclTestClient {})->load(1);
        $this->assertTrue($clientEntity->getField($clientEntity->fieldName()->vat_number)->isEditable());
//        // TODO https://github.com/atk4/login/issues/39 - instanceof relation must be fully supported by ACL
//        $this->assertFalse($clientEntity->getField($clientEntity->fieldName()->balance)->isEditable());
    }
}

/**
 * @property string $name       @Atk4\Field()
 * @property string $vat_number @Atk4\Field()
 * @property float  $balance    @Atk4\Field()
 * @property bool   $active     @Atk4\Field()
 */
class AclTestClient extends Model
{
    public $table = 'unit_client';

    protected function init(): void
    {
        parent::init();

        $this->addField('name', ['required' => true]);
        $this->addField('vat_number');
        $this->addField('balance', ['type' => 'atk4_money']);
        $this->addField('active', ['type' => 'boolean', 'default' => true]);
    }
}
