<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum BoardRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer';
}
