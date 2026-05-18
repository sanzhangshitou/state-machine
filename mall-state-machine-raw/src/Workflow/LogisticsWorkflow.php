<?php

declare(strict_types=1);

namespace App\Workflow;

use App\StateMachine\Definition;
use App\StateMachine\Transition;

class LogisticsWorkflow
{
    public const NAME = 'logistics';

    public const PLACES = [
        'pending',
        'picking',
        'packed',
        'shipped',
        'arrived',
        'out_for_delivery',
        'delivered',
        'returned',
        'exception',
    ];

    public const TRANSITIONS = [
        'start_pick'     => ['pending',                           'picking'],
        'pack'           => ['picking',                           'packed'],
        'ship_out'       => ['packed',                            'shipped'],
        'arrive'         => ['shipped',                           'arrived'],
        'out_delivery'   => ['arrived',                           'out_for_delivery'],
        'sign'           => ['out_for_delivery',                  'delivered'],
        'return_back'    => ['shipped',                           'returned'],
        'mark_exception' => [['shipped', 'picking', 'packed'],    'exception'],
        'resolve'        => ['exception',                         'shipped'],
    ];

    public static function build(): Definition
    {
        $transitions = [];
        foreach (self::TRANSITIONS as $name => [$froms, $to]) {
            foreach ((array) $froms as $from) {
                $transitions[] = new Transition($name, $from, $to);
            }
        }

        return new Definition(self::NAME, self::PLACES, $transitions, 'pending');
    }
}
