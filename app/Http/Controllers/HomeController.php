<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Показать главную страницу
     */
    public function index()
    {
        // Здесь можно добавить логику для расчета времени до начала соревнования
        $competitionStart = '2026-09-03 10:00:00'; // Пример даты начала
        
        return view('home', [
            'competitionStart' => $competitionStart,
            'timeRemaining' => $this->calculateTimeRemaining($competitionStart),
        ]);
    }

    /**
     * Рассчитать оставшееся время до начала
     */
    private function calculateTimeRemaining($competitionStart)
    {
        $startTime = strtotime($competitionStart);
        $currentTime = time();
        
        if ($currentTime >= $startTime) {
            return [
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'has_started' => true,
            ];
        }
        
        $diff = $startTime - $currentTime;
        
        return [
            'days' => floor($diff / (60 * 60 * 24)),
            'hours' => floor(($diff % (60 * 60 * 24)) / (60 * 60)),
            'minutes' => floor(($diff % (60 * 60)) / 60),
            'seconds' => $diff % 60,
            'has_started' => false,
        ];
    }
}
