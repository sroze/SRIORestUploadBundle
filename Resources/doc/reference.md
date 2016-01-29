# Configuration reference

```yml
srio_rest_upload:
    # Define the available storages
    storages:
        name:
            # Filesystem service created by Gaufrette (or your own matching the Gaufrette's interface)
            filesystem: fs_service_id

            # Naming strategy service
            naming_strategy: srio_rest_upload.naming.default_strategy

            # Storage strategy service
            storage_strategy: srio_rest_upload.storage.default_strategy

    # The storage voter, that chose between storage based on upload context
    storage_voter: srio_rest_upload.storage_voter.default

    # The default storage name. With the default storage voter, it'll use
    # the first defined storage if value is null
    default_storage: ~

    # If you want to use the resumable upload way, you must set
    # the class name of your entity which store the upload sessions.
    resumable_entity_class: ~

    # Parameter the define the upload way, internally the provider selector
    upload_type_parameter: uploadType
```

The [Advanced usage](advanced.md) section explain the naming and storage strategies.
