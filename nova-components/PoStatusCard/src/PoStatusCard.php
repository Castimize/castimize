<?php

namespace Castimize\PoStatusCard;

use Laravel\Nova\Card;

class PoStatusCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    public function statuses(array $statuses): PoStatusCard
    {
        $count = count($statuses);
        return $this->withMeta(['statuses' => $statuses, 'statusesCount' => $count, 'statusesWidth' => (100 / $count)]);
    }

    public function refreshIntervalSeconds(int $seconds = 60): PoStatusCard
    {
        return $this->withMeta(['refreshIntervalSeconds' => $seconds]);
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'po-status-card';
    }
}
