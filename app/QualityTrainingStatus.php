<?php

namespace App;

enum QualityTrainingStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Competent = 'competent';
    case Expired = 'expired';
    case RetrainingRequired = 'retraining_required';
}
