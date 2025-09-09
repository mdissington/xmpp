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

namespace Fabiang\Xmpp\Stream;

use Fabiang\Xmpp\Event\XMLEventInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Fabiang\Xmpp\Stream\XMLStream::class)]
class XMLStreamTest extends TestCase
{

    protected XMLStream $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->object = new XMLStream();
    }

    /**
     * Test parsing xml.
     *
     * @covers Fabiang\Xmpp\Stream\XMLStream
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::startXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::createAttributeNodes
     * @uses Fabiang\Xmpp\Stream\XMLStream::endXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::dataXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::cacheEvent
     * @uses Fabiang\Xmpp\Stream\XMLStream::trigger
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::setEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     * @return void
     */
    public function testParse()
    {
        $triggered = 0;

        $this->object->getEventManager()->attach(
            '{http://etherx.jabber.org/streams}features',
            function () use (&$triggered) {
                $triggered++;
            }
        );

        $this->object->getEventManager()->attach(
            '{urn:ietf:params:xml:ns:xmpp-sasl}mechanism',
            function () use (&$triggered) {
                $triggered++;
            }
        );

        $xml = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'
            .'<stream:stream xmlns:stream="http://etherx.jabber.org/streams" '
            .'xmlns="jabber:client" from="gamebox" id="b9a85bbd" xml:lang="en" version="1.0">'
            .'<stream:features>'
            .'<test someattribute="test" stream:anothertest="foo"/>'
            .'<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"></starttls>'
            .'<mechanisms xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
            .'<mechanism>DIGEST-MD5</mechanism>'
            .'<mechanism>PLAIN</mechanism>'
            .'<mechanism>ANONYMOUS</mechanism>'
            .'<mechanism>CRAM-MD5</mechanism>'
            .'</mechanisms>'
            .'</stream:features>';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" from="gamebox" '
            .'id="b9a85bbd" xml:lang="en" version="1.0"><stream:features>'
            .'<test someattribute="test" stream:anothertest="foo"/>'
            .'<starttls '
            .'xmlns="urn:ietf:params:xml:ns:xmpp-tls"/><mechanisms xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
            .'<mechanism>DIGEST-MD5</mechanism><mechanism>PLAIN</mechanism><mechanism>ANONYMOUS</mechanism>'
            .'<mechanism>CRAM-MD5</mechanism></mechanisms></stream:features></stream:stream>'
            ."\n";

        $result = $this->object->parse($xml);
        $this->assertInstanceOf('\DOMDocument', $result);
        $this->assertSame($expected, $result->saveXML());
        $this->assertSame(10, $triggered, 'Event where not triggered five times');
    }

    /**
     * Test parsing xml if xml stream is finished.
     *
     * @covers ::parse
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::startXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::createAttributeNodes
     * @uses Fabiang\Xmpp\Stream\XMLStream::endXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::dataXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::cacheEvent
     * @uses Fabiang\Xmpp\Stream\XMLStream::trigger
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::setEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     * @return void
     */
    public function testParseFinalEndTag()
    {
        $triggered = 0;

        $this->object->getEventManager()->attach(
            '{http://etherx.jabber.org/streams}stream',
            function (XMLEventInterface $event) use (&$triggered) {
                if ($event->isEndTag()) {
                    $triggered++;
                }
            }
        );

        $start = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<stream:stream xmlns:stream="http://etherx.jabber.org/streams" '
            .'xmlns="jabber:client" from="gamebox" id="b9a85bbd" xml:lang="en" version="1.0">';

        $end = '</stream:stream>';

        $this->object->parse($start);
        $this->object->parse($end);
        $this->assertSame(1, $triggered);
    }

    /**
     * Test parsing xml.
     *
     * @covers ::parse
     * @covers ::clearDocument
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::startXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::createAttributeNodes
     * @uses Fabiang\Xmpp\Stream\XMLStream::endXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::dataXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::cacheEvent
     * @uses Fabiang\Xmpp\Stream\XMLStream::trigger
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::setEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     * @return void
     */
    public function testParseChallenge()
    {
        $xml = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><stream:stream '
            .'xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" from="gamebox" id="7f3ceab2" '
            .'xml:lang="en" version="1.0">';
        $this->assertInstanceOf('\DOMDocument', $this->object->parse($xml));

        $xml = '<stream:features><starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"></starttls>'
            .'<mechanisms xmlns="urn:ietf:params:xml:ns:xmpp-sasl"><mechanism>DIGEST-MD5</mechanism>'
            .'<mechanism>PLAIN</mechanism><mechanism>ANONYMOUS</mechanism><mechanism>CRAM-MD5</mechanism>'
            .'</mechanisms><compression xmlns="http://jabber.org/features/compress"><method>zlib</method>'
            .'</compression><auth xmlns="http://jabber.org/features/iq-auth"/>'
            .'<register xmlns="http://jabber.org/features/iq-register"/></stream:features>';
        $this->assertInstanceOf('\DOMDocument', $this->object->parse($xml));

        $xml = '<proceed ';
        $this->assertInstanceOf('\DOMDocument', $this->object->parse($xml));

        $xml = 'xmlns="urn:ietf:params:xml:ns:xmpp-tls"/>';
        $this->assertInstanceOf('\DOMDocument', $this->object->parse($xml));
    }

    /**
     * Test parsing with namespaces.
     *
     * @covers ::parse
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::startXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::createAttributeNodes
     * @uses Fabiang\Xmpp\Stream\XMLStream::endXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::dataXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::cacheEvent
     * @uses Fabiang\Xmpp\Stream\XMLStream::trigger
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::setEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     * @return void
     */
    public function testParseNamespaces()
    {
        $events = array();
        $this->object->getEventManager()->attach('*', function (XMLEventInterface $event) use (&$events) {
            $events[] = $event->getName();
        });

        $xml = <<<'XML'
                    <?xml version="1.0" encoding="UTF-8"?>
                    <stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client">
                        <stream:features></stream:features>
                        <iq>test</iq>
                        <iq xmlns="jabber:client">testtwo</iq>
                    </stream:stream>
                    XML;

        $this->object->parse($xml);

        $this->assertSame(
            [
                '{http://etherx.jabber.org/streams}stream',
                '{http://etherx.jabber.org/streams}features',
                '{http://etherx.jabber.org/streams}features',
                '{jabber:client}iq',
                '{jabber:client}iq',
                '{jabber:client}iq',
                '{jabber:client}iq',
                '{http://etherx.jabber.org/streams}stream',
            ],
            $events
        );
    }

    /**
     * Test parsing invalid XML.
     *
     * @covers ::parse
     * @uses Fabiang\Xmpp\Exception\XMLParserException
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @return void
     */
    public function testParseInvalidXml()
    {
        $this->expectException(\Fabiang\Xmpp\Exception\XMLParserException::class);
        $this->object->parse('<tsst<>');
    }

    /**
     * Test parsing with namespaces, when a parse() is called second time without "xmlns"
     *
     * @covers ::parse
     * @covers ::endXml
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::startXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::createAttributeNodes
     * @uses Fabiang\Xmpp\Stream\XMLStream::cacheEvent
     * @uses Fabiang\Xmpp\Stream\XMLStream::trigger
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::setEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     * @return void
     */
    public function testParseNamespaceCache()
    {
        $triggered = 0;

        $this->object->getEventManager()->attach(
            '{http://etherx.jabber.org/streams}features',
            function () use (&$triggered) {
                $triggered++;
            }
        );
        $this->object->getEventManager()->attach(
            '{http://etherx.jabber.org/streams}stream',
            function () use (&$triggered) {
                $triggered++;
            }
        );

        $this->object->parse(
            '<stream:stream xmlns:stream="http://etherx.jabber.org/streams"'
            .' xmlns="jabber:client" from="gamebox" id="b9a85bbd" xml:lang="en" version="1.0">'
        );
        $this->object->parse('<stream:features></stream:features>');
        $this->object->parse('</stream:stream>');
        $this->assertSame(4, $triggered, 'Event where not triggered three times');
    }

    /**
     * Test setting and getting event manager.
     *
     * @covers ::getEventManager
     * @covers ::setEventManager
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     * @return void
     */
    public function testSetAndGetEventManager()
    {
        $this->assertInstanceOf('\Fabiang\Xmpp\Event\EventManager', $this->object->getEventManager());
        $eventManager = $this->getMockBuilder('\Fabiang\Xmpp\Event\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertSame($eventManager, $this->object->setEventManager($eventManager)->getEventManager());
    }

    /**
     * @covers ::parse
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::__construct
     * @uses Fabiang\Xmpp\Stream\XMLStream::clearDocument
     * @uses Fabiang\Xmpp\Stream\XMLStream::startXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::endXml
     * @uses Fabiang\Xmpp\Stream\XMLStream::createAttributeNodes
     * @uses Fabiang\Xmpp\Stream\XMLStream::cacheEvent
     * @uses Fabiang\Xmpp\Stream\XMLStream::trigger
     * @uses Fabiang\Xmpp\Stream\XMLStream::reset
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::setEventManager
     * @uses Fabiang\Xmpp\Stream\XMLStream::getEventObject
     */
    public function testParseCutted()
    {
        $this->assertInstanceOf('DOMDocument', $this->object->parse('<'));
        $this->assertInstanceOf('DOMDocument', $this->object->parse('features xmlns="test"></features>'));
    }

    public function testAttributeWithAmp()
    {
        $this->assertInstanceOf('DOMDocument', $this->object->parse('<x attr="with&amp;in-val" xmlns="test" />'));
    }
}
