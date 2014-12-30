# Upload ways

This is a summary of currently supported upload ways.

- [Simple](#simple-upload-way): Send binary data to an URL and use query parameters to submit additional data.
- [Multipart](#multipart-upload-way): Send both JSON and binary data using the [multipart Content-Type](http://www.w3.org/Protocols/rfc1341/7_2_Multipart.html).
- [FormData](#formdata-upload-way): Matches the classic browser file upload
- [Resumable](#resumable-upload-way): start a resumable upload session by sending JSON data and then send file entirely or by chunks. It allow to restart a failed upload where it stops.

## Simple upload way

The most straightforward method for uploading a file is by making a simple upload request. This option is a good choice when:
- The file is small enough to upload again in its entirety if the connection fails
- There is no or a very small amount of metadata to send

To use simple upload, make a `POST` or `PUT` request to the upload method's URI and add the query parameter `uploadType=simple`.
The HTTP headers to use when making a simple upload request include:
- `Content-Type`. Set the media content-type
- `Content-Length`. Set to the number of bytes you are uploading.

### Example

The following example shows the use of a simple photo upload request for an upload path that would be `/upload`:

```
POST /upload?uploadType=simple HTTP/1.1
Host: www.example.com
Content-Type: image/jpeg
Content-Length: number_of_bytes_in_JPEG_file

JPEG data
```

## Multipart upload way

If you have metadata that you want to send along with the data to upload, you can make a single `multipart/related` request. This is a good choice if the data you are sending is small enough to upload again in its entirety if the connection fails.
To use multipart upload, make a `POST` or `PUT` request to the upload method's URI and add the query parameter `uploadType=multipart`.

The top-level HTTP headers to use when making a multipart upload request include:
- `Content-Type`. Set to `multipart/related` and include the boundary string you're using to identify the parts of the request.
- `Content-Length`. Set to the total number of bytes in the request body.

The body of the request is formatted as a `multipart/related` content type [<a href="http://tools.ietf.org/html/rfc2387">RFC2387</a>] and contains exactly two parts. The parts are identified by a boundary string, and the final boundary string is followed by two hyphens.

Each part of the multipart request needs an additional `Content-Type` header:
- **Metadata part**: Must come first, and Content-Type must match one of the the accepted metadata formats.
- **Media part**: Must come second, and Content-Type must match one the method's accepted media MIME types.

### Example

The following example shows the use of a multipart upload request for an upload path that would be `/upload`:

```
POST /upload?uploadType=multipart HTTP/1.1
Host: www.example.com
Content-Type: multipart/related; boundary="foo_bar_baz"
Content-Length: number_of_bytes_in_entire_request_body

--foo_bar_baz
Content-Type: application/json; charset=UTF-8

{
    "name": "Some value"
}

--foo_bar_baz
Content-Type: image/jpeg

JPEG data

--foo_bar_baz--
```

### FormData upload way

This may be the most used way to upload files: it matches with the classic form "file" upload.

You just have to have a field of type `file` named `file` on your form and set the action path to `/upload?uploadType=formData`.
It can ether be used with any XHR upload method.

## Resumable upload way

To upload data files more reliably, you can use the resumable upload protocol. This protocol allows you to resume an upload operation after a communication failure has interrupted the flow of data. It is especially useful if you are transferring large files and the likelihood of a network interruption or some other transmission failure is high, for example, when uploading from a mobile client app. It can also reduce your bandwidth usage in the event of network failures because you don&#39;t have to restart large file uploads from the beginning.

The steps for using resumable upload include:

1.  [Start a resumable session](#start-resumable). Make an initial request to the upload URI that includes the metadata, if any.
2.  [Save the resumable session URI](#save-session-uri). Save the session URI returned in the response of the initial request; you'll use it for the remaining requests in this session.
3.  [Upload the file](#upload-resumable). Send the media file to the resumable session URI.

In addition, apps that use resumable upload need to have code to [resume an interrupted upload](#resume-upload). If an upload is interrupted, find out how much data was successfully received, and then resume the upload starting from that point.

### Resumable Configuration

First, you need to configure the bundle.

#### Create your `ResumableUploadSession` entity

The entity will contains the resumable upload sessions and is required if you want the resumable way of upload to work.

```php
<?php
namespace Acme\Entity;

use SRIO\RestUploadBundle\Entity\ResumableUploadSession as BaseResumableUploadSession;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ResumableUploadSession extends BaseResumableUploadSession
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
}
```

Copy this entity in your bundle and update the schema.

#### Configure the bundle

Configure the bundle to use this entity to store resumable upload sessions:
```yml
srio_rest_upload:
    resumable_entity: Acme\Entity\ResumableUploadSession
```

### Resumable upload requests

#### Start a resumable session

For this initiating request, the body is either empty or it contains the metadata only; you'll transfer the actual contents of the file you want to upload in subsequent requests.

Use the following HTTP headers with the initial request:

*   `X-Upload-Content-Type`. Set to the media MIME type of the upload data to be transferred in subsequent requests.
*   `X-Upload-Content-Length`. Set to the number of bytes of upload data to be transferred in subsequent requests.
*   `Content-Type`. Set according to the metadata's data type.
*   `Content-Length`. Set to the number of bytes provided in the body of this initial request.

The following example shows the use of a resumable upload request for an upload path that would be `/upload`:

```
POST /upload?uploadType=resumable HTTP/1.1
Host: www.example.com
Content-Length: 41
Content-Type: application/json; charset=UTF-8
X-Upload-Content-Type: image/jpeg
X-Upload-Content-Length: 2000000

{
    "name": "Some value"
}
```

#### Save the resumable session URI

If the session initiation request succeeds, the API server responds with a `200 OK` HTTP status code. In addition, it provides a `Location` header that specifies your resumable session URI. The `Location` header, shown in the example below, includes an `uploadId` query parameter portion that gives the unique upload ID to use for this session.

Here is the response to the request in the last step:

```
HTTP/1.1 200 OK
Location: /upload?uploadType=resumable&uploadId=fooBar123
Content-Length: 0
```

The value of the `Location` header, as shown in the above example response, is the session URI you'll use as the HTTP endpoint for doing the actual file upload or querying the upload status.

#### Upload the file

To upload the file, send a `PUT` request to the upload URI that you obtained in the previous step.

The HTTP headers to use when making the resumable file upload requests includes `Content-Length`. Set this to the number of bytes you are uploading in this request, which is generally the upload file size.

```
PUT /upload?uploadType=resumable&uploadId=fooBar123 HTTP/1.1
Content-Length: 2000000
Content-Type: image/jpeg

bytes 0-1999999
```

If the request succeeds, the server responds with an HTTP `201 Created`, along with any metadata associated with this resource. If the initial request of the resumable session had been a `PUT`, to update an existing resource, the success response would be `200 OK`, along with any metadata associated with this resource.

#### Upload file in chunks

With resumable uploads, you can break a file into chunks and send a series of requests to upload each chunk in sequence. This is not the preferred approach since there are performance costs associated with the additional requests, and it is generally not needed. However, you might need to use chunking to reduce the amount of data transferred in any single request.

If you are uploading the data in chunks, the `Content-Range` header is also required, along with the `Content-Length` header required for full file uploads:

*   `Content-Length`. Set to the chunk size or possibly less, as might be the case for the last request.
*   `Content-Range`: Set to show which bytes in the file you are uploading. For example, `Content-Range: bytes 0-524287/2000000` shows that you are providing the first 524,288 bytes in a 2,000,000 byte file.

A sample request would be:

```
PUT {session_uri} HTTP/1.1
Host: www.example.com
Content-Length: 524288
Content-Type: image/jpeg
Content-Range: bytes 0-524287/2000000

bytes 0-524288
```

If the request succeeds, the server responds with `308 Resume Incomplete`, along with a `Range` header that identifies the total number of bytes that have been stored so far:

```
HTTP/1.1 308 Resume Incomplete
Content-Length: 0
Range: 0-524287
```

Use the upper value returned in the `Range` header to determine where to start the next chunk. Continue to PUT each chunk of the file until the entire file has been uploaded

#### Resume an interrupted upload

If an upload request is terminated before receiving a response or if you receive an HTTP `503 Service Unavailable` or even an HTTP `500 Internal Server Error` response from the server, then you need to resume the interrupted upload. To do this:

- **Request the upload status**
  ```
  PUT {session_uri} HTTP/1.1
  Content-Length: 0
  Content-Range: bytes */2000000
  ```

- **Extract the number of bytes uploaded so far from the response**
  The server's response uses the `Range` header to indicate that it has received the first 43 bytes of the file so far. Use the upper value of the `Range` header to determine where to start the resumed upload.
  ```
  HTTP/1.1 308 Resume Incomplete
  Content-Length: 0
  Range: 0-42
  ```

- **Resume the upload from the point where it left off**
  Use the [upload file in chunks](#upload-chunks) method to restart upload at the point where there's a failure.


