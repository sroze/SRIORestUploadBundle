<?php
namespace SRIO\RestUploadBundle\Upload;

class UploadResult extends UploadContext
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @var \SRIO\RestUploadBundle\Exception\UploadException
     */
    protected $exception;

    /**
     * @param \SRIO\RestUploadBundle\Exception\UploadException $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return \SRIO\RestUploadBundle\Exception\UploadException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
