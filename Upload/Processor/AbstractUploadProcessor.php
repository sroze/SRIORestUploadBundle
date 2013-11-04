<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\InternalUploadProcessorException;
use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Model\UploadableFileInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     */
    abstract public function handleRequest (Request $request);

    /**
     * Create the form data that the form will be able to handle.
     *
     * It walk one the form and make an intersection between its keys and
     * provided data.
     *
     * @param array $data
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
     * Set the uploaded file on the form data.
     *
     * @param UploadedFile $file
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
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

    /**
     * Open a file.
     *
     */
    protected function openFile ()
    {
        $filePath = $this->config['upload_dir'].'/'.uniqid();
        $resource = fopen($filePath, 'a');
        if ($resource === false) {
            throw new InternalUploadProcessorException('Unable to open file');
        }

        return array(
            'resource' => $resource,
            'path' => $filePath
        );
    }

    /**
     * Close a file.
     *
     * @param $file
     * @return bool
     */
    protected function closeFile ($file)
    {
        return @fclose($file['resource']);
    }

    /**
     * Unlink a file.
     *
     * @param $file
     * @return bool
     */
    protected function unlinkFile ($file)
    {
        return @unlink($file['path']);
    }

    /**
     * Write some content on the resource starting at position, for a specified
     * length.
     *
     * @param $resource
     * @param $position
     * @param $length
     * @param $content
     * @throws \SRIO\RestUploadBundle\Exception\InternalUploadProcessorException
     */
    protected function writeFile ($file, $position, $length, $content)
    {
        $resource = $file['resource'];
        if (@fseek($resource, $position) != 0) {
            throw new InternalUploadProcessorException('Unable to seek to specified file position');
        }

        if (@fwrite($resource, $content, $length) === false) {
            throw new InternalUploadProcessorException('Unable to write content to file');
        }
    }
}