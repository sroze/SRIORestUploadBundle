#!/bin/bash

export TEST_FILESYSTEM="Gaufrette"
vendor/bin/phpunit

export TEST_FILESYSTEM="Flysystem"
vendor/bin/phpunit