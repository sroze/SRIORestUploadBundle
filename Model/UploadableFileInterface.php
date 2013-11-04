<?php
namespace SRIO\RestUploadBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * A file object that will be uploaded with RestUploadBundle must implements
 * this interface.
 *
 */
interface UploadableFileInterface
{
    /**
     * Set the uploaded file instance.
     *
     * @param UploadedFile $file
     */
    public function setFile (UploadedFile $file);
}