<?php

namespace Hanoivip\Game\Contracts;

interface ServerState
{
    const GOOD = 0;
    const HOT = 1;
    const FULL = 2;
    const MAINTAIN = 3;
}