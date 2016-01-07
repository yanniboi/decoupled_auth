#!/bin/bash

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Ensure the module is linked into the codebase.
drupal_ti_ensure_module_linked

# Require that decoupled_auth is always enabled when the user module is enabled.
cd $DRUPAL_TI_DRUPAL_DIR
git apply -v $DRUPAL_TI_DRUPAL_DIR/modules/decoupled_auth/travis-ci/decoupled_auth_user_modules_installed.patch

# Enable main module and submodules.
drush en -y decoupled_auth

if [ "${TRAVIS_PULL_REQUEST}" = "false" ]; then
  export DRUPAL_TI_SIMPLETEST_GROUP='user'
else
  export DRUPAL_TI_SIMPLETEST_GROUP='decoupled_auth'
fi