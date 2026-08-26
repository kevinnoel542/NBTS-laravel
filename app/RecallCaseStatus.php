<?php

namespace App;

enum RecallCaseStatus: string
{
    case Open = 'open';
    case Tracing = 'tracing';
    case Contained = 'contained';
    case Closed = 'closed';
}
