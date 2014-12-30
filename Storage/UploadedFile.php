<?php
namespace SRIO\RestUploadBundle\Storage;

use Gaufrette\File;

class UploadedFile
{
    /**
     * @var FileStorage
     */
    protected $storage;

    /**
     * @var \Gaufrette\File
     */
    protected $file;

    /**
     * @param FileStorage $storage
     * @param File        $file
     */
    public function __construct(FileStorage $storage, File $file)
    {
        $this->storage = $storage;
        $this->file = $file;
    }

    /**
     * @return \Gaufrette\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return \SRIO\RestUploadBundle\Storage\FileStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
