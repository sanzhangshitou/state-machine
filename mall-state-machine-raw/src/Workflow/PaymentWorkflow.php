<?php

declare(strict_types=1);

namespace App\Workflow;

use App\StateMachine\Definition;
use App\StateMachine\Transition;

class PaymentWorkflow
{
    public const NAME = 'payment';

    public const PLACES = [
        'pending',
        'processing',
        'paid',
        'failed',
        'refunding',
        'refunded',
        'partial_refund',
    ];

    public const TRANSITIONS = [
        'pay'            => ['pending',          'processing'],
        'pay_success'    => ['processing',       'paid'],
        'pay_fail'       => ['processing',       'failed'],
        'retry_pay'      => ['failed',           'processing'],
        'start_refund'   => ['paid',             'refunding'],
        'refund_success' => ['refunding',        'refunded'],
        'partial_refund' => ['paid',             'partial_refund'],
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
