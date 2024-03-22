<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Login\Acl;
use Atk4\Login\Auth;

class AclTest extends GenericTestCase
{
    #[\Override]
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
        $this->createAccessRuleModel()->load(1)
            ->save(['model' => AclTestClient::class]);
        $this->createAccessRuleModel()->load(1)->duplicate()
            ->save(['model' => AclTestInterface::class]);
    }

    protected function createAuthAndLogin(string $user): Auth
    {
        $auth = new Auth($this->createAppForSession(), ['check' => false]);

        $auth->setModel($this->createUserModel());
        $auth->tryLogin($user, $user === 'admin' ? 'admin' : 'user');
        self::assertTrue($auth->isLoggedIn());

        $auth->setAcl(new Acl(), $this->db);

        return $auth;
    }

    /**
     * @param \Closure(): void $fx
     */
    protected function invokeAndAssertAclException(\Closure $fx): void
    {
        $e = null;
        try {
            $fx();
        } catch (\Exception $e) {
        }

        self::assertInstanceOf(\Atk4\Core\Exception::class, $e); // TODO should be specific ACL exception
    }

    public function testAclBasic(): void
    {
        $this->setupDefaultDb();

        $this->createAuthAndLogin('user');

        // "user" user can edit client.vat_number field
        $clientEntity = (new AclTestClient($this->db))->load(1);
        self::assertTrue($clientEntity->getField($clientEntity->fieldName()->vat_number)->isEditable());
        $clientEntity->save([$clientEntity->fieldName()->vat_number => 'new']);
        self::assertSame($clientEntity->vat_number, 'new');

        // but not client.balance field
        self::assertFalse($clientEntity->getField($clientEntity->fieldName()->balance)->isEditable());
        $clientEntity = (new AclTestClient($this->db))->load(1);
        //        // TODO ACL currently work on UI level only, reject edit on data model layer
        //        $this->invokeAndAssertAclException(function () use ($clientEntity) {
        //            $clientEntity->save([$clientEntity->fieldName()->balance => 100]);
        //        });
        //        static::assertSame($clientEntity->balance, 1234.56);

        // must also match parent classes
        $clientEntity = (new class($this->db) extends AclTestClient {})->load(1);
        self::assertTrue($clientEntity->getField($clientEntity->fieldName()->vat_number)->isEditable());
        self::assertFalse($clientEntity->getField($clientEntity->fieldName()->balance)->isEditable());

        // and interfaces
        $clientEntity = (new AclTestClient2($this->db))->load(1);
        self::assertTrue($clientEntity->getField($clientEntity->fieldName()->vat_number)->isEditable());
        self::assertFalse($clientEntity->getField($clientEntity->fieldName()->balance)->isEditable());
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

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('name', ['required' => true]);
        $this->addField('vat_number');
        $this->addField('balance', ['type' => 'atk4_money']);
        $this->addField('active', ['type' => 'boolean', 'default' => true]);
    }
}

interface AclTestInterface {}

/**
 * @property string $vat_number @Atk4\Field()
 * @property float  $balance    @Atk4\Field()
 */
class AclTestClient2 extends Model implements AclTestInterface
{
    public $table = 'unit_client';

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('vat_number');
        $this->addField('balance', ['type' => 'atk4_money']);
    }
}
