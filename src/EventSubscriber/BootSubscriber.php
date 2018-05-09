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
    $apikey = trim($config->get('raygun_apikey'));
    if (file_exists(_raygun_get_library_path()) && !empty($apikey)) {
      $user = \Drupal::currentUser();
      global $raygun_client;

      require_once _raygun_get_library_path();
      $raygun_client = new \Raygun4php\RaygunClient($config->get('raygun_apikey'), (bool) $config->get('raygun_async_sending'));

      if ($config->get('raygun_send_version') && $config->get('raygun_application_version') != '') {
        $raygun_client->SetVersion($config->get('raygun_application_version'));
      }
      if ($config->get('raygun_send_email') && $user->id()) {
        $raygun_client->SetUser($user->getEmail());
      }
      if ($config->get('raygun_exceptions')) {
        set_exception_handler('raygun_exception_handler');
      }
      if ($config->get('raygun_error_handling')) {
        set_error_handler('raygun_error_handler');
      }
      // Proxy support
      // @FIXME
      // // @FIXME
      // // This looks like another module's variable. You'll need to rewrite this call
      // // to ensure that it uses the correct configuration object.
      // if ($proxy_server = variable_get('proxy_server', FALSE)) {
      //       if ($proxy_port = variable_get('proxy_port', FALSE)) {
      //         $raygun_client->setProxy('http://' . $proxy_server . ':' . $proxy_port);
      //       }
      //       else {
      //         $raygun_client->setProxy('http://' . $proxy_server);
      //       }
      //     }

    }
  }

}
