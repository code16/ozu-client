<?php

namespace Code16\OzuClient\Support\Api;

enum OzuApiEndpoints: string
{
    case GeneralCollectionSettings = '/collections/{collectionKey}/configure/general';
    case CollectionListConfiguration = '/collections/{collectionKey}/configure/list';
    case CollectionFormConfiguration = '/collections/{collectionKey}/configure/form';
    case CollectionCustomFieldsConfiguration = '/collections/{collectionKey}/configure/custom-fields';
    case CollectionAutoDeployFields = '/collections/{collectionKey}/configure/auto-deploy';
    case SeedOzuCollection = '/collections/{collectionKey}/seed';
    case SeedFileOzuCollection = '/collections/{collectionKey}/seed/{postId}/file';

    public function endpoint(string $collectionKey, ?int $postId = null): string
    {
        return str($this->value)->replace([
            '{collectionKey}',
            '{postId}',
        ], [
            $collectionKey,
            $postId,
        ]);
    }
}
