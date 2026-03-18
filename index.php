<?php
declare(strict_types=1);

function isWeekend(int $dayOfWeek): bool
{
    return $dayOfWeek === 0 || $dayOfWeek === 6;
}

function getMonthName(int $month): string
{
    $months = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь'
    ];
    return $months[$month];
}

function generateScheduleForPeriod(int $startYear, int $startMonth, int $monthsCount): array
{
    $schedule = [];
    $currentYear = $startYear;
    $currentMonth = $startMonth;
    
    $isWorking = true;
    $daysOffCounter = 0;
    
    for ($m = 0; $m < $monthsCount; $m++) {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%d-%02d-%02d", $currentYear, $currentMonth, $day);
            $dayOfWeek = (int)date('w', strtotime($date));
            
            if ($isWorking) {
                if (isWeekend($dayOfWeek)) {
                    $schedule[$date] = false;
                    
                    $nextDay = $day + 1;
                    while ($nextDay <= $daysInMonth) {
                        $nextDate = sprintf("%d-%02d-%02d", $currentYear, $currentMonth, $nextDay);
                        $nextDayOfWeek = (int)date('w', strtotime($nextDate));
                        if ($nextDayOfWeek === 1) {
                            $schedule[$nextDate] = true;
                            $isWorking = false;
                            $daysOffCounter = 2;
                            $day = $nextDay;
                            break;
                        }
                        $nextDay++;
                    }
                } else {
                    $schedule[$date] = true;
                    $isWorking = false;
                    $daysOffCounter = 2;
                }
            } else {
                $schedule[$date] = false;
                $daysOffCounter--;
                if ($daysOffCounter <= 0) {
                    $isWorking = true;
                }
            }
        }
        
        $currentMonth++;
        if ($currentMonth > 12) {
            $currentMonth = 1;
            $currentYear++;
        }
    }
    
    return $schedule;
}

function displaySchedule(array $schedule, int $startYear, int $startMonth, int $monthsCount): void
{
    $currentYear = $startYear;
    $currentMonth = $startMonth;
    
    for ($m = 0; $m < $monthsCount; $m++) {
        $monthName = getMonthName($currentMonth);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        
        echo "\n" . str_repeat('=', 50) . "\n";
        echo "   Расписание на $monthName $currentYear года\n";
        echo str_repeat('=', 50) . "\n\n";
        
        echo " Пн  Вт  Ср  Чт  Пт  Сб  Вс\n";
        echo str_repeat('----', 7) . "\n";
        
        $firstDayOfMonth = (int)date('w', strtotime("$currentYear-$currentMonth-01"));
        $firstDayOfMonth = $firstDayOfMonth === 0 ? 6 : $firstDayOfMonth - 1;
        
        for ($i = 0; $i < $firstDayOfMonth; $i++) {
            echo "    ";
        }
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%d-%02d-%02d", $currentYear, $currentMonth, $day);
            $dayOfWeek = ($firstDayOfMonth + $day - 1) % 7;
            
            $isWorking = $schedule[$date] ?? false;
            
            if ($isWorking) {
                echo "\033[32m";
            } else {
                echo "\033[31m";
            }
            
            printf("%2d  ", $day);
            echo "\033[0m";
            
            if ($dayOfWeek === 6) {
                echo "\n";
            }
        }
        
        if (($firstDayOfMonth + $daysInMonth - 1) % 7 !== 6) {
            echo "\n";
        }
        
        echo "\n" . str_repeat('-', 50) . "\n";
        echo "Рабочие дни отмечены \033[32mзеленым\033[0m цветом\n";
        echo "Выходные дни отмечены \033[31mкрасным\033[0m цветом\n";
        
        $workingCount = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf("%d-%02d-%02d", $currentYear, $currentMonth, $day);
            if ($schedule[$date] ?? false) {
                $workingCount++;
            }
        }
        echo "Всего рабочих дней в месяце: $workingCount\n";
        
        $currentMonth++;
        if ($currentMonth > 12) {
            $currentMonth = 1;
            $currentYear++;
        }
    }
}

function calculateWorkSchedule(int $startYear = 0, int $startMonth = 0, int $monthsCount = 1): void
{
    if ($startYear === 0) {
        $startYear = (int)date('Y');
    }
    if ($startMonth === 0) {
        $startMonth = (int)date('m');
    }
    
    $schedule = generateScheduleForPeriod($startYear, $startMonth, $monthsCount);
    displaySchedule($schedule, $startYear, $startMonth, $monthsCount);
}

$startYear = $argv[1] ?? 0;
$startMonth = $argv[2] ?? 0;
$monthsCount = $argv[3] ?? 1;

calculateWorkSchedule((int)$startYear, (int)$startMonth, (int)$monthsCount);
