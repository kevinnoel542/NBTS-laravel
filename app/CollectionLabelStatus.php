<?php

namespace App;

enum CollectionLabelStatus: string
{
    case Generated = 'generated';
    case Printed = 'printed';
    case Applied = 'applied';
    case Voided = 'voided';
}
