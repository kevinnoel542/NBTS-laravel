<?php

namespace App;

enum DonorReactionSeverity: string
{
    case Mild = 'mild';
    case Moderate = 'moderate';
    case Severe = 'severe';
    case Critical = 'critical';
}
