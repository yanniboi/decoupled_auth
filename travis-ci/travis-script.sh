#!/bin/bash

set -ev $DRUPAL_TI_DEBUG

# Run PHPUnit tests and submit code coverage statistics.
drupal_ti_ensure_drupal
drupal_ti_ensure_module_linked
cd $DRUPAL_TI_DRUPAL_DIR/core

if [ "${TRAVIS_PULL_REQUEST}" = "false" ]; then
  $DRUPAL_TI_DRUPAL_DIR/vendor/bin/phpunit --group decoupled_auth
else
  $DRUPAL_TI_DRUPAL_DIR/vendor/bin/phpunit
fi
