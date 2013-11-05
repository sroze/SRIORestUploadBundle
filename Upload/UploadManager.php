<?php
namespace SRIO\RestUploadBundle\Upload;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Upload\Processor\AbstractUploadProcessor;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        try {
            $processor = $this->createProcessor($form, $request, $extraConfig);
            if ($processor->handleRequest($request)) {
                return $this->returnSuccessResponse($form);
            } else {
                return $this->returnFormErrors($form);
            }
        } catch (UploadException $e) {
            $form->addError(new FormError($e->getMessage()));

            return $this->returnFormErrors($form);
        }
    }

    /**
     * Return a 200 successful JSON response.
     *
     * @return JsonResponse
     */
    protected function returnSuccessResponse (FormInterface $form)
    {
        return new JsonResponse($form->getData());
    }

    /**
     * If form isn't valid, call this method to return a response
     * with 400 bad request status code and form errors.
     *
     * @return Response
     */
    protected function returnFormErrors (FormInterface $form)
    {
        return new JsonResponse($this->computeFormErrors($form), 400);
    }

    /**
     * Compute the form errors as an array.
     *
     * @param FormInterface $form
     * @return array
     */
    protected function computeFormErrors (FormInterface $form)
    {
        $list = array();
        $errors = $form->getErrors();

        if (count($errors) > 0) {
            $list['errors'] = array();
            foreach ($errors as $error) {
                /** @var $error FormError */
                $list['errors'][] = $error->getMessage();
            }
        }

        $children = $form->all();
        if (count($children) > 0) {
            $childrenErrors = array();
            foreach ($children as $child) {
                /** @var $child FormInterface */
                $errors = $this->computeFormErrors($child);
                if (!empty($errors)) {
                    $childrenErrors[$child->getName()] = $errors;
                }
            }

            if (!empty($childrenErrors)) {
                $list['children'] = $childrenErrors;
            }
        }

        return $list;
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