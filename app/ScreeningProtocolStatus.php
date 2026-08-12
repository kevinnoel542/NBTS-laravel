<?php

namespace App;

enum ScreeningProtocolStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Retired = 'retired';
}
