<?php

declare(strict_types=1);

namespace ResqueLogger;

use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Resque\Interfaces\PayloadableTask;
use Resque\Interfaces\Serializer;
use Resque\JsonSerializer;
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

class ResqueLogger
{
    private $logger;
    private $listenerProvider;
    private $serializer;

    private $taskToCommandMap = [
        AfterEnqueue::class => 'info',
        AfterUserJobPerform::class => 'notice',
        BeforeEnqueue::class => 'info',
        BeforeJobPop::class => 'debug',
        BeforeJobPush::class => 'debug',
        BeforeSignalsRegister::class => 'info',
        BeforeUserJobPerform::class => 'notice',
        FailedUserJobPerform::class => 'error',
        ForkFailed::class => 'error',
        JobFailed::class => 'error',
        ParentWaiting::class => 'debug',
        UnknownChildFailure::class => 'critical',
        WorkerDoneWorking::class => 'info',
        WorkerIdle::class => 'debug',
        WorkerRegistering::class => 'info',
        WorkerStartup::class => 'info',
        WorkerUnregistering::class => 'info',
    ];

    public function __construct(LoggerInterface $logger, ListenerProviderInterface $listenerProvider)
    {
        $this->logger = $logger;
        $this->listenerProvider = $listenerProvider;
        $this->serializer = new JsonSerializer();
    }

    public function setLogLevelForTask(string $taskClassName, string $logLevel): void
    {
        $this->taskToCommandMap[$taskClassName] = $logLevel;
    }

    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function register(): void
    {
        $provider = $this->listenerProvider;
        $provider->addListener(function (BeforeEnqueue $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (AfterEnqueue $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (BeforeJobPop $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (BeforeJobPush $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (BeforeSignalsRegister $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (BeforeUserJobPerform $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (AfterUserJobPerform $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (FailedUserJobPerform $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (ForkFailed $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (JobFailed $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (ParentWaiting $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (UnknownChildFailure $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (WorkerDoneWorking $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (WorkerIdle $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (WorkerRegistering $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (WorkerStartup $task) {
            $this->logTask($task);
        });
        $provider->addListener(function (WorkerUnregistering $task) {
            $this->logTask($task);
        });
    }

    private function logTask(PayloadableTask $task): void
    {
        $taskClassName = get_class($task);
        $command = $this->taskToCommandMap[$taskClassName];
        $message = $this->buildMessage($taskClassName, $task);
        $this->logger->$command($message);
    }

    private function buildMessage(string $taskClassName, PayloadableTask $task): string
    {
        return "{$taskClassName} - payload: {$this->serializer->serialize($task->getPayload())}";
    }
}
