<?php
/**
 * @file
 * Contains \Drupal\raygun\EventSubscriber\BootSubscriber.
 */

namespace Drupal\raygun\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;


/**
 * BootSubscriber event subscriber.
 *
 * @package Drupal\raygun\EventSubscriber
 */
class BootSubscriber extends ControllerBase implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent(GetResponseEvent $event) {
    $config = $this->configFactory->get('raygun.settings');
    $apikey = trim($config->get('apikey'));
    if (file_exists(_raygun_get_library_path()) && !empty($apikey)) {
      $user = \Drupal::currentUser();
      global $raygun_client;

      require_once _raygun_get_library_path();
      $raygun_client = new \Raygun4php\RaygunClient($config->get('apikey'), (bool) $config->get('async_sending'));
      echo '<pre>';print_r($raygun_client);

      if ($config->get('send_version') && $config->get('application_version') != '') {
        $raygun_client->SetVersion($config->get('application_version'));
      }
      if ($config->get('send_email') && $user->id()) {
        $raygun_client->SetUser($user->getEmail());
      }
      if ($config->get('exceptions')) {
        set_exception_handler('exception_handler');
      }
      if ($config->get('error_handling')) {
        set_error_handler('error_handler');
      }
    }
  }
}
