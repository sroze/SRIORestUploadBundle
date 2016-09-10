<?php

namespace SRIO\RestUploadBundle\Strategy;

use SRIO\RestUploadBundle\Upload\UploadContext;

class DefaultStorageStrategy implements StorageStrategy
{
    public function getDirectory(UploadContext $context, $fileName)
    {
        return '';
    }
}
