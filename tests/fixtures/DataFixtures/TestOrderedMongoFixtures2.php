<?php

declare(strict_types = 1);

namespace Facile\MongoDbBundle\Tests\fixtures\DataFixtures;

use Facile\MongoDbBundle\Capsule\Database;
use Facile\MongoDbBundle\Fixtures\AbstractContainerAwareFixture;
use Facile\MongoDbBundle\Fixtures\MongoFixtureInterface;
use Facile\MongoDbBundle\Fixtures\OrderedFixtureInterface;

class TestOrderedMongoFixtures2 extends AbstractContainerAwareFixture implements MongoFixtureInterface
{
    /**
     * @return array
     */
    public function loadData()
    {
        $doc = [
            'type' => 'fixture',
            'data' => 'Batman Begins - 2005',
        ];


        /** @var Database $connection */
        $connection = $this->getContainer()->get('mongo.connection.test_db');
        $collection = $connection->selectCollection($this->collection());
        $collection->insertOne($doc);
    }

    /**
     * @return array
     */
    public function loadIndexes()
    {
    }

    /**
     * @return string
     */
    public function collection(): string
    {
        return 'testFixturesOrderedCollection';
    }
}
