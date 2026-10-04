<?php

declare(strict_types=1);

namespace FootballApi\Actions;

use FootballApi\Resources\AccountStatus;

trait ManagesAccount
{
    public function status(): AccountStatus
    {
        return AccountStatus::fromArray($this->dataArray($this->get('status')));
    }
}
