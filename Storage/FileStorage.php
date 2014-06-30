<?php
namespace SRIO\RestUploadBundle\Storage;

use Gaufrette\File;
use Gaufrette\Filesystem;

use SRIO\RestUploadBundle\Strategy\NamingStrategy;
use SRIO\RestUploadBundle\Strategy\StorageStrategy;
use SRIO\RestUploadBundle\Upload\UploadContext;

class FileStorage
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Gaufrette\Filesystem
     */
    protected $filesystem;

    /**
     * @var \SRIO\RestUploadBundle\Strategy\StorageStrategy
     */
    protected $storageStrategy;

    /**
     * @var \Doctrine\ORM\Mapping\NamingStrategy
     */
    protected $namingStrategy;

    /**
     * Constructor.
     *
     * @param $name
     * @param Filesystem $filesystem
     * @param StorageStrategy $storageStrategy
     * @param NamingStrategy $namingStrategy
     */
    public function __construct ($name, Filesystem $filesystem, StorageStrategy $storageStrategy, NamingStrategy $namingStrategy)
    {
        $this->name = $name;
        $this->filesystem = $filesystem;
        $this->storageStrategy = $storageStrategy;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Store a file content.
     *
     * @param UploadContext $context
     * @param $content
     * @throws \RuntimeException
     * @return UploadedFile
     */
    public function store (UploadContext $context, $content)
    {
        $name = $this->namingStrategy->getName($context);
        $directory = $this->storageStrategy->getDirectory($context, $name);
        $path = $directory.'/'.$name;

        $this->filesystem->write($path, $content);

        $file = $this->filesystem->get($path);
        return new UploadedFile($this, $file);
    }

    /**
     * Get file size.
     *
     * @param $name
     * @return int
     */
    public function size ($name)
    {
        return $this->filesystem->size($name);
    }

    /**
     * Get file.
     *
     * @param $name
     * @return File
     */
    public function get ($name)
    {
        return $this->filesystem->get($name);
    }

    /**
     * Get a stream from file.
     *
     * @param $name
     * @return \Gaufrette\Stream|\Gaufrette\Stream\InMemoryBuffer
     */
    public function getStream ($name)
    {
        return $this->filesystem->createStream($name);
    }

    /**
     * @return \Gaufrette\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Doctrine\ORM\Mapping\NamingStrategy
     */
    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    /**
     * @return \SRIO\RestUploadBundle\Strategy\StorageStrategy
     */
    public function getStorageStrategy()
    {
        return $this->storageStrategy;
    }
} 