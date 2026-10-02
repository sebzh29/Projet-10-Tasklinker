<?php

namespace App\Enum;

enum StatutEmploye: string
{
    case CDI = 'CDI';
    case CDD = 'CDD';
    case FREELANCE = 'Freelance';
    case STAGE = 'Stage';
}