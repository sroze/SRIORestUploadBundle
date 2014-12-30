# Usage

This bundle gives you a `srio_rest_upload.upload_handler` service that provide an `handleRequest` method that make all the work for you which returns an [UploadResult](../../Upload/UploadResult.php) object.

There's two possible usage:
- [Without form](#without-form): meaning we just want to upload the file without associated data
- [With form](#with-form): meaning we also want to add additional data such as name, etc...

In addition to the [upload ways documentation](upload-ways.md) there's an [AngularJS upload usage example](#on-the-client-side).

## Without form

When the file was successfully uploaded, an instance of `\SRIO\RestUploadBundle\Storage\UploadedFile` can be with `getFile` on the upload result.

Here's an example controller that handle uploads with the upload handler.

```php
namespace Acme/Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MediaController extends Controller
{
    public function uploadAction(Request $request)
    {
        $uploadHandler = $this->get('srio_rest_upload.upload_handler');
        $result = $uploadHandler->handleRequest($request);

        if (($response = $result->getResponse()) !== null) {
            return $response;
        }

        if (($file = $result->getFile()) !== null) {
            // Store the file path in an entity, call an API,
            // do whatever with the uploaded file here.

            return new Response();
        }

        throw new BadRequestHttpException('Unable to handle upload request');
    }
}
```

## With form

Because most of the time you may want to link a form to the file upload, you're able to handle it too.
Depending on the [upload way](upload-ways.md) you're using, form data will be fetched from request body or HTTP parameters.

Here's an example of a controller with a form (it comes directly from tests, [feel free to have a look](../../Tests/Fixtures/Controller/UploadController.php), you'll have all sources):
```php
class UploadController extends Controller
{
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
```

## On the client side

Here's a simple example of an upload (which is using the form-data handler) using [AngularJS's `$upload` service](https://github.com/danialfarid/angular-file-upload):
```js
$upload.upload({
    url: '/path/to/upload?uploadType=formData',
    method: 'POST',
    file: file
})
```

