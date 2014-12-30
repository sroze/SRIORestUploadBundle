# Advanced usage

## Strategies

You can set naming and storage strategies for each defined storage.
```yml
srio_rest_upload:
    storages:
        default:
            filesystem: gaufrette.default_filesystem
            naming_strategy: your_naming_strategy_service
            storage_strategy: your_storage_strategy_service
```

### Naming strategy

The naming strategy is responsible to set the name that the stored file will have. The [default naming strategy](../../Strategy/DefaultNamingStrategy.php) create a random file name.

To create your own strategy you just have to create a class that implements the `NamingStrategy` interface. Here's an example with a strategy that generate a random file name but with its extension or the default one as fallback.

```php
namespace Acme\Storage\Strategy;

use SRIO\RestUploadBundle\Upload\UploadContext;
use SRIO\RestUploadBundle\Strategy\NamingStrategy;

class DefaultNamingStrategy implements NamingStrategy
{
    const DEFAULT_EXTENSION = 'png';

    /**
     * {@inheritdoc}
     */
    public function getName(UploadContext $context)
    {
        $name = uniqid();
        $extension = self::DEFAULT_EXTENSION;

        if (($request = $context->getRequest()) !== null) {
            $files = $request->files->all();

            /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = array_pop($files);

            if ($file !== null) {
                $parts = explode('.', $file->getClientOriginalName());
                $extension = array_pop($parts);
            }
        }

        return $name.'.'.$extension;
    }
}
```

Then, define a service and change the `naming_strategy` of your storage configuration with the created service ID.

### Storage strategy

It defines the (sub)directory in which the file will be created in your filesystem.

The storage strategy is working the same way than the naming strategy: create a service with a class that implements `StorageStrategy` and set the `storage_strategy` configuration of your storage with the created service.

## Create a custom handler

You can easily create your custom upload providers (and feel free to _PR_ them on GitHub) by creating a [tagged service](http://symfony.com/doc/current/components/dependency_injection/tags.html) with the `rest_upload.processor` tag

```yml
<parameters>
    <parameter key="acme.my.processor.class">Acme\AcmeBundle\Processor\MyUploadProcessor</parameter>
</parameters>

<services>
    <service id="acme.my.processor" class="%acme.my.processor.class%">
        <argument type="service" id="doctrine.orm.entity_manager" />
        <tag name="rest_upload.processor" uploadType="acme" />
    </service>
</services>
```

Note the `uploadType` attribute that define the unique name of the upload way, set in the `uploadType` query parameters.

Your `MyUploadProcessor` class should then implements the [`ProcessorInterface`](../../Processor/ProcessorInterface.php) or extends the [`AbstractUploadProcessor`](../../Processor/AbstractUploadProcessor.php)

