<?php

namespace Keepsuit\Campaigns\Models;

enum TokenType: string
{
    case Access = 'access';
    case Refresh = 'refresh';
}
