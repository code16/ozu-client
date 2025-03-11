<?php

namespace Code16\OzuClient\Support\Database;

use Code16\OzuClient\Client;
use Code16\OzuClient\Eloquent\IsOzuModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class OzuProductionSeeder extends Seeder
{
    protected Client $client;

    public function __construct(\Code16\OzuClient\Client $client)
    {
        $this->client = $client;
    }

    protected function createItemsFor(string $collection, Collection|Model $items)
    {
        if (! class_exists($collection)) {
            throw new \InvalidArgumentException("Class $collection does not exist");
        }

        if (! in_array(IsOzuModel::class, class_uses_recursive($collection))) {
            throw new \InvalidArgumentException("Class $collection doesn't have the IsOzuModel trait");
        }

        $payload = $items->toArray();

        $this->client->seed($collection, $payload);
    }
}
