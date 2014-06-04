<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadException;
use SRIO\RestUploadBundle\Upload\File\FileWriter;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FormDataUploadProcessor extends SimpleUploadProcessor
{
    const KEY_FIELD_FILE = 'key_file';
    const KEY_FIELD_FORM = 'key_form';
    /**
     * {@inheritDoc}
     */
    public function handleUpload (Request $request, FormInterface $form, array $config)
    {
        $config = array_merge(array(
            self::KEY_FIELD_FILE => 'file',
            self::KEY_FIELD_FORM => 'form'
        ), $config);

        return parent::handleUpload($request, $form, $config);
    }

    /**
     * @param Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     */
    public function handleRequest (Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request, array('Content-Length', 'Content-Type'));

        if (!$request->request->has($this->config[self::KEY_FIELD_FORM])) {
            throw new UploadException(sprintf(
                '%s request field not found in (%s)',
                $this->config[self::KEY_FIELD_FORM],
                implode(', ', $request->request->keys())
            ));
        }
        if (!$request->files->has($this->config[self::KEY_FIELD_FILE])) {
            throw new UploadException(sprintf('%s file not found', $this->config[self::KEY_FIELD_FILE]));
        }

        $submittedValue = $request->request->get($this->config[self::KEY_FIELD_FORM]);
        if (is_string($submittedValue)) {
            $submittedValue = json_decode($submittedValue, true);
            if (!$submittedValue) {
                throw new UploadException('Unable to decode JSON');
            }
        } else if (!is_array($submittedValue)) {
            throw new UploadException('Unable to parse form data');
        }

        // Submit form data
        $formData = $this->createFormData($submittedValue);
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return false;
        }

        /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $request->files->get($this->config[self::KEY_FIELD_FILE]);

        $filePath = $this->createFilePath();
        $writer = new FileWriter($filePath);

        try {
            $writer->write(file_get_contents($file->getPathname()));
            $writer->close();

            // Create the uploaded file
            $uploadedFile = new UploadedFile(
                $filePath,
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $file->getClientSize()
            );

            $this->setUploadedFile($uploadedFile);

            return true;
        } catch (UploadException $e) {
            $writer->unlink();

            throw $e;
        }
    }
}