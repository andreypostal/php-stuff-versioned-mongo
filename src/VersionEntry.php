<?php

namespace Andrey\StuffVersioned\Backend\Mongo;

use Andrey\PancakeObject\Attributes\SkipItem;
use Andrey\PancakeObject\Attributes\ValueObject;
use Andrey\StuffVersioned\VersionEntryInterface;
use MongoDB\BSON\ObjectId;

#[ValueObject]
class VersionEntry implements VersionEntryInterface
{
    #[SkipItem]
    public ObjectId $id;

    public function __construct(
        public string $versionId {
            get {
                return $this->versionId;
            }
        },
        public bool $successful,
        public string $message,
        public int $createdAt,
        public int $updatedAt,
    ) {
    }
}