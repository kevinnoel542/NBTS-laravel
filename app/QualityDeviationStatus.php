<?php

namespace App;

enum QualityDeviationStatus: string
{
    case Open = 'open';
    case Contained = 'contained';
    case CapaInProgress = 'capa_in_progress';
    case EffectivenessCheck = 'effectiveness_check';
    case Closed = 'closed';
}
