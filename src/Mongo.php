<?php

namespace Andrey\StuffVersioned\Backend\Mongo;

use Andrey\PancakeObject\SimpleHydrator;
use Andrey\PancakeObject\SimpleSerializer;
use Andrey\StuffVersioned\BackendInterface;
use Andrey\StuffVersioned\VersionEntryInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Database;
use ReflectionException;

readonly class Mongo implements BackendInterface
{
    private const string CollectionName = '__php_seeder_versions';

    public function __construct(
        private Database $database,
    ) {
    }

    private function collection(): Collection
    {
        return $this->database->getCollection(self::CollectionName);
    }

    public function getCurrentVersionId(): ?string
    {
        $entries = $this->collection()->find([
            'successful' => true,
        ], [
            'sort' => [ 'created_at' => -1 ],
            'limit' => 1,
        ])->toArray();

        return $entries[0]['version_id'] ?? null;
    }

    /**
     * @throws ReflectionException
     *
     * @return VersionEntry
     */
    public function markVersionAsProcessing(string $versionId): VersionEntryInterface
    {
        $serializer = new SimpleSerializer();

        $entry = new VersionEntry(
            versionId: $versionId,
            successful: false,
            message: 'processing',
            createdAt: time(),
            updatedAt: time(),
        );

        $entry->id = new ObjectId();

        $data = $serializer->serialize($entry);
        $data['_id'] = $entry->id;

        $this->collection()->insertOne($data);
        return $entry;
    }

    /**
     * @param VersionEntry $version
     */
    public function markVersionAsSuccessful(VersionEntryInterface $version): void
    {
        $this->collection()->updateOne(
            filter: [ '_id' => $version->id ],
            update: [
                '$set' => [
                    'successful' => true,
                    'message' => 'success',
                    'updated_at' => time(),
                ],
            ],
        );
    }

    /**
     * @param VersionEntry $version
     */
    public function abortVersionProcessing(VersionEntryInterface $version, string $message): void
    {
        $this->collection()->updateOne(
            filter: [ '_id' => $version->id ],
            update: [
                '$set' => [
                    'successful' => false,
                    'message' => $message,
                    'updated_at' => time(),
                ],
            ],
        );
    }

    /**
     * @return VersionEntry[] a list containing the ID of every version successfully executed
     * @throws ReflectionException
     */
    public function getVersionList(): array
    {
        $hydrator = new SimpleHydrator();

        $entries = $this->collection()->find([
            'successful' => true,
        ], [
            'sort' => [ 'created_at' => 1 ],
        ])->toArray();

        $r = [];
        foreach ($entries as $entry) {
            /** @var VersionEntry $e */
            $e = $hydrator->hydrate((array)$entry, VersionEntry::class);
            $e->id = $entry['_id'];
            $r[] = $e;
        }

        return $r;
    }
}