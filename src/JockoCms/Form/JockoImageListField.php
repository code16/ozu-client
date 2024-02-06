<?php

namespace Code16\JockoClient\JockoCms\Form;

class JockoImageListField extends JockoFileListField
{
    private function buildUploadField(): SharpFormUploadField
    {
        return SharpFormUploadField::make('file')
            ->setFileFilterImages()
            ->setMaxFileSize(5)
            ->shouldOptimizeImage()
            ->setStorageDisk('local')
            ->setStorageBasePath(sprintf('data/%s/Posts/{id}', CurrentSharpWebsite::getKey()));
    }

    public function type(): string
    {
        return 'imageList';
    }
}
