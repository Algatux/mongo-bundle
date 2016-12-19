<?php

declare(strict_types=1);

namespace Facile\MongoDbBundle\Services\Loggers;

use Facile\MongoDbBundle\Models\QueryLog;

/**
 * Class MongoLogger
 */
class MongoLogger implements DataCollectorLoggerInterface
{
    /** @var \SplQueue|QueryLog[] */
    private $logs;

    /** @var array|string[] */
    private $connections;

    /**
     * MongoLogger constructor.
     */
    public function __construct()
    {
        $this->logs = new \SplQueue();
        $this->connections = [];
    }

    /**
     * @param string $connection
     */
    public function addConnection(string $connection)
    {
        $this->connections[] = $connection;
    }

    /**
     * @return array|\string[]
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * @param QueryLog $event
     */
    public function logQuery(QueryLog $event)
    {
        $this->logs->enqueue($event);
    }

    /**
     * @return bool
     */
    public function hasLoggedEvents(): bool
    {
        return !$this->logs->isEmpty();
    }

    /**
     * @return QueryLog
     */
    public function getLoggedEvent(): QueryLog
    {
        if (!$this->hasLoggedEvents()) {
            throw new \LogicException('No more events logged!');
        }

        return $this->logs->dequeue();
    }
}
