<?php

namespace App;

enum BloodUnitQuarantineReason: string
{
    case IncompleteReleaseCriteria = 'incomplete_release_criteria';
    case ReactiveScreening = 'reactive_screening';
    case DiscrepantIdentity = 'discrepant_identity';
    case FailedQualityControl = 'failed_quality_control';
    case Expired = 'expired';
    case Recalled = 'recalled';
    case Unlabelled = 'unlabelled';
    case ColdChainExcursion = 'cold_chain_excursion';
}
