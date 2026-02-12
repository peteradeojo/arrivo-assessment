<?php

namespace App\Enums;

enum Status: int {
    case failed = 0;
    case active = 1;
    case pending = 2;
    case closed = 3;
}
