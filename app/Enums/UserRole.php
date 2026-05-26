<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Translator = 'translator';
}
