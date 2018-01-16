<?php

namespace Hanoivip\Game\Services;

use Hanoivip\Game\ServerSchedule;
use Carbon\Carbon;

class ScheduleService
{
    public function getAll()
    {
        $schedules = ServerSchedule::where('action_time', '>', Carbon::now())
                        ->get();
        return $schedules;
    }
}