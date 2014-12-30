# SRIORestUploadBundle

[![Build Status](https://api.travis-ci.org/sroze/SRIORestUploadBundle.png)](https://travis-ci.org/sroze/SRIORestUploadBundle)

This bundle provide a simple ways to handle uploads on the server side.

Currently, it supports the simple, form-data, multipart and resumable ways.

## Getting started

Using [Gaufrette](https://github.com/KnpLabs/Gaufrette) as storage layer, you can handle file uploads and store files on many places such as a local file system, an Amazon S3 bucket, ...

- [Installation](Resources/doc/installation.md)
- [Usage](Resources/doc/usage.md)
- [Advanced usage](Resources/doc/advanced.md)
- [Upload ways summary](Resources/doc/upload-ways.md)
- [Configuration reference](Resources/doc/reference.md)

## Testing

Tests are run with [PHPUnit](http://phpunit.de). Once you installed dependencies with composer, then:

- Create a database, allow access to a user, and set configuration in `Tests/Fixtures/App/app/config/parameters.yml` file
- Create the database schema for the `test` environment
  ```sh
  php Tests/Fixtures/App/app/console doctrine:schema:update --force --env=test
  ```
- Run PHPUnit
  ```sh
  phpunit
  ```
