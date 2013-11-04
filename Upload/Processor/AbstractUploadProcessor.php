<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractUploadProcessor
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
     * Constructor.
     *
     * @param FormInterface $form
     * @param array $config
     */
    public function __construct (FormInterface $form, array $config)
    {
        $this->form = $form;
        $this->config = $config;
    }

    /**
     * Handle an upload request.
     *
     * This method return a Response object that will be sent beck
     * to the client or will be caught by controller.
     *
     * @param Request $request
     * @return Response
     */
    abstract public function handleRequest (Request $request);
}