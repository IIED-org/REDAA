<?php

namespace Drupal\purge_processor_lateruntime\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\purge\Plugin\Purge\Purger\Exception\CapacityException;
use Drupal\purge\Plugin\Purge\Purger\Exception\DiagnosticsException;
use Drupal\purge\Plugin\Purge\Purger\Exception\LockException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Processes queue items at the end of every request.
 */
class LateRuntimeProcessor implements EventSubscriberInterface {

  /**
   * The processor plugin or FALSE when disabled.
   *
   * @var null|false|\Drupal\purge_processor_lateruntime\Plugin\Purge\Processor\LateRuntimeProcessor
   */
  protected $processor = NULL;

  /**
   * The purge executive service, which wipes content from external caches.
   *
   * @var \Drupal\purge\Plugin\Purge\Purger\PurgersServiceInterface
   */
  protected $purgePurgers;

  /**
   * The queue in which to store, claim and release invalidation objects from.
   *
   * @var \Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface
   */
  protected $purgeQueue;

  /**
   * The container service.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Construct the Late Runtime Processor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container service.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Set a high priority so it is executed before services are destructed. Not
    // doing this would, for example, result in successful invalidation never
    // being deleted from the queue.
    // @see \Drupal\Core\EventSubscriber\KernelDestructionSubscriber::getSubscribedEvents()
    // @see \Drupal\purge\Plugin\Purge\Queue\QueueService::destruct()
    $events[KernelEvents::TERMINATE][] = ['onKernelTerminate', 1000];
    return $events;
  }

  /**
   * Initialize the services.
   *
   * @return bool
   *   TRUE when everything is available, FALSE when our plugin is disabled.
   */
  protected function initialize() {
    if (is_null($this->processor)) {
      // If the lateruntime processor plugin doesn't load, this object is not
      // allowed to operate and thus loads the least possible dependencies.
      $this->processor = $this->container->get('purge.processors')->get('lateruntime');
      if ($this->processor !== FALSE) {
        $this->purgePurgers = $this->container->get('purge.purgers');
        $this->purgeQueue = $this->container->get('purge.queue');
      }
    }
    return $this->processor !== FALSE;
  }

  /**
   * Invoked by the TERMINATE kernel event.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The event object.
   */
  public function onKernelTerminate(TerminateEvent $event) {

    // Immediately stop if our plugin is disabled.
    if (!$this->initialize()) {
      return;
    }

    // Claim a chunk of invalidations, process and let the queue handle results.
    $claims = $this->purgeQueue->claim();
    try {
      $this->purgePurgers->invalidate($this->processor, $claims);
    }
    catch (DiagnosticsException $e) {
      // Diagnostic exceptions happen when the system cannot purge.
    }
    catch (CapacityException $e) {
      // Capacity exceptions happen when too much was purged this request.
    }
    catch (LockException $e) {
      // Lock exceptions happen when another code path is currently processing.
    }
    finally {
      $this->purgeQueue->handleResults($claims);
    }
  }

}
