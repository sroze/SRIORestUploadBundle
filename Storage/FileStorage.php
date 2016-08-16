<?php
namespace SRIO\RestUploadBundle\Storage;

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
     * @var FilesystemAdapterInterface
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
     * @param                                 $name
     * @param FilesystemAdapterInterface      $filesystem
     * @param StorageStrategy                 $storageStrategy
     * @param NamingStrategy                  $namingStrategy
     */
    public function __construct($name, FilesystemAdapterInterface $filesystem, StorageStrategy $storageStrategy, NamingStrategy $namingStrategy)
    {
        $this->name = $name;
        $this->filesystem = $filesystem;
        $this->storageStrategy = $storageStrategy;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Store a file's content.
     *
     * @param  UploadContext $context
     * @param  string        $content
     * @param  array         $config
     * @param  bool          $overwrite
     * 
     * @return UploadedFile
     */
    public function store(UploadContext $context, $content, array $config = array(), $overwrite = false)
    {
        $path = $this->getFilePathFromContext($context);
        if ($overwrite === true) {
            $this->filesystem->put($path, $content, $config);    
        } else {
            $this->filesystem->write($path, $content, $config);
        }
        $file = $this->filesystem->get($path);

        return new UploadedFile($this, $file);
    }

    /**
     * Store a file's content.
     *
     * @param  UploadContext $context
     * @param  resource      $resource
     * @param  array         $config
     * @param  bool          $overwrite
     * 
     * @return UploadedFile
     */
    public function storeStream(UploadContext $context, $resource, array $config = array(), $overwrite = false)
    {
        $path = $this->getFilePathFromContext($context);
        if ($overwrite === true) {
            $this->filesystem->putStream($path, $resource, $config);    
        } else {
            $this->filesystem->writeStream($path, $resource, $config);
        }
        $file = $this->filesystem->get($path);

        return new UploadedFile($this, $file);
    }

    /**
     * Get or creates a file path from UploadContext
     *
     * @param  UploadContext $context
     * @return string
     */
    protected function getFilePathFromContext(UploadContext $context)
    {
        if($context->getFile() != null) {
            return $context->getFile()->getFile()->getName();
        }
        
        $name = $this->namingStrategy->getName($context);
        $directory = $this->storageStrategy->getDirectory($context, $name);
        $path = $directory.'/'.$name;
        
        return $path;
    }
    
    /**
     * @return FilesystemAdapterInterface
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
     * @return NamingStrategy
     */
    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    /**
     * @return StorageStrategy
     */
    public function getStorageStrategy()
    {
        return $this->storageStrategy;
    }
}
