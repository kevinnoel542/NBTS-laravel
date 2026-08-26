<?php

namespace App;

enum QualityDocumentStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Effective = 'effective';
    case Retired = 'retired';
}
