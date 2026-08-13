<?php

namespace App;

enum LaboratoryQualityEventType: string
{
    case Deviation = 'deviation';
    case Nonconformity = 'nonconformity';
    case InstrumentFailure = 'instrument_failure';
    case ReagentRecall = 'reagent_recall';
    case EqaFailure = 'eqa_failure';
}
