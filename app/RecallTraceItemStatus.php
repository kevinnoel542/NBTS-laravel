<?php

namespace App;

enum RecallTraceItemStatus: string
{
    case Located = 'located';
    case Notified = 'notified';
    case Recovered = 'recovered';
    case Disposed = 'disposed';
    case Transfused = 'transfused';
    case Unresolved = 'unresolved';
}
