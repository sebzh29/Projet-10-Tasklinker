<?php

namespace App\Enum;

enum StatutTache: string
{
    case A_FAIRE = 'To Do';
    case EN_COURS = 'Doing';
    case TERMINEE = 'Done';
}