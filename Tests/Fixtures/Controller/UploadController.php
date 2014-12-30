<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Controller;

use SRIO\RestUploadBundle\Tests\Fixtures\Entity\Media;
use SRIO\RestUploadBundle\Tests\Fixtures\Form\Type\MediaFormType;
use SRIO\RestUploadBundle\Upload\UploadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class UploadController extends Controller
{
    /**
     * @Route("/upload")
     * @Method({"POST", "PUT"})
     *
     * @param  \Symfony\Component\HttpFoundation\Request                          $request
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $form = $this->createForm(new MediaFormType());

        /** @var $uploadHandler UploadHandler */
        $uploadHandler = $this->get('srio_rest_upload.upload_handler');
        $result = $uploadHandler->handleRequest($request, $form);

        if (($response = $result->getResponse()) != null) {
            return $response;
        }

        if (!$form->isValid()) {
            throw new BadRequestHttpException();
        }

        if (($file = $result->getFile()) !== null) {
            /** @var $media Media */
            $media = $form->getData();
            $media->setFile($file);

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            return new JsonResponse($media);
        }

        throw new NotAcceptableHttpException();
    }
}
