<?php

/**
 * @file
 * Contains \Drupal\decoupled_auth\AcquisitionService
 */

namespace Drupal\decoupled_auth;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AcquisitionService implements AcquisitionServiceInterface {

  /**
   * The context for the acquisition process.
   *
   * Additional behaviors are added in AcquisitionService::__construct() from
   * current site configuration.
   *
   * @var array
   */
  protected $context = [
    'name' => NULL,
    'conjunction' => 'AND',
    'behavior' => self::BEHAVIOR_CREATE | self::BEHAVIOR_PREFER_COUPLED,
  ];

  /**
   * The failure code, if any. This is set as part of
   * AcquisitionService::acquire(). One of the
   * \Drupal\decoupled_auth\AcquisitionServiceInterface::FAIL_* constants, or
   * NULL if there is no failure code.
   *
   * @var int|NULL
   */
  protected $failCode;

  /**
   * The user storage class.
   *
   * @var \Drupal\decoupled_auth\DecoupledAuthUserStorageSchema
   */
  protected $userStorage;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher) {
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function acquire(array $values, array $context = [], &$method = NULL) {
    // Ensure method and fail code are NULL before we start.
    $method = NULL;
    $this->failCode = NULL;

    // Merge in our default contexts.
    $this->context = $context + $this->context;

    // Allow modules to make adjustments to our acquisition attempt.
    $this->eventDispatcher->dispatch(AcquisitionEvent::PRE, new AcquisitionEvent($values, $this->context));

    // Look for a match.
    if (!empty($values)) {
      $user = $this->findMatch($values, $this->context);
    }
    // Otherwise record a failure.
    else {
      $this->failCode = self::FAIL_NO_VALUES;
      $user = NULL;
    }

    // If there's no match and we are preferring coupled users, run again
    // without that behavior.
    if (!$user && $this->context['behavior'] & self::BEHAVIOR_PREFER_COUPLED) {
      // Build a new context so we can remove the prefer coupled behavior.
      $new_context = $this->context;
      $new_context['behavior'] -= self::BEHAVIOR_PREFER_COUPLED;

      // We don't exclude coupled from the query as it may have failed due to
      // multiple matches.

      // Re-run the find match with our new context.
      $user = $this->findMatch($values, $new_context);

      // Copy our new fail status in over the old one.
      $this->failCode = $new_context['fail'];
    }


    // If we have a match, we are acquiring.
    if ($user) {
      $method = 'acquire';
    }
    // Otherwise see if we should create.
    elseif ($this->context['behavior'] & self::BEHAVIOR_CREATE) {
      $method = 'create';
      $user = $this->userStorage->create();
    }

    // Allow modules to respond to our acquisition attempt.
    $this->eventDispatcher->dispatch(AcquisitionEvent::POST, new AcquisitionEvent($values, $this->context, $user));

    return $user;
  }

  /**
   * Find a match for the given parameters.
   *
   * @param array $values
   *   An array of party fields to match on. Keys are the field and values are
   *   the expected values.
   * @param array $context
   *   The context we are using to find a match.
   *
   * @return \Drupal\decoupled_auth\DecoupledAuthUserInterface|NULL
   *   Return the matched user or NULL if no valid match could be found.
   */
  protected function findMatch(array $values, array &$context) {
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = $this->userStorage->getQuery($this->context['conjunction'])
      ->addTag('decoupled_auth_acquisition')
      ->addMetaData('values', $values)
      ->addMetaData('context', $this->context);

    // By default, we want to exclude blocked users.
    $values += ['status' => 1];

    // Add our conditions to the query.
    foreach ($values as $key => $value) {
      // 'decoupled' is a special column.
      // @todo: Switch this to a field so it can be queried generally.
      if ($key == 'decoupled') {
        if ($value) {
          $query->notExists('name');
        }
        else {
          $query->exists('name');
        }
      }
      // NULL needs to be dealt with specially.
      elseif ($value === NULL) {
        $query->notExists($key);
      }
      else {
        $query->condition($key, $value);
      }
    }

    // If we have the prefer coupled behaviour, ensure only coupled users are
    // included.
    if ($context['behavior'] & self::BEHAVIOR_PREFER_COUPLED) {
      $query->exists('name');
    }

    // If we are set to take the first, we don't need to return more than one.
    // Otherwise return 2 matches so we can ignore multiple matches.
    $limit = $context['behavior'] & self::BEHAVIOR_FIRST ? 1 : 2;
    $query->range(0, $limit);

    // Get the resulting IDs.
    $uids = $query->execute();

    // If we got a single match we can return a party..
    if (count($uids) == 1) {
      return $this->userStorage->load(reset($uids));
    }

    // Store something helpful in $this->context.
    $context['fail'] = count($uids) ? self::FAIL_MULTIPLE_MATCHES: self::FAIL_NO_MATCHES;

    // Otherwise we have nothing to return.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFailCode() {
    return $this->failCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->context;
  }

}
