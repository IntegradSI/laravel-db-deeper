<?php

namespace Integrad\DbDeeper\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class BackupScheduleFrequency
{
    /**
     * @param  array{name: string, frequency: string, timezone: string, run_at?: string|null, day_of_week?: int|null, day_of_month?: int|null}  $values
     */
    public function nextRun(array $values, ?CarbonImmutable $now = null): CarbonImmutable
    {
        $timezone = $values['timezone'];
        $localNow = ($now ?? CarbonImmutable::now('UTC'))->setTimezone($timezone);
        $frequency = $values['frequency'];

        if ($frequency === 'hourly') {
            return $localNow->startOfHour()->addHour()->setTimezone('UTC');
        }

        $runAt = $values['run_at'] ?? '02:00';
        [$hour, $minute] = array_map('intval', explode(':', $runAt));
        $candidate = $localNow->setTime($hour, $minute);

        if ($frequency === 'daily') {
            if ($candidate->lessThanOrEqualTo($localNow)) {
                $candidate = $candidate->addDay();
            }

            return $candidate->setTimezone('UTC');
        }

        if ($frequency === 'weekly') {
            $dayOfWeek = (int) ($values['day_of_week'] ?? 0);
            $daysAhead = ($dayOfWeek - $localNow->dayOfWeek + 7) % 7;
            $candidate = $candidate->addDays($daysAhead);

            if ($candidate->lessThanOrEqualTo($localNow)) {
                $candidate = $candidate->addWeek();
            }

            return $candidate->setTimezone('UTC');
        }

        if ($frequency === 'monthly') {
            $dayOfMonth = (int) ($values['day_of_month'] ?? 1);
            $candidate = $candidate->setDay($dayOfMonth);

            if ($candidate->lessThanOrEqualTo($localNow)) {
                $candidate = $candidate->addMonthNoOverflow()->setDay($dayOfMonth);
            }

            return $candidate->setTimezone('UTC');
        }

        throw new InvalidArgumentException("Frequência de backup [{$frequency}] não reconhecida.");
    }

    public function description(string $frequency, ?string $runAt, ?int $dayOfWeek, ?int $dayOfMonth): string
    {
        $days = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
        $time = $runAt === null ? '02:00' : substr($runAt, 0, 5);

        return match ($frequency) {
            'hourly' => 'A cada hora',
            'daily' => 'Todos os dias às '.$time,
            'weekly' => 'Toda '.($days[$dayOfWeek ?? 0] ?? 'semana').' às '.$time,
            'monthly' => 'Dia '.($dayOfMonth ?? 1).' de cada mês às '.$time,
            default => 'Frequência inválida',
        };
    }
}
