<?php

namespace App;

enum ColdChainDeviceType: string
{
    case Refrigerator = 'refrigerator';
    case Freezer = 'freezer';
    case PlateletStorage = 'platelet_storage';
    case TransportBox = 'transport_box';
    case DataLogger = 'data_logger';
    case Generator = 'generator';
}
