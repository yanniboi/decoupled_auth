#!/bin/bash
# @file
# Drupal-8 environment variables and functions.

#
# Install drupal.
#
function drupal_ti_install_drupal() {
    if [ "$TRAVIS_EVENT_TYPE" = "cron" ]
    then
        composer create-project drupal/drupal:8.*@dev -n
    else
        composer create-project drupal/drupal -n
    fi

    cd drupal
    composer config extra.enable-patching true
    composer config extra.merge-plugin.merge-extra true
    composer require cweagans/composer-patches ~1.6
    composer install
    php -d sendmail_path=$(which true) ~/.composer/vendor/bin/drush.php --yes -v site-install "$DRUPAL_TI_INSTALL_PROFILE" --db-url="$DRUPAL_TI_DB_URL"
    drush use $(pwd)#default
}

#
# Ensures that the module is linked into the Drupal code base.
#
function drupal_ti_ensure_module_linked() {
	# Ensure we are in the right directory.
	cd "$DRUPAL_TI_DRUPAL_DIR"

	# This function is re-entrant.
	if [ -L "$DRUPAL_TI_MODULES_PATH/$DRUPAL_TI_MODULE_NAME" ]
	then
		return
	fi

	composer config repositories.drupal composer https://packages.drupal.org/8
	composer config "repositories.$DRUPAL_TI_MODULE_NAME" path $TRAVIS_BUILD_DIR
    composer require "drupal/$DRUPAL_TI_MODULE_NAME:*"

    # Path repo conflicts with drupal.org repo.
    # @todo Find a better way of using correct commit of module.
	rm -rf "$DRUPAL_TI_DRUPAL_DIR/modules/$DRUPAL_TI_MODULE_NAME"
	MODULE_DIR=$(cd "$TRAVIS_BUILD_DIR"; pwd)
	ln -sf "$MODULE_DIR" "$DRUPAL_TI_DRUPAL_DIR/modules/$DRUPAL_TI_MODULE_NAME"

	git apply -v $DRUPAL_TI_DRUPAL_DIR/modules/decoupled_auth/travis-ci/merging_data_types-2693081-15_0.patch

	# Require that decoupled_auth is always enabled when the user module is enabled.
	git apply -v $DRUPAL_TI_DRUPAL_DIR/modules/decoupled_auth/travis-ci/decoupled_auth_user_modules_installed.patch
}

#
# Ensures that the module is linked but not installed.
#
function drupal_ti_ensure_module() {
	# Ensure the module is linked into the code base.
	drupal_ti_ensure_module_linked
}