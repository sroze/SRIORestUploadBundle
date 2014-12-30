# Installation

First, you need to install [KnpGaufretteBundle](https://github.com/KnpLabs/KnpGaufretteBundle), a Symfony integration of Gaufrette which will handle the file storage on places your want.

## Add SRIORestUploadBundle in your dependencies

In your `composer.json` file, add `srio/rest-upload-bundle`:
```json
{
    "require": {
        "srio/rest-upload-bundle": "~2.0.0"
    }
}
```

Then, update your dependencies:
```
composer update srio/rest-upload-bundle
```

## Enable the bundle in your kernel

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new SRIO\RestUploadBundle\SRIORestUploadBundle(),
    );
}
```

## Create the Gaufrette filesystem

In your configuration file, create your Gaufrette filesystem. Let's start with a local filesystem storage in the `web/uploads` directory.

```yml
# app/config/config.yml

knp_gaufrette:
    adapters:
        local_uploads:
            local:
                directory: %kernel.root_dir%/../web/uploads
    filesystems:
        uploads:
            adapter: local_uploads
```

## Configure the bundle

Then, we just have to configure the bundle to use the Gaufrette storage:
```
srio_rest_upload:
    storages:
        default:
            filesystem: gaufrette.uploads_filesystem
```

If you want to use the resumable upload way, you have to [configure it](upload-ways.md#resumable-configuration).

Then, [start using it](usage.md).
