<?php

namespace DevBites\Auctions;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class Event {
    private UuidInterface $id;

    public function __construct() {
        $this->id = Uuid::uuid4();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }
}