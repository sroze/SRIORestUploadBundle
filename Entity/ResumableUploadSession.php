<?php

namespace SRIO\RestUploadBundle\Entity;

/**
 * This model represent a resumable upload session. It is used to store
 * a session ID and the related file path.
 */
class ResumableUploadSession
{
    /**
     * The session ID.
     *
     * @var string
     */
    protected $sessionId;

    /**
     * The destination file path.
     *
     * @var string
     */
    protected $filePath;

    /**
     * Name of storage used.
     *
     * @var string
     */
    protected $storageName;

    /**
     * The form data.
     *
     * @var string
     */
    protected $data;

    /**
     * Content type.
     *
     * @var string
     */
    protected $contentType;

    /**
     * Content length.
     *
     * @var int
     */
    protected $contentLength;

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param int $contentLength
     */
    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;
    }

    /**
     * @return int
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $storageName
     */
    public function setStorageName($storageName)
    {
        $this->storageName = $storageName;
    }

    /**
     * @return string
     */
    public function getStorageName()
    {
        return $this->storageName;
    }
}
