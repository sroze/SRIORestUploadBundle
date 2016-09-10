<?php

namespace SRIO\RestUploadBundle\Storage;

interface FileAdapterInterface
{
    /**
     * Get the file size.
     *
     * @return int file size
     */
    public function getSize();

    /**
     * Check whether the file exists.
     *
     * @return bool
     */
    public function exists();

    /**
     * Retrieve the file path.
     *
     * @return string path
     */
    public function getName();

    /**
     * Returns the underlying file instance for processing by specialized code.
     *
     * @return mixed
     */
    public function getFile();
}
