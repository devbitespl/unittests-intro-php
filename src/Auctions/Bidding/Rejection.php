<?php

namespace DevBites\Auctions\Bidding;

class Rejection
{
    public function __construct(
        public readonly string $reason
    )
    {
    }
}