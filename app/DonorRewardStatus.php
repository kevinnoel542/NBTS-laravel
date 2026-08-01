<?php

namespace App;

enum DonorRewardStatus: string
{
    case Earned = 'earned';
    case Redeemed = 'redeemed';
}
