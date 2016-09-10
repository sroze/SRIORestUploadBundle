<?php

namespace SRIO\RestUploadBundle\Upload;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Storage\FileStorage;
use SRIO\RestUploadBundle\Storage\FilesystemAdapterInterface;
use SRIO\RestUploadBundle\Storage\UploadedFile;
use SRIO\RestUploadBundle\Voter\StorageVoter;

/**
 * This class defines the storage handler.
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
    public function __construct(StorageVoter $voter)
    {
        $this->voter = $voter;
    }

    /**
     * Store a file's content.
     *
     * @param UploadContext $context
     * @param string        $content
     * @param array         $config
     * @param bool          $overwrite
     *
     * @return UploadedFile
     */
    public function store(UploadContext $context, $contents, array $config = array(), $overwrite = false)
    {
        return $this->getStorage($context)->store($context, $contents, $config, $overwrite);
    }

    /**
     * Store a file's content.
     *
     * @param UploadContext $context
     * @param resource      $resource
     * @param array         $config
     * @param bool          $overwrite
     *
     * @return UploadedFile
     */
    public function storeStream(UploadContext $context, $resource, array $config = array(), $overwrite = false)
    {
        return $this->getStorage($context)->storeStream($context, $resource, $config, $overwrite);
    }

    /**
     * @return FilesystemAdapterInterface
     */
    public function getFilesystem(UploadContext $context)
    {
        return $this->getStorage($context)->getFilesystem();
    }

    /**
     * Get storage by upload context.
     *
     * @param UploadContext $context
     *
     * @return FileStorage
     *
     * @throws \SRIO\RestUploadBundle\Exception\UploadException
     */
    public function getStorage(UploadContext $context)
    {
        $storage = $this->voter->getStorage($context);
        if (!$storage instanceof FileStorage) {
            throw new UploadException('Storage returned by voter isn\'t instanceof FileStorage');
        }

        return $storage;
    }
}
