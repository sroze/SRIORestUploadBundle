<?php
namespace SRIO\RestUploadBundle\Storage;

class UploadedFile
{
    /**
     * @var FileStorage
     */
    protected $storage;

    /**
     * @var FileAdapterInterface
     */
    protected $file;

    /**
     * @param FileStorage $storage
     * @param FileAdapterInterface $file
     */
    public function __construct(FileStorage $storage, FileAdapterInterface $file)
    {
        $this->storage = $storage;
        $this->file = $file;
    }

    /**
     * @return FileAdapterInterface
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
