<?php

namespace SRIO\RestUploadBundle\Upload;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Processor\ProcessorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UploadHandler
{
    /**
     * @var string
     */
    protected $uploadTypeParameter;

    /**
     * @var array
     */
    protected $processors = array();

    /**
     * Constructor.
     *
     * @var string
     */
    public function __construct($uploadTypeParameter)
    {
        $this->uploadTypeParameter = $uploadTypeParameter;
    }

    /**
     * Add an upload processor.
     *
     * @param $uploadType
     * @param ProcessorInterface $processor
     *
     * @throws \LogicException
     */
    public function addProcessor($uploadType, ProcessorInterface $processor)
    {
        if (array_key_exists($uploadType, $this->processors)) {
            throw new \LogicException(sprintf(
                'A processor is already registered for type %s',
                $uploadType
            ));
        }

        $this->processors[$uploadType] = $processor;
    }

    /**
     * Handle the upload request.
     *
     * @param Request                               $request
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array                                 $config
     *
     * @throws \SRIO\RestUploadBundle\Exception\UploadException
     *
     * @return UploadResult
     */
    public function handleRequest(Request $request, FormInterface $form = null, array $config = array())
    {
        try {
            $processor = $this->getProcessor($request, $config);

            return $processor->handleUpload($request, $form, $config);
        } catch (UploadException $e) {
            if ($form != null) {
                $form->addError(new FormError($e->getMessage()));
            }

            $result = new UploadResult();
            $result->setException($e);
            $result->setForm($form);

            return $result;
        }
    }

    /**
     * Get the upload processor.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array                                     $config
     *
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     *
     * @return ProcessorInterface
     */
    protected function getProcessor(Request $request, array $config)
    {
        $uploadType = $request->get($this->getUploadTypeParameter($config));

        if (!array_key_exists($uploadType, $this->processors)) {
            throw new UploadProcessorException(sprintf(
                'Unknown upload processor for upload type %s',
                $uploadType
            ));
        }

        return $this->processors[$uploadType];
    }

    /**
     * Get the current upload type parameter.
     *
     * @param array $extraConfiguration
     *
     * @internal param $parameter
     * @internal param $config
     *
     * @return mixed
     */
    protected function getUploadTypeParameter(array $extraConfiguration)
    {
        return array_key_exists('uploadTypeParameter', $extraConfiguration)
            ? $extraConfiguration['uploadTypeParameter']
            : $this->uploadTypeParameter;
    }
}
