#!/bin/bash

set -ev $DRUPAL_TI_DEBUG

# Run PHPUnit tests and submit code coverage statistics.
drupal_ti_ensure_drupal
drupal_ti_ensure_module_linked
cd $DRUPAL_TI_DRUPAL_DIR/core

if [ "${TRAVIS_PULL_REQUEST}" = "false" ]; then
  $DRUPAL_TI_DRUPAL_DIR/vendor/bin/phpunit
else
  $DRUPAL_TI_DRUPAL_DIR/vendor/bin/phpunit --group decoupled_auth
fi

if [ "${TRAVIS_PULL_REQUEST}" = "false" ]; then
  export ARGS=( $DRUPAL_TI_SIMPLETEST_ARGS )

  cd "$DRUPAL_TI_DRUPAL_DIR"
  { php "$DRUPAL_TI_SIMPLETEST_FILE" --php $(which php) "${ARGS[@]}" || echo "1 fails"; } | tee /tmp/simpletest-result.txt

  egrep -i "([1-9]+ fail[s]?\s)|(Fatal error)|([1-9]+ exception[s]?\s)" /tmp/simpletest-result.txt && exit 1
fi
