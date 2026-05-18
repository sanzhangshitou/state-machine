<?php

declare(strict_types=1);

namespace App\Workflow;

use App\StateMachine\Definition;
use App\StateMachine\Transition;

class AfterSaleWorkflow
{
    public const NAME = 'after_sale';

    public const PLACES = [
        'pending',
        'approved',
        'rejected',
        'returning',
        'returned',
        'refunding',
        'refunded',
        'completed',
        'closed',
    ];

    public const TRANSITIONS = [
        'approve'        => ['pending',                          'approved'],
        'reject'         => ['pending',                          'rejected'],
        'reapply'        => ['rejected',                         'pending'],
        'start_return'   => ['approved',                         'returning'],
        'confirm_return' => ['returning',                        'returned'],
        'start_refund'   => [['approved', 'returned'],           'refunding'],
        'refund_success' => ['refunding',                        'refunded'],
        'complete'       => ['refunded',                         'completed'],
        'close'          => [['pending', 'approved', 'rejected'], 'closed'],
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
