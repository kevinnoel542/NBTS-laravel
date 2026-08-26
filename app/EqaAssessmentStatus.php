<?php

namespace App;

enum EqaAssessmentStatus: string
{
    case Scheduled = 'scheduled';
    case Submitted = 'submitted';
    case Acceptable = 'acceptable';
    case Nonconforming = 'nonconforming';
    case Closed = 'closed';
}
