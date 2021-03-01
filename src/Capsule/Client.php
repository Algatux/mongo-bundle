<?php

declare(strict_types=1);

namespace Facile\MongoDbBundle\Capsule;

use MongoDB\Client as MongoClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class Client extends MongoClient
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var string */
    private $clientName;

    /**
     * @param string $clientName
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $uri
     * @param array $uriOptions
     * @param array $driverOptions
     */
    public function __construct(
        string $clientName,
        EventDispatcherInterface $eventDispatcher,
        $uri = 'mongodb://localhost:27017',
        array $uriOptions = [],
        array $driverOptions = []
    ) {
        parent::__construct($uri, $uriOptions, $driverOptions);
        $this->eventDispatcher = $eventDispatcher;
        $this->clientName = $clientName;
    }

    /**
     * {@inheritdoc}
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        $debug = $this->__debugInfo();
        $options += [
            'typeMap' => $debug['typeMap'],
        ];

        return new Database($debug['manager'], $this->eventDispatcher, $this->clientName, $databaseName, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        $debug = $this->__debugInfo();
        $options += [
            'typeMap' => $debug['typeMap'],
        ];

        return new Collection(
            $debug['manager'],
            $this->eventDispatcher,
            $this->clientName,
            $databaseName,
            $collectionName,
            $options
        );
    }
}
