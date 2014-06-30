<?php
namespace SRIO\RestUploadBundle\Upload;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UploadContext
{
    /**
     * @var \SRIO\RestUploadBundle\Storage\UploadedFile
     */
    protected $file;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $storageName;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $config
     */
    public function __construct (Request $request = null, FormInterface $form = null, array $config = array())
    {
        $this->request = $request;
        $this->form = $form;
        $this->config = $config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \SRIO\RestUploadBundle\Storage\UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return \SRIO\RestUploadBundle\Storage\UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
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