<?php
namespace SRIO\RestUploadBundle\Storage;

use League\Flysystem\File;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use SRIO\RestUploadBundle\Exception\FileExistsException as WrappingFileExistsException;
use SRIO\RestUploadBundle\Exception\FileNotFoundException as WrappingFileNotFoundException;  

class FlysystemFilesystemAdapter implements FilesystemAdapterInterface
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @inheritdoc
     */
    public function getAdapter()
    {
        return $this->filesystem->getAdapter();
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        return $this->filesystem->has($path);
    }

    /**
     * @inheritdoc
     */
    public function get($path)
    {
        return new FlysystemFileAdapter(new File($this->filesystem, $path));
    }

    /**
     * @inheritdoc
     */
    public function write($path, $content, array $config = array())
    {
        try {
            return $this->filesystem->write($path, $content, $config);
        } catch (FileExistsException $ex) {
            $this->throwFileExistsException($path, $ex);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function writeStream($path, $resource, array $config = array())
    {
        try {
            return $this->filesystem->writeStream($path, $resource, $config);
        } catch (FileExistsException $ex) {
            $this->throwFileExistsException($path, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function put($path, $content, array $config = array())
    {
        return $this->filesystem->put($path, $content, $config);
    }

    /**
     * @inheritdoc
     */
    public function putStream($path, $resource, array $config = array())
    {
        return $this->filesystem->putStream($path, $resource, $config);
    }
    
    /**
     * @inheritdoc
     */
    public function read($path)
    {
        try {
            return $this->filesystem->read($path);
        } catch (FileNotFoundException $ex) {
            $this->throwFileNotFoundException($path, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function readStream($path)
    {
        try {
            return $this->filesystem->readStream($path);
        } catch (FileNotFoundException $ex) {
            $this->throwFileNotFoundException($path, $ex);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        try {
            return $this->filesystem->delete($path);
        } catch (FileNotFoundException $ex) {
            $this->throwFileNotFoundException($path, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function getStreamCopy($path)
    {
        $stream = $this->readStream($path);

        // Neatly overflow into a file on disk after more than 10MBs.
        $mbLimit = 10 * 1024 * 1024;
        $streamCopy = fopen("php://temp/maxmemory:$mbLimit", 'w+b');
        
        stream_copy_to_stream($stream, $streamCopy);
        rewind($streamCopy);
        
        return $streamCopy;
    }
    
    /**
     * @inheritdoc
     */
    public function getModifiedTimestamp($path)
    {
        if (false === $timestamp = $this->filesystem->getTimestamp($path)) {
            $this->throwFileNotFoundException($path);
        }

        return $timestamp;
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        if (false === $size = $this->filesystem->getSize($path)) {
            $this->throwFileNotFoundException($path);
        }
        
        return $size;
    }

    /**
     * @inheritdoc
     */
    public function getMimeType($path)
    {
        if (false === $mimeType = $this->filesystem->getMimetype($path)) {
            $this->throwFileNotFoundException($path);
        }
        
        return $mimeType;
    }
    
    protected function throwFileNotFoundException($path, $previousEx = null)
    {
        if ($previousEx === null) {
            $previousEx = new FileNotFoundException($path);
        }
        throw new WrappingFileNotFoundException($previousEx->getMessage(), $previousEx->getCode(), $previousEx);
    }

    protected function throwFileExistsException($path, $previousEx = null)
    {
        if ($previousEx === null) {
            $previousEx = new FileExistsException($path);
        }
        throw new WrappingFileExistsException($previousEx->getMessage(), $previousEx->getCode(), $previousEx);
    }
}