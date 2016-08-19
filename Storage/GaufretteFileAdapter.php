<?php

namespace SRIO\RestUploadBundle\Storage;

use Gaufrette\File;

class GaufretteFileAdapter implements FileAdapterInterface
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
        return $this->file->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getFile()
    {
        return $this->file;
    }
}
