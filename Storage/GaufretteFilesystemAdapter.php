<?php
namespace SRIO\RestUploadBundle\Storage;

use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Exception\FileAlreadyExists;
use Gaufrette\Exception\FileNotFound;
use Gaufrette\Filesystem;
use Gaufrette\StreamMode;
use SRIO\RestUploadBundle\Exception\FileExistsException as WrappingFileExistsException;
use SRIO\RestUploadBundle\Exception\FileNotFoundException as WrappingFileNotFoundException;

class GaufretteFilesystemAdapter implements FilesystemAdapterInterface
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
        return new GaufretteFileAdapter($this->filesystem->get($path));
    }

    /**
     * @inheritdoc
     */
    public function write($path, $content, array $config = array())
    {
        return $this->writeContents($path, $content, $config, false);
    }
    
    /**
     * @inheritdoc
     */
    public function writeStream($path, $resource, array $config = array())
    {
        // This is not ideal, stream_get_contents will read the full stream into memory before we can
        // flow it into the write function. Watch out with big files and Gaufrette!
        return $this->writeContents($path, stream_get_contents($resource, -1 ,0), $config, false);
    }

    /**
     * @inheritdoc
     */
    public function put($path, $content, array $config = array())
    {
        return $this->writeContents($path, $content, $config, true);
    }

    /**
     * @inheritdoc
     */
    public function putStream($path, $resource, array $config = array())
    {
        // This is not ideal, stream_get_contents will read the full stream into memory before we can
        // flow it into the write function. Watch out with big files and Gaufrette!
        return $this->writeContents($path, stream_get_contents($resource, -1 ,0), $config, true);
    }

    /**
     * General function for all writes.
     * 
     * @param       $path
     * @param       $content
     * @param array $config
     * @param bool  $overwrite
     *
     * @return bool
     */
    protected function writeContents($path, $content, array $config = array(), $overwrite = false)
    {
        if (!empty($config['metadata'])) {
            $adapter = $this->getAdapter();
            if ($adapter instanceof MetadataSupporter) {
                $allowed = empty($config['allowedMetadataKeys'])
                    ? array(FileStorage::METADATA_CONTENT_TYPE)
                    : array_merge($config['allowedMetadataKeys'], array(FileStorage::METADATA_CONTENT_TYPE));

                $adapter->setMetadata($path, $this->resolveMetadataMap($allowed, $config['metadata']));
            }
        }

        try {
            $this->filesystem->write($path, $content, $overwrite);
            return true;
        } catch (\RuntimeException $ex) {
            return false;
        }
    }
    
    /**
     * Resolve the metadata map.
     *
     * @param  array $allowedMetadataKeys
     * @param  array $metadataMap
     * @return array
     */
    protected function resolveMetadataMap(array $allowedMetadataKeys, array $metadataMap)
    {
        $map = array();

        foreach ($allowedMetadataKeys as $key) {
            if (array_key_exists($key, $metadataMap)) {
                $map[$key] = $metadataMap[$key];
            }
        }

        return $map;
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        try {
            $this->filesystem->read($path);
            return true;
        } catch (FileNotFound $ex) {
            throw $this->createFileNotFoundException($path, $ex);
        } catch (\RuntimeException $ex) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function readStream($path)
    {
        if (!$this->filesystem->has($path)) {
            throw $this->createFileNotFoundException($path);
        }

        // If castable to a real stream (local filesystem for instance) use that stream.
        $streamWrapper = $this->filesystem->createStream($path);
        $streamWrapper->open(new StreamMode('rb'));
        $stream = $streamWrapper->cast(0);

        if ($stream === false) {
            // This is not ideal, read will first read the full file into memory before we can
            // flow it into the temp stream. Watch out with big files and Gaufrette!
            $stream = fopen('php://temp','w+b');
            fwrite($stream, $this->read($path));
            rewind($stream);
        }
        
        return $stream;
    }

    /**
     * @inheritdoc
     */
    public function getStreamCopy($path)
    {
        if (!$this->filesystem->has($path)) {
            throw $this->createFileNotFoundException($path);
        }

        // If castable to a real stream (local filesystem for instance) use that stream.
        $streamWrapper = $this->filesystem->createStream($path);
        $streamWrapper->open(new StreamMode('rb'));
        $stream = $streamWrapper->cast(0);

        // Neatly overflow into a file on disk after more than 10MBs.
        $mbLimit = 10 * 1024 * 1024;
        $streamCopy = fopen("php://temp/maxmemory:$mbLimit", 'w+b');
        if ($stream === false) {
            // This is not ideal, read will first read the full file into memory before we can
            // flow it into the temp stream. Watch out with big files and Gaufrette!
            $data = $this->read($path);
            fwrite($streamCopy, $data);
        } else {
            stream_copy_to_stream($stream, $streamCopy);
        }

        rewind($streamCopy);
        return $streamCopy;
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        try {
            return $this->filesystem->delete($path);
        } catch (FileNotFound $ex) {
            throw $this->createFileNotFoundException($path, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function getModifiedTimestamp($path)
    {
        try {
            return $this->filesystem->mtime($path);
        } catch (FileNotFound $ex) {
            throw $this->createFileNotFoundException($path, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        try {
            return $this->filesystem->size($path);
        } catch (FileNotFound $ex) {
            throw $this->createFileNotFoundException($path, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMimeType($path)
    {
        try {
            return $this->filesystem->mimeType($path);
        } catch (FileNotFound $ex) {
            throw $this->createFileNotFoundException($path, $ex);
        }
    }

    protected function createFileNotFoundException($path, $previousEx = null)
    {
        if ($previousEx === null) {
            $previousEx = new FileNotFound($path);
        }

        return new WrappingFileNotFoundException($previousEx->getMessage(), $previousEx->getCode(), $previousEx);
    }

    protected function createFileExistsException($path, $previousEx = null)
    {
        if ($previousEx === null) {
            $previousEx = new FileAlreadyExists($path);
        }

        return new WrappingFileExistsException($previousEx->getMessage(), $previousEx->getCode(), $previousEx);
    }
}
