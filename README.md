[![Build Status](https://travis-ci.org/FreelyGive/decoupled_auth.png)](https://travis-ci.org/FreelyGive/decoupled_auth)
# Decoupled User Auth

This project is on Drupal.org here: https://www.drupal.org/project/decoupled_auth

This module aims to provide a simple API for storing information about User's who visit your site whether they register or not. By extended the base user entity the module allows you to store users that do not have a name and password.

##Description

###Why bother?

Drupal has thousands of useful contributed modules and many of these work with User's really well (e.g. Simplenews, OG and Profile2). These modules would be even more effective if they could be used with Users that are not registered yet.

Storing Profile2's for unregistered users opens up whole new possibilities for using Drupal as a CRM framework.

Drupal Commerce could also benefit from this by allowing users to give an email address but not register before going through the checkout process.

###Sessions and Storing

Anonymous User API replaces the UserSession class with a new class UserAnonSession. This is the same in most ways except it provides the extra method store().

Store optionally takes an email address and a label and adds a User record to the database. (@todo:) It then makes a temporary session for this unregistered user so that everything the user does is stored against the right user.

Store() allows you to store a anonymous user record if (but only if) you need to, helping to prevent large amounts of clutter in the User table.

###Example of store()

When a anonymous user fills out a comment form, you may choose to store a user so that when they register with that email address they can take ownership of the comments at a later date.

In hook_comment_insert() simply add the following code

```
<?php
 function mymodule_comment_insert($comment) {
  global $user;

  if ($user->uid == 0) {
    $user->store($comment->mail, $comment->name);
    $comment->uid = $user->uid;
  }
} 
?>
```
