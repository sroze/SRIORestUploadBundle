<?php
namespace SRIO\RestUploadBundle\Strategy;

use SRIO\RestUploadBundle\Upload\UploadContext;

interface NamingStrategy
{
    public function getName (UploadContext $context);
}
