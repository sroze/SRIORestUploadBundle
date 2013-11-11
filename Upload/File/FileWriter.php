<?php
namespace SRIO\RestUploadBundle\Upload\File;

use SRIO\RestUploadBundle\Exception\InternalUploadProcessorException;

class FileWriter implements FileWriterInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var resource
     */
    protected $resource = null;

    /**
     * @var integer
     */
    protected $size = null;

    /**
     * @var integer
     */
    protected $cursor = null;

    /**
     * Constructor.
     *
     * @param $filePath
     */
    public function __construct ($filePath)
    {
        $this->path = $filePath;
    }

    /**
     * Seek pointer to that position.
     *
     * @param $position
     * @return integer
     */
    public function seek($position)
    {
        if (fseek($this->getResource(), $position) !== 0) {
            throw new InternalUploadProcessorException(sprintf(
                'Unable to seek on %d',
                $position
            ));
        }

        $this->cursor = $position;

        return $this->cursor;
    }

    /**
     * Write some content at this position.
     *
     * @param $content
     * @param $length
     * @return integer $bytes Wrote bytes
     */
    public function write($content, $length = null)
    {
        if ($length !== null) {
            $wrote = fwrite($this->getResource(), $content, $length);
        } else {
            $wrote = fwrite($this->getResource(), $content);
        }

        $this->size = $this->cursor + $wrote;
        $this->cursor += $wrote;

        return $wrote;
    }

    /**
     * Close file pointer.
     *
     * @return boolean
     */
    public function close()
    {
        if ($this->resource !== null) {
            return @fclose($this->resource);
        }

        return false;
    }

    /**
     * Get the file size.
     *
     * @return int
     */
    public function size ($clear = false)
    {
        if ($clear) {
            clearstatcache(true, $this->path);
            $this->size = filesize($this->path);
        }

        return $this->size;
    }

    /**
     * Unlink a file;
     *
     * @return bool
     */
    public function unlink()
    {
        $this->close();

        return unlink($this->path);
    }

    /**
     * Get resource.
     *
     * @return resource
     */
    protected function getResource ()
    {
        if ($this->resource === null) {
            $this->resource = fopen($this->path, 'c');
        }

        return $this->resource;
    }
}