<?php

namespace Code16\OzuClient\Tests\Fixtures;

use Code16\OzuClient\Eloquent\IsOzuModel;
use Code16\OzuClient\OzuCms\OzuCollectionConfig;
use Code16\OzuClient\OzuCms\OzuCollectionFormConfig;
use Code16\OzuClient\OzuCms\OzuCollectionListConfig;
use Illuminate\Database\Eloquent\Model;

class DummySubcollectionTestModel extends Model
{
    protected $table = 'dummy_test_models';

    use IsOzuModel;

    protected $guarded = [];

    public function ozuCollectionKey(): string
    {
        return 'dummy-subcollection';
    }

    public static function configureOzuCollection(OzuCollectionConfig $config): OzuCollectionConfig
    {
        return $config;
    }

    public static function configureOzuCollectionList(OzuCollectionListConfig $config): OzuCollectionListConfig
    {
        return $config;
    }

    public static function configureOzuCollectionForm(OzuCollectionFormConfig $config): OzuCollectionFormConfig
    {
        return $config;
    }
}
