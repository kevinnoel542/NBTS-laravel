<?php

namespace App;

enum LaboratoryReagentValidationState: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Failed = 'failed';
}
