<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Controller;

use SRIO\RestUploadBundle\Tests\Fixtures\Form\Type\MediaFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class UploadController extends Controller
{
    /**
     * @Route("/upload")
     * @Method({"POST", "PUT"})
     *
     * @return JsonResponse
     */
    public function uploadAction (Request $request)
    {
        $form = $this->createForm(new MediaFormType());
        $uploadManager = $this->get('srio_rest_upload.upload_manager');
        $result = $uploadManager->handleRequest($form, $request);

        if ($result instanceof Response) {
            return $result;
        } else if ($form->isValid()) {
            $media = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            return new JsonResponse($media);
        } else {
            return new NotAcceptableHttpException();
        }
    }
}