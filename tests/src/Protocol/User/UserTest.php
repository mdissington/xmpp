<?php

namespace Fabiang\Xmpp\Protocol\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Fabiang\Xmpp\Protocol\User\User::class)]
class UserTest extends TestCase
{

    /**
     * @var User
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new User;
    }

    /**
     * @covers Fabiang\Xmpp\Protocol\User\User
     * @return void
     */
    public function testSetterAndGetters()
    {
        $this->assertSame('1', $this->object->setName('1')->getName());
        $this->assertSame('2', $this->object->setJid('2')->getJid());
        $this->assertSame('3', $this->object->setSubscription(3)->getSubscription());
        $this->assertSame(array(1, 2, 3), $this->object->setGroups(array(1, 2, 3))->getGroups());
        $this->assertContains('test', $this->object->addGroup('test')->getGroups());

        $this->assertNull($this->object->setName('')->getName());
        $this->assertNull($this->object->setName(null)->getName());
    }

}
