<?php

namespace App;

enum HaemovigilanceEventType: string
{
    case DonorReaction = 'donor_reaction';
    case RecipientReaction = 'recipient_reaction';
    case NearMiss = 'near_miss';
    case SeriousAdverseEvent = 'serious_adverse_event';
}
