<?php
namespace SRIO\RestUploadBundle\Upload;

use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Upload\Processor\AbstractUploadProcessor;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadManager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     */
    public function __construct (array $config)
    {
        $this->config = $config;
    }

    /**
     * Handle the upload request.
     *
     * @param $form
     * @param Request $request
     * @return Response
     */
    public function handleRequest (FormInterface $form, Request $request, array $extraConfig = array())
    {
        $processor = $this->createProcessor($form, $request, $extraConfig);

        return $processor->handleRequest($request);
    }

    /**
     * Create the upload processor.
     *
     * @param FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $extraConfig
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     * @return AbstractUploadProcessor
     */
    protected function createProcessor (FormInterface $form, Request $request, array $extraConfig)
    {
        $uploadType = $request->get($this->getParameterName('uploadType'));
        $config = array_merge($this->config, $extraConfig);

        $processors = $config['processors'];
        if (!array_key_exists($uploadType, $processors)) {
            throw new UploadProcessorException(sprintf(
                'Unknown upload processor for upload type %s',
                $uploadType
            ));
        }

        $className = $processors[$uploadType];
        $abstractClass = 'SRIO\RestUploadBundle\Upload\Processor\AbstractUploadProcessor';
        if (!is_subclass_of($className, $abstractClass)) {
            throw new UploadProcessorException(sprintf(
                'Processor %s must extends %s',
                $className,
                $abstractClass
            ));
        }

        return new $className($form, $config);
    }

    /**
     * Get a parameter name.
     *
     * @param $parameter
     * @return mixed
     */
    protected function getParameterName ($parameter)
    {
        return $this->config['parameters'][$parameter];
    }
}