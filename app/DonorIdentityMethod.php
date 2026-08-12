<?php

namespace App;

enum DonorIdentityMethod: string
{
    case DonorCardQr = 'donor_card_qr';
    case DonorId = 'donor_id';
    case NationalIdentifier = 'national_identifier';
    case AssistedQuestions = 'assisted_questions';
    case OfflineAssisted = 'offline_assisted';
}
