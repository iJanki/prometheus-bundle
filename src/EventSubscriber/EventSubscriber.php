<?php

namespace Ijanki\Bundle\PrometheusBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\PrometheusException;

class EventSubscriber implements EventSubscriberInterface
{
    protected $prometheus;
    protected $startTime;

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::REQUEST => [
                ['setStartTime', 10],
            ],
            KernelEvents::TERMINATE => [
                ['updatePrometheusCounters', 10],
            ],
        ];
    }

    public function __construct(CollectorRegistry $prometheus)
    {
        $this->prometheus = $prometheus;
    }

    public function setStartTime(GetResponseEvent $event)
    {
        $this->startTime = microtime(true);
    }

    public function updatePrometheusCounters(PostResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!in_array($request->getPathInfo(), ['/metrics', '/healthz'])) {
            try {
                $totalRequests = $this->prometheus->getCounter('http_requests_total');
                $durationGauge = $this->prometheus->getGauge('http_request_duration_seconds');
                $durationHistogram = $this->prometheus->getHistogram('http_request_duration_seconds_bucket');


                $duration = microtime(true) - $this->startTime;

                if ('404' != $response->getStatusCode()) {
                    $durationGauge->set($duration, ['url' => $request->get('_route')]);
                    $durationHistogram->observe($duration, ['url' => $request->get('_route')]);
                    $totalRequests->inc(1, ['url' => $request->get('_route'), 'code' => $response->getStatusCode()]);
                }
            } catch (PrometheusException $e) {
                echo $e->getMessage();
            }
        }
    }
}