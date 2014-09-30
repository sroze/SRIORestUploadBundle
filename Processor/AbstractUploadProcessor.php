<?php
namespace SRIO\RestUploadBundle\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Model\UploadableFileInterface;
use SRIO\RestUploadBundle\Request\RequestContentHandler;
use SRIO\RestUploadBundle\Request\RequestContentHandlerInterface;

use SRIO\RestUploadBundle\Upload\StorageHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractUploadProcessor implements ProcessorInterface
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var RequestContentHandler
     */
    protected $contentHandler = null;

    /**
     * @var \SRIO\RestUploadBundle\Upload\StorageHandler
     */
    protected $storageHandler;

    /**
     * Constructor.
     *
     * @param StorageHandler $storageHandler
     */
    public function __construct (StorageHandler $storageHandler)
    {
        $this->storageHandler = $storageHandler;
    }

    /**
     * Constructor.
     *
     * @param Request $request
     * @param FormInterface $form
     * @param array $config
     * @return boolean
     */
    public function handleUpload (Request $request, FormInterface $form = null, array $config = array())
    {
        $this->form = $form;
        $this->config = $config;

        return $this->handleRequest($request);
    }

    /**
     * Handle an upload request.
     *
     * This method return a Response object that will be sent back
     * to the client or will be caught by controller.
     *
     * @param Request $request
     * @return \SRIO\RestUploadBundle\Upload\UploadResult
     */
    abstract public function handleRequest (Request $request);

    /**
     * Create the form data that the form will be able to handle.
     *
     * It walk one the form and make an intersection between its keys and
     * provided data.
     *
     * @param array $data
     * @return array
     */
    protected function createFormData (array $data)
    {
        $keys = $this->getFormKeys($this->form);
        return array_intersect_key($data, $keys);
    }

    /**
     * Get keys of the form.
     *
     * @param FormInterface $form
     * @return array
     */
    protected function getFormKeys (FormInterface $form)
    {
        $keys = array();
        foreach ($form->all() as $child) {
            $keys[$child->getName()] = count($child->all() > 0) ? $this->getFormKeys($child) : null;
        }
        return $keys;
    }

    /**
     * Get a request content handler.
     *
     * @param Request $request
     * @return RequestContentHandlerInterface
     */
    protected function getRequestContentHandler (Request $request)
    {
        if ($this->contentHandler === null) {
            $this->contentHandler = new RequestContentHandler($request);
        }

        return $this->contentHandler;
    }

	/**
	 * Check that needed headers are here.
	 *
	 * @param Request $request the request
	 * @param array $headers the headers to check
	 * @throws \SRIO\RestUploadBundle\Exception\UploadException
	 */
    protected function checkHeaders (Request $request, array $headers)
    {
        foreach ($headers as $header) {
            $value = $request->headers->get($header, null);
            if ($value === null) {
                throw new UploadException(sprintf('%s header is needed', $header));
            } else if (!is_int($value) && empty($value)) {
                throw new UploadException(sprintf('%s header must not be empty', $header));
            }
        }
    }

    /**
     * Set the uploaded file on the form data.
     *
     * @param UploadedFile $file
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     * @deprecated
     */
    protected function setUploadedFile (UploadedFile $file)
    {
        $data = $this->form->getData();
        if ($data instanceof UploadableFileInterface) {
            $data->setFile($file);
        } else {
            throw new UploadProcessorException(sprintf(
                'Unable to set file, %s do not implements %s',
                get_class($data),
                'SRIO\RestUploadBundle\Model\UploadableFileInterface'
            ));
        }
    }
}