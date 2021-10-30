<?php

namespace App\Service;

class AntiSpamService 
{
    public function antiSpam($data) {
        if ($data != null) { return true; }
    }
}