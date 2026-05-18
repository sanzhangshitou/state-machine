<?php

declare(strict_types=1);

namespace App\Workflow;

use App\StateMachine\Definition;
use App\StateMachine\Transition;

class ProductWorkflow
{
    public const NAME = 'product';

    public const PLACES = [
        'draft',
        'pending_review',
        'published',
        'off_shelf',
        'deleted',
    ];

    public const TRANSITIONS = [
        // name            => [froms,                       tos]
        'submit_review' => ['draft',                        'pending_review'],
        'approve'       => ['pending_review',               'published'],
        'reject'        => ['pending_review',               'draft'],
        'off_shelf'     => ['published',                    'off_shelf'],
        'relist'        => ['off_shelf',                    'published'],
        'delete'        => [['draft', 'off_shelf'],         'deleted'],
    ];

    public static function build(): Definition
    {
        $transitions = [];
        foreach (self::TRANSITIONS as $name => [$froms, $to]) {
            foreach ((array) $froms as $from) {
                $transitions[] = new Transition($name, $from, $to);
            }
        }

        return new Definition(self::NAME, self::PLACES, $transitions, 'draft');
    }
}
