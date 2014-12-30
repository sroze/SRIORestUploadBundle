<?php
namespace SRIO\RestUploadBundle\Storage;

use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\File;
use Gaufrette\Filesystem;

use SRIO\RestUploadBundle\Strategy\NamingStrategy;
use SRIO\RestUploadBundle\Strategy\StorageStrategy;
use SRIO\RestUploadBundle\Upload\UploadContext;

class FileStorage
{
    const METADATA_CONTENT_TYPE = 'contentType';

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
     * @param Filesystem      $filesystem
     * @param StorageStrategy $storageStrategy
     * @param NamingStrategy  $namingStrategy
     */
    public function __construct($name, Filesystem $filesystem, StorageStrategy $storageStrategy, NamingStrategy $namingStrategy)
    {
        $this->name = $name;
        $this->filesystem = $filesystem;
        $this->storageStrategy = $storageStrategy;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Store a file content.
     *
     * @param  UploadContext $context
     * @param $content
     * @param  array         $metadataMap
     * @return UploadedFile
     */
    public function store (UploadContext $context, $content, array $metadataMap = array())
    {
        $name = $this->namingStrategy->getName($context);
        $directory = $this->storageStrategy->getDirectory($context, $name);
        $path = $directory.'/'.$name;

        $adapter = $this->filesystem->getAdapter();
        if ($adapter instanceof MetadataSupporter) {
            $adapter->setMetadata($path, $this->resolveMetadataMap($context, $metadataMap));
        }
        $this->filesystem->write($path, $content);

        $file = $this->filesystem->get($path);

        return new UploadedFile($this, $file);
    }

    /**
     * Resolve the metadata map.
     *
     * @param  UploadContext $context
     * @param  array         $metadataMap
     * @return array
     */
    protected function resolveMetadataMap(UploadContext $context, array $metadataMap)
    {
        $allowedMetadataKeys = array(self::METADATA_CONTENT_TYPE);
        $map = array();

        foreach ($allowedMetadataKeys as $key) {
            if (array_key_exists($key, $metadataMap)) {
                $map[$key] = $metadataMap[$key];
            }
        }

        return $map;
    }

    /**
     * Get file size.
     *
     * @param $name
     * @return int
     */
    public function size($name)
    {
        return $this->filesystem->size($name);
    }

    /**
     * Get file.
     *
     * @param $name
     * @return File
     */
    public function get($name)
    {
        return $this->filesystem->get($name);
    }

    /**
     * Get a stream from file.
     *
     * @param $name
     * @return \Gaufrette\Stream|\Gaufrette\Stream\InMemoryBuffer
     */
    public function getStream($name)
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
