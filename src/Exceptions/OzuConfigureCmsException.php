<?php

namespace Code16\OzuClient\Exceptions;

class OzuConfigureCmsException extends OzuClientException
{
    public static function unknownKeys(string $model, array $keys): self
    {
        return new static(sprintf(
            'The keys [%s] are defined either in the list or in the form but are not custom fields of the model [%s]',
            implode(', ', $keys),
            $model
        ));
    }
}
