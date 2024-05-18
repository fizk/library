<?php

namespace Library\Service;

enum ServiceActionResult
{
    case created;
    case updated;
    case deleted;
    case noop;
}
