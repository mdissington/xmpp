<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\EventListener;

use PHPUnit\Framework\TestCase;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\TestHandler;
use Fabiang\Xmpp\Event\Event;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Event\EventManagerInterface;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-01-21 at 14:18:43.
 *
 * @coversDefaultClass Fabiang\Xmpp\EventListener\Logger
 */
class LoggerTest extends TestCase
{

    /**
     * @var Logger
     */
    protected $object;

    /**
     *
     * @var TestHandler
     */
    protected $handler;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new Logger;

        $logger        = new MonologLogger('xmpp');
        $this->handler = new TestHandler(MonologLogger::DEBUG);
        $logger->pushHandler($this->handler);

        $options = new Options;
        $options->setLogger($logger);
        $this->object->setOptions($options);
    }

    /**
     * Test event
     *
     * @covers ::event
     * @covers ::getOptions
     * @covers ::setOptions
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Options
     * @return void
     */
    public function testEvent()
    {
        $event = new Event();
        $event->setParameters(array('test', MonologLogger::EMERGENCY));
        $this->object->event($event);
        $this->assertTrue($this->handler->hasEmergency('test'));
    }

    /**
     * @covers ::attachEvents
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Options
     * @retrun void
     */
    public function testAttachEvents()
    {
        $this->object->attachEvents();

        $eventList = $this->object->getEventManager()->getEventList();
        $logger    = $eventList['logger'][0];
        $this->assertSame($this->object, $logger[0]);
        $this->assertSame('event', $logger[1]);
    }

    /**
     * @covers ::getEventManager
     * @covers ::setEventManager
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Options
     */
    public function testSetAndGetEventManager()
    {
        $this->assertInstanceOf(EventManagerInterface::class, $this->object->getEventManager());
        $eventManager = $this->createMock(EventManagerInterface::class);
        $this->assertSame($eventManager, $this->object->setEventManager($eventManager)->getEventManager());
    }
}
