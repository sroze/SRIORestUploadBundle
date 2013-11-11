<?php
namespace SRIO\RestUploadBundle\Upload\File;

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
        return fseek($this->getResource(), $position);
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
            return fwrite($this->getResource(), $content, $length);
        } else {
            return fwrite($this->getResource(), $content);
        }
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
    public function size ()
    {
        return filesize($this->path);
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
            $this->resource = fopen($this->path, 'a');
        }

        return $this->resource;
    }
}