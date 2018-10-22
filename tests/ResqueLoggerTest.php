<?php

namespace ResqueLogger;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Resque\Interfaces\Serializer;

class ResqueLoggerTest extends TestCase
{
    public function setUp()
    {
        $this->logger = $this->getLoggerMock();
        $this->listenerProvider = $this->getListenerProviderMock();
        $this->serializer = $this->getSerializerMock();

        $this->resqueLogger = new ResqueLogger($this->logger, $this->listenerProvider);
    }

    public function tearDown()
    {
        unset($this->resqueLogger);
        unset($this->listenerProvider);
        unset($this->logger);
    }

    public function testRegisterShouldRegisterLoggingHandlerForEveryResqueEvent()
    {
        $this->resqueLogger->setSerializer($this->serializer);

        $property = new \ReflectionProperty(ResqueLogger::class, 'taskToCommandMap');
        $property->setAccessible(true);
        $map = $property->getValue($this->resqueLogger);

        $i = 0;
        foreach (array_keys($map) as $taskClassName) {
            $payload = ['some' => 'payload'];
            $serializedPayload = 'some serialized payload';
            $targetLevel = 'error';

            $this->resqueLogger->setLogLevelForTask($taskClassName, $targetLevel);
            $this->logger->expects($this->at($i))
                ->method($targetLevel)
                ->with("{$taskClassName} - payload: {$serializedPayload}")
            ;
            $task = new $taskClassName();
            $task->setPayload($payload);

            $this->serializer->expects($this->at($i))
                ->method('serialize')
                ->with($payload)
                ->willReturn($serializedPayload)
            ;

            $this->listenerProvider->expects($this->at($i))
                ->method('addListener')
                ->with($this->callback(function ($listener) use ($task, $taskClassName) {
                    $listener($task);
                    return $this->assertFirstCallableParameterClass($taskClassName, $listener);
                }))
            ;

            ++$i;
        }

        $this->resqueLogger->register();
    }

    private function assertFirstCallableParameterClass(string $className, callable $listener)
    {
        $reflection = new \ReflectionFunction($listener);
        $eventClass = $reflection->getParameters()[0]->getType()->getName();
        $this->assertEquals($className, $eventClass);
        return true;
    }

    private function getListenerProviderMock()
    {
        return $this->getMockBuilder(ListenerProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addListener', 'getListenersForEvent'])
            ->getMock()
        ;
    }

    private function getSerializerMock()
    {
        return $this->getMockBuilder(Serializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock()
        ;
    }

    private function getLoggerMock()
    {
        return $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'emergency',
                'alert',
                'critical',
                'error',
                'warning',
                'notice',
                'info',
                'debug',
                'log',
            ])
            ->getMock()
        ;
    }
}
