<?php

namespace Fabiang\Xmpp\Stream;

use Fabiang\Xmpp\Event\EventManager;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-12-31 at 21:28:14.
 */
class XMLStreamTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var XmlStream
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new XMLStream;
    }

    /**
     * Test parsing xml.
     *
     * @covers Fabiang\Xmpp\Stream\XmlStream
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
            . '<stream:stream xmlns:stream="http://etherx.jabber.org/streams" '
            . 'xmlns="jabber:client" from="gamebox" id="b9a85bbd" xml:lang="en" version="1.0">'
            . '<stream:features>'
            . '<test someattribute="test" stream:anothertest="foo"/>'
            . '<starttls xmlns="urn:ietf:params:xml:ns:xmpp-tls"></starttls>'
            . '<mechanisms xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
            . '<mechanism>DIGEST-MD5</mechanism>'
            . '<mechanism>PLAIN</mechanism>'
            . '<mechanism>ANONYMOUS</mechanism>'
            . '<mechanism>CRAM-MD5</mechanism>'
            . '</mechanisms>'
            . '</stream:features>';
        
        $expected = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<stream:stream xmlns:stream="http://etherx.jabber.org/streams" xmlns="jabber:client" from="gamebox" '
            . 'id="b9a85bbd" xml:lang="en" version="1.0"><stream:features>'
            . '<test someattribute="test" stream:anothertest="foo"/>'
            . '<starttls '
            . 'xmlns="urn:ietf:params:xml:ns:xmpp-tls"/><mechanisms xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
            . '<mechanism>DIGEST-MD5</mechanism><mechanism>PLAIN</mechanism><mechanism>ANONYMOUS</mechanism>'
            . '<mechanism>CRAM-MD5</mechanism></mechanisms></stream:features></stream:stream>'
            . "\n";

        $result = $this->object->parse($xml);
        $this->assertInstanceOf('\DOMDocument', $result);
        $this->assertSame($expected, $result->saveXML());
        $this->assertSame(6, $triggered, 'Event where not triggered five times');
    }
    
    /**
     * Test parsing with namespaces, when a parse() is called second time without "xmlns"
     * 
     * @covers Fabiang\Xmpp\Stream\XmlStream::parse
     * @covers Fabiang\Xmpp\Stream\XmlStream::endXml
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
            . ' xmlns="jabber:client" from="gamebox" id="b9a85bbd" xml:lang="en" version="1.0">'
        );
        $this->object->parse('<stream:features></stream:features>');
        $this->object->parse('</stream:stream>');
        $this->assertSame(4, $triggered, 'Event where not triggered three times');
    }

    /**
     * Test setting and getting event manager.
     *
     * @covers Fabiang\Xmpp\Stream\XmlStream::getEventManager
     * @covers Fabiang\Xmpp\Stream\XmlStream::setEventManager
     * @return void
     */
    public function testSetAndGetEventManager()
    {
        $this->assertInstanceOf('\Fabiang\Xmpp\Event\EventManager', $this->object->getEventManager());
        $eventManager = new EventManager;
        $this->assertSame($eventManager, $this->object->setEventManager($eventManager)->getEventManager());
    }

}
