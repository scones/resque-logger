<?php

namespace ResqueLogger;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Resque\Tasks\AfterEnqueue;
use Resque\Tasks\AfterUserJobPerform;
use Resque\Tasks\BeforeEnqueue;
use Resque\Tasks\BeforeJobPop;
use Resque\Tasks\BeforeJobPush;
use Resque\Tasks\BeforeSignalsRegister;
use Resque\Tasks\BeforeUserJobPerform;
use Resque\Tasks\FailedUserJobPerform;
use Resque\Tasks\ForkFailed;
use Resque\Tasks\JobFailed;
use Resque\Tasks\ParentWaiting;
use Resque\Tasks\UnknownChildFailure;
use Resque\Tasks\WorkerDoneWorking;
use Resque\Tasks\WorkerIdle;
use Resque\Tasks\WorkerRegistering;
use Resque\Tasks\WorkerStartup;
use Resque\Tasks\WorkerUnregistering;

class ResqueLoggerTest extends TestCase
{
    public function setUp()
    {
        $this->logger = $this->getLoggerMock();
        $this->listenerProvider = $this->getListenerProviderMock();

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
        $this->listenerProvider->expects($this->exactly(17))
            ->method('addListener')
            ->withConsecutive(
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(AfterEnqueue::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(AfterUserJobPerform::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(BeforeEnqueue::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(BeforeJobPop::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(BeforeJobPush::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(BeforeSignalsRegister::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(BeforeUserJobPerform::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(FailedUserJobPerform::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(ForkFailed::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(JobFailed::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(ParentWaiting::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(UnknownChildFailure::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(WorkerDoneWorking::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(WorkerIdle::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(WorkerRegistering::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(WorkerStartup::class, $listener);
                }),
                $this->callback(function ($listener) {
                    return $this->assertFirstCallableParameterClass(WorkerUnregistering::class, $listener);
                })
            )
        ;

        $this->resqueLogger->register();
    }

    public function testlogTaskShouldLogUsingTheProvidedLoggerAndCorrectMapping()
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with(WorkerIdle::class . ' - payload: {"some":"payload"}')
        ;
        $task = new WorkerIdle();
        $task->setPayload(['some' => 'payload']);

        $this->resqueLogger->setLogLevelForTask(WorkerIdle::class, 'info');

        $reflection = new \ReflectionMethod(ResqueLogger::class, 'logTask');
        $reflection->setAccessible(true);
        $reflection->invoke($this->resqueLogger, $task);
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
