#!/bin/bash

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Download module dependencies.
#(
	# These variables come from environments/drupal-*.sh
	#mkdir -p "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH"
    #cd "$DRUPAL_TI_DRUPAL_DIR/$DRUPAL_TI_MODULES_PATH"
	#git clone --branch 8.x-1.x http://git.drupal.org/project/module_we_need.git --depth 1
#)

# Ensure the module is linked into the codebase.
drupal_ti_ensure_module_linked

pwd
ls

# Enable main module and submodules.
drush en -y decoupled_auth