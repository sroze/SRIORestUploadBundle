<?php
namespace SRIO\RestUploadBundle\Upload;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Storage\FileStorage;
use SRIO\RestUploadBundle\Storage\UploadedFile;
use SRIO\RestUploadBundle\Voter\StorageVoter;

/**
 * This class defines the storage handler.
 *
 */
class StorageHandler
{
    /**
     * @var \SRIO\RestUploadBundle\Voter\StorageVoter
     */
    protected $voter;

    /**
     * Constructor.
     *
     * @param StorageVoter $voter
     */
    public function __construct (StorageVoter $voter)
    {
        $this->voter = $voter;
    }

    /**
     * @param UploadContext $context
     * @param $contents
     * @throws \SRIO\RestUploadBundle\Exception\UploadException
     * @return UploadedFile
     */
    public function store (UploadContext $context, $contents)
    {
        return $this->getStorage($context)->store($context, $contents);
    }

    /**
     * Get file size.
     *
     * @param UploadContext $context
     * @param $name
     * @return int
     */
    public function size (UploadContext $context, $name)
    {
        return $this->getStorage($context)->size($name);
    }

    /**
     * Get file.
     *
     * @param UploadContext $context
     * @param $name
     * @return \Gaufrette\File
     */
    public function get (UploadContext $context, $name)
    {
        return $this->getStorage($context)->get($name);
    }

    /**
     * Get a stream for that file.
     *
     * @param UploadContext $context
     * @param $name
     * @return \Gaufrette\Stream|\Gaufrette\Stream\InMemoryBuffer
     */
    public function getStream (UploadContext $context, $name)
    {
        return $this->getStorage($context)->getStream($name);
    }

    /**
     * Get storage by upload context.
     *
     * @param UploadContext $context
     * @return FileStorage
     * @throws \SRIO\RestUploadBundle\Exception\UploadException
     */
    public function getStorage (UploadContext $context)
    {
        $storage = $this->voter->getStorage($context);
        if (!$storage instanceof FileStorage) {
            throw new UploadException('Storage returned by voter isn\'t instanceof FileStorage');
        }

        return $storage;
    }
} 