<?php

namespace SRIO\RestUploadBundle\Storage;

use League\Flysystem\File;

class FlysystemFileAdapter implements FileAdapterInterface
{
    /**
     * @var File
     */
    protected $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function exists()
    {
        return $this->file->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->file->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->file->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->file;
    }
}
