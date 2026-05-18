<?php

declare(strict_types=1);

namespace App\Workflow;

use App\StateMachine\Definition;
use App\StateMachine\Transition;

class OrderWorkflow
{
    public const NAME = 'order';

    public const PLACES = [
        'pending',
        'confirmed',
        'processing',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
        'refunding',
    ];

    public const TRANSITIONS = [
        'confirm'        => ['pending',                       'confirmed'],
        'process'        => ['confirmed',                     'processing'],
        'ship'           => ['processing',                    'shipped'],
        'deliver'        => ['shipped',                       'delivered'],
        'complete'       => ['delivered',                     'completed'],
        'cancel'         => [['pending', 'confirmed'],        'cancelled'],
        'request_refund' => ['delivered',                     'refunding'],
        'complete_refund' => ['refunding',                    'completed'],
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
