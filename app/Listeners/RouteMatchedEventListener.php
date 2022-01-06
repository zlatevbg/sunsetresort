<?php

namespace App\Listeners;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RouteMatchedEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RouteMatched  $event
     * @return void
     */
    public function handle(RouteMatched $event)
    {
        \Slug::setRouteParameters($event->route->parameters());
        \Slug::setRouteSlug($event->route->getName());
    }
}
