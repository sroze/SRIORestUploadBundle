<?php
namespace SRIO\RestUploadBundle\Upload\File;

interface FileWriterInterface
{
    /**
     * Seek pointer to that position.
     *
     * @param $position
     * @return mixed
     */
    public function seek($position);

    /**
     * Write some content at this position.
     *
     * @param $content
     * @param $length
     * @return mixed
     */
    public function write($content, $length);

    /**
     * Close file pointer.
     *
     * @return mixed
     */
    public function close();

    /**
     * Unlink file.
     *
     * @return boolean
     */
    public function unlink();

    /**
     * Get the file size.
     *
     * @return int
     */
    public function size();
} 