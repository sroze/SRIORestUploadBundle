<?php
namespace SRIO\RestUploadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This model represent a resumable upload session. It is used to store
 * a session ID and the related file path.
 *
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
}