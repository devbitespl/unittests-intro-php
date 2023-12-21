<?php

namespace DevBites\Auctions;

interface EventPublisher
{
    public function publish(Event $event): void;
}