<?php
namespace SRIO\RestUploadBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UploadController extends Controller
{
    /**
     * @Route("/upload")
     * @Method({"POST", "PUT"})
     *
     * @return JsonResponse
     */
    public function uploadAction ()
    {
        return new JsonResponse(array('code' => 'OK'));
    }
}