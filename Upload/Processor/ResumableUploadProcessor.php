<?php
namespace SRIO\RestUploadBundle\Upload\Processor;

use Doctrine\ORM\EntityManager;
use SRIO\RestUploadBundle\Entity\ResumableUploadSession;
use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Upload\File\FileWriter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResumableUploadProcessor extends AbstractUploadProcessor
{
    /**
     * @var string
     */
    const PARAMETER_UPLOAD_ID = 'uploadId';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct (EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Handle upload.
     *
     * @param Request $request
     * @param FormInterface $form
     * @param array $config
     * @return bool|void
     */
    public function handleUpload (Request $request, FormInterface $form, array $config)
    {
        if (!array_key_exists('resumable_entity', $config)) {
            throw new UploadProcessorException(sprintf(
                'You must configure the "%s" option',
                'resumable_entity'
            ));
        }

        return parent::handleUpload($request, $form, $config);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Exception|\SRIO\RestUploadBundle\Exception\UploadException
     * @param Request $request
     */
    public function handleRequest (Request $request)
    {
        if ($request->query->has(self::PARAMETER_UPLOAD_ID)) {
            $this->checkHeaders($request, array('Content-Length'));

            $uploadId = $request->query->get(self::PARAMETER_UPLOAD_ID);

            $repository = $this->getRepository();
            $resumableUpload = $repository->findOneBy(array(
                'sessionId' => $uploadId
            ));

            if ($resumableUpload == null) {
                throw new UploadProcessorException('Unable to find upload session');
            }

            return $this->handleResume($request, $resumableUpload);
        }

        return $this->handleStartSession($request);
    }

    /**
     * Handle a start session.
     *
     * @param Request $request
     * @return bool|JsonResponse
     * @throws \SRIO\RestUploadBundle\Exception\UploadProcessorException
     */
    protected function handleStartSession (Request $request)
    {
        // Check that needed headers exists
        $this->checkHeaders($request, array('Content-Type', 'X-Upload-Content-Type', 'X-Upload-Content-Length'));
        $expectedContentType = 'application/json';
        if (substr($request->headers->get('Content-Type'), 0, strlen($expectedContentType)) != $expectedContentType) {
            throw new UploadProcessorException(sprintf(
                'Expected content type is %s. Found %s',
                $expectedContentType,
                $request->headers->get('Content-Type')
            ));
        }

        // Submit form data
        $data = json_decode($request->getContent(), true);
        $formData = $this->createFormData($data);
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return false;
        }

        // Form is valid, store it
        $repository = $this->getRepository();
        $className = $repository->getClassName();

        /** @var $resumableUpload ResumableUploadSession */
        $resumableUpload = new $className;

        $resumableUpload->setData(serialize($formData));
        $resumableUpload->setFilePath($this->createFilePath());
        $resumableUpload->setSessionId($this->createSessionId());
        $resumableUpload->setContentType($request->headers->get('X-Upload-Content-Type'));
        $resumableUpload->setContentLength($request->headers->get('X-Upload-Content-Length'));

        // Store resumable session
        $this->em->persist($resumableUpload);
        $this->em->flush($resumableUpload);

        // Compute redirect location path
        $location = $request->getPathInfo().'?'.http_build_query(array_merge($request->query->all(), array(
            self::PARAMETER_UPLOAD_ID => $resumableUpload->getSessionId()
        )));

        $response = new Response(null);
        $response->headers->set('Location', $location);
        return $response;
    }

    /**
     * Handle an upload resume.
     *
     * @param Request $request
     * @param ResumableUploadSession $uploadSession
     */
    protected function handleResume (Request $request, ResumableUploadSession $uploadSession)
    {
        $contentLength = $request->headers->get('Content-Length');
        if ($request->headers->has('Content-Range')) {
            $range = $this->parseContentRange($request->headers->get('Content-Range'));

            if ($range['total'] != $uploadSession->getContentLength()) {
                throw new UploadProcessorException(sprintf(
                    'File size must be "%d", range total length is %d',
                    $uploadSession->getContentLength(),
                    $range['total']
                ));
            } else if ($range['start'] === '*') {
                if ($contentLength == 0) {
                    return $this->requestUploadStatus($uploadSession, $range);
                }

                throw new UploadProcessorException('Content-Length must be 0 if asking upload status');
            }

            $uploaded = file_exists($uploadSession->getFilePath()) ? filesize($uploadSession->getFilePath()) : 0;
            if ($range['start'] != $uploaded) {
                throw new UploadProcessorException(sprintf(
                    'Unable to start at %d while uploaded is %d',
                    $range['start'],
                    $uploaded
                ));
            }
        } else {
            $range = array(
                'start' => 0,
                'end' => $uploadSession->getContentLength() - 1,
                'total' => $uploadSession->getContentLength() - 1
            );
        }

        // Handle upload from
        $handler = $this->getRequestContentHandler($request);
        $writer = new FileWriter($uploadSession->getFilePath());
        $writer->seek($range['start']);
        $wrote = 0;
        while (!$handler->eof()) {
            $wrote += $writer->write($handler->gets());
        }
        $writer->close();

        // If upload is completed, create the upload file, else
        // return like the request upload status
        if ($writer->size() < $uploadSession->getContentLength()) {
            return $this->requestUploadStatus($uploadSession, $range);
        } else if ($writer->size() == $uploadSession->getContentLength()) {
            return $this->handleCompletedUpload($uploadSession);
        } else {
            throw new UploadProcessorException('Wrote file size is greater that expected Content-Length');
        }
    }

    /**
     * Handle a completed upload.
     *
     * @param ResumableUploadSession $uploadSession
     */
    protected function handleCompletedUpload (ResumableUploadSession $uploadSession)
    {
        // Submit the form data
        $formData = unserialize($uploadSession->getData());
        $this->form->submit($formData);
        if (!$this->form->isValid()) {
            return false;
        }

        // Create the uploaded file
        $uploadedFile = new UploadedFile(
            $uploadSession->getFilePath(),
            null,
            $uploadSession->getContentType(),
            $uploadSession->getContentLength()
        );

        $this->setUploadedFile($uploadedFile);

        return true;
    }

    /**
     * Return the upload status.
     *
     * @param ResumableUploadSession $uploadSession
     * @param array $range
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function requestUploadStatus (ResumableUploadSession $uploadSession, array $range)
    {
        $filePath = $uploadSession->getFilePath();
        if (!file_exists($filePath)) {
            $length = 0;
        } else {
            clearstatcache(true, $filePath);
            $length = filesize($filePath);
        }

        $response = new Response(null, $length == $range['total'] ? 201 : 308);
        $response->headers->set('Range', '0-'.($length - 1));

        return $response;
    }

    /**
     * Parse the Content-Range header.
     *
     * It returns an array with these keys:
     * - `start` Start index of range
     * - `end`   End index of range
     * - `total` Total number of bytes
     *
     * @param string $contentRange
     * @return array
     */
    protected function parseContentRange ($contentRange)
    {
        $contentRange = trim($contentRange);
        if (!preg_match('#^bytes (\*|([0-9]+)-([0-9]+))/([0-9]+)$#', $contentRange, $matches)) {
            throw new UploadProcessorException('Invalid Content-Range header. Must start with "bytes ", range and total length');
        }

        $range = array(
            'start' => $matches[1] === '*' ? '*' : ($matches[2] === '' ? null : (int) $matches[2]),
            'end' => $matches[3] === '' ? null : (int) $matches[3],
            'total' => (int) $matches[4]
        );

        if (empty($range['total'])) {
            throw new UploadProcessorException('Content-Range total length not found');
        }
        if ($range['start'] === '*') {
            if ($range['end'] !== null) {
                throw new UploadProcessorException('Content-Range end must not be present if start is "*"');
            }
        } else if ($range['start'] === null || $range['end'] === null) {
            throw new UploadProcessorException('Content-Range end or start is empty');
        } else if ($range['start'] > $range['end']) {
            throw new UploadProcessorException('Content-Range start must be lower than end');
        } else if ($range['end'] > $range['total']) {
            throw new UploadProcessorException('Content-Range end must be lower or equals to total length');
        }

        return $range;
    }

    /**
     * Get resumable upload session entity repository.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository ()
    {
        return $this->em->getRepository($this->config['resumable_entity']);
    }

    /**
     * Create a session ID.
     *
     */
    protected function createSessionId ()
    {
        return uniqid();
    }
}