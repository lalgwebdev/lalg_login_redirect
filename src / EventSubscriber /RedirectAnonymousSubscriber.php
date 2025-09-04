<?php

namespace Drupal\lalg_login_redirect\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Event subscriber subscribing to KernelEvents::REQUEST.
 */
class RedirectAnonymousSubscriber implements EventSubscriberInterface {

  protected AccountProxyInterface $account;
  
  public function __construct(AccountProxyInterface $currentUser) {
    $this->account = $currentUser;
  }
  
  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkAuthStatus'];
    return $events;
  }

  /**
   * Redirect if anonymous and at /myprofile
   *
   * @param Symfony\Component\EventDispatcher\Event $event
   */
  public function checkAuthStatus(RequestEvent $event) {

    $allowed = [
      'user.logout',
      'user.register',
      'user.login',
      'user.reset',
      'user.reset.form',
      'user.reset.login',
      'user.login.http',
      'user.logout.http',
      'user.pass'
    ];

    $route_name = \Drupal::routeMatch()->getRouteName();
    if ( ($this->account->isAnonymous()) && ( !in_array($route_name, $allowed)) ) {
      $path = $event->getRequest()->getPathInfo();
      #\Drupal::logger('lalg_login_redirect')->notice($path);
      if (strpos($path, 'myprofile') !== false) {
        $event->setResponse(new RedirectResponse('/user/login', 302));
      }
    }

  }
}
