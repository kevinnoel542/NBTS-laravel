<?php

namespace App;

enum LaboratoryTestInterpretation: string
{
    case NonReactive = 'non_reactive';
    case Reactive = 'reactive';
    case Negative = 'negative';
    case Positive = 'positive';
    case Discrepant = 'discrepant';
    case Invalid = 'invalid';
}
