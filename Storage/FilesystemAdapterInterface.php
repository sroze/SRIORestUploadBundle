<?php
namespace SRIO\RestUploadBundle\Storage;

use SRIO\RestUploadBundle\Exception\FileExistsException;
use SRIO\RestUploadBundle\Exception\FileNotFoundException;

interface FilesystemAdapterInterface
{
    /**
     * Returns the underlying filesystem
     *
     * @return mixed
     */
    public function getFilesystem();
        
    /**
     * Returns the adapter
     *
     * @return mixed
     */
    public function getAdapter();

    /**
     * Indicates whether the file matching the specified name exists
     *
     * @param string $path
     *
     * @return boolean TRUE if the file exists, FALSE otherwise
     */
    public function has($path);

    /**
     * Returns the file matching the specified name
     *
     * @param string $path Key of the file
     *
     * @return FileAdapterInterface
     */
    public function get($path);

    /**
     * Writes the given content into the new file
     *
     * @param string  $path Name of the file
     * @param string  $content Content to write in the file
     * @param boolean $config Any data or settings to pass down
     *
     * @throws FileExistsException
     * 
     * @return bool Whether the write succeeded or not
     */
    public function write($path, $content, array $config = array());

    /**
     * Writes the given content into the new file stream
     *
     * @param string    $path Name of the file
     * @param resource  $resource Stream to write in the file
     * @param boolean   $config Any data or settings to pass down
     *
     * @throws FileExistsException
     *
     * @return bool Whether the write succeeded or not
     */
    public function writeStream($path, $resource, array $config = array());

    /**
     * Writes the given content into the new file or replaces the old contents
     *
     * @param string  $path Name of the file
     * @param string  $content Content to write in the file
     * @param boolean $config Any data or settings to pass down
     *
     * @return bool Whether the write succeeded or not
     */
    public function put($path, $content, array $config = array());

    /**
     * Writes the given content into the new file or replaces the old contents
     *
     * @param string    $path Name of the file
     * @param resource  $resource Stream to write in the file
     * @param boolean   $config Any data or settings to pass down
     *
     * @return bool Whether the write succeeded or not
     */
    public function putStream($path, $resource, array $config = array());


    /**
     * Reads the content from the file
     *
     * @param string $path Path of the file
     *
     * @throws FileNotFoundException
     * 
     * @return string|false If the file could not be read, false is returned
     */
    public function read($path);
    
    /**
     * Reads the content from the file as a stream
     *
     * @param string $path Path of the file
     *
     * @throws FileNotFoundException
     *
     * @return resource|false If the file could not be read, false is returned
     */
    public function readStream($path);

    /**
     * Deletes the file matching the specified name
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     * 
     * @return boolean
     */
    public function delete($path);


    /**
     * Tries to do an efficient read and copy of the original filesystem stream
     * it overflows onto disk and always permits random reads, writes and seeks
     *
     * @param string $path Path of the file
     * 
     * @throws FileNotFoundException
     *
     * @return resource
     */
    public function getStreamCopy($path);

    /**
     * Returns the last modified time of the specified file
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return integer An UNIX like timestamp
     */
    public function getModifiedTimestamp($path);

    /**
     * Returns the size of the specified file's content
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return integer File size in Bytes
     */
    public function getSize($path);

    /**
     * Returns the mime type of the specified file
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return string
     */
    public function getMimeType($path);
}