<?php

namespace App;

enum ComponentReturnDisposition: string
{
    case Restock = 'restock';
    case Hold = 'hold';
    case Discard = 'discard';
}
