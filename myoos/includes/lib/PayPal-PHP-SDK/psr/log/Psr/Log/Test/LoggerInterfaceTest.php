<?php

namespace Psr\Log\Test;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Provides a base test class for ensuring compliance with the LoggerInterface.
 *
 * Implementors can extend the class and implement abstract methods to run this
 * as part of their test suite.
 */
abstract class LoggerInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return LoggerInterface
     */
    abstract public function getLogger();

    /**
     * This must return the log messages in order.
     *
     * The simple formatting of the messages is: "<LOG LEVEL> <MESSAGE>".
     *
     * Example ->error('Foo') would yield "error Foo".
     *
     * @return string[]
     */
    abstract public function getLogs();

    public function testImplements()
    {
        $this->assertInstanceOf(\Psr\Log\LoggerInterface::class, $this->getLogger());
    }

    /**
     * @dataProvider provideLevelsAndMessages
     */
    public function testLogsAtAllLevels($level, $message)
    {
        $logger = $this->getLogger();
        $logger->{$level}($message, ['user' => 'Bob']);
        $logger->log($level, $message, ['user' => 'Bob']);

        $expected = [$level.' message of level '.$level.' with context: Bob', $level.' message of level '.$level.' with context: Bob'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function provideLevelsAndMessages()
    {
        return [LogLevel::EMERGENCY => [LogLevel::EMERGENCY, 'message of level emergency with context: {user}'], LogLevel::ALERT => [LogLevel::ALERT, 'message of level alert with context: {user}'], LogLevel::CRITICAL => [LogLevel::CRITICAL, 'message of level critical with context: {user}'], LogLevel::ERROR => [LogLevel::ERROR, 'message of level error with context: {user}'], LogLevel::WARNING => [LogLevel::WARNING, 'message of level warning with context: {user}'], LogLevel::NOTICE => [LogLevel::NOTICE, 'message of level notice with context: {user}'], LogLevel::INFO => [LogLevel::INFO, 'message of level info with context: {user}'], LogLevel::DEBUG => [LogLevel::DEBUG, 'message of level debug with context: {user}']];
    }

    /**
     * @expectedException \Psr\Log\InvalidArgumentException
     */
    public function testThrowsOnInvalidLevel()
    {
        $logger = $this->getLogger();
        $logger->log('invalid level', 'Foo');
    }

    public function testContextReplacement()
    {
        $logger = $this->getLogger();
        $logger->info('{Message {nothing} {user} {foo.bar} a}', ['user' => 'Bob', 'foo.bar' => 'Bar']);

        $expected = ['info {Message {nothing} Bob Bar a}'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testObjectCastToString()
    {
        if (method_exists($this, 'createPartialMock')) {
            $dummy = $this->createPartialMock(\Psr\Log\Test\DummyTest::class, ['__toString']);
        } else {
            $dummy = $this->getMock(\Psr\Log\Test\DummyTest::class, ['__toString']);
        }
        $dummy->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('DUMMY'));

        $this->getLogger()->warning($dummy);

        $expected = ['warning DUMMY'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testContextCanContainAnything()
    {
        $closed = fopen('php://memory', 'r');
        fclose($closed);

        $context = ['bool' => true, 'null' => null, 'string' => 'Foo', 'int' => 0, 'float' => 0.5, 'nested' => ['with object' => new DummyTest()], 'object' => new \DateTime(), 'resource' => fopen('php://memory', 'r'), 'closed' => $closed];

        $this->getLogger()->warning('Crazy context data', $context);

        $expected = ['warning Crazy context data'];
        $this->assertEquals($expected, $this->getLogs());
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues()
    {
        $logger = $this->getLogger();
        $logger->warning('Random message', ['exception' => 'oops']);
        $logger->critical('Uncaught Exception!', ['exception' => new \LogicException('Fail')]);

        $expected = ['warning Random message', 'critical Uncaught Exception!'];
        $this->assertEquals($expected, $this->getLogs());
    }
}

class DummyTest implements \Stringable
{
    public function __toString(): string
    {
        return '';
    }
}
