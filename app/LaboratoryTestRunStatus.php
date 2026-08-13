<?php

namespace App;

enum LaboratoryTestRunStatus: string
{
    case Completed = 'completed';
    case Invalid = 'invalid';
    case Repeated = 'repeated';
}
