#!/bin/bash

export TEST_ENV="Gaufrette"
vendor/bin/phpunit

export TEST_ENV="Flysystem"
vendor/bin/phpunit