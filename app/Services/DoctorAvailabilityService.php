<?php

namespace App\Services;

use App\Models\Doctor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use RRule\RRule;

class DoctorAvailabilityService
{
    public function getDoctorSlots(Doctor $doctor): array
    {
        $startDate = Carbon::today(); //از امروز //
        $endDate = Carbon::today()->addWeeks(4); // تا ۴ هفته آینده //

        $schedule = [];

        $availabilities = $doctor->availabilities;

        // دریافت استثناات (تعطیلی ها یا تغییر ساعات) در این بازه ۴ هفته‌ای//
        $exceptions = $doctor->exceptions()
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(fn($item) => $item->date);// کلید بر اساس تاریخ برای دسترسی سریع //

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $dayName = $date->format('l');// نام روز (Saturday, ...) //

            $daySlots = [];

            //  ۱. بررسی اولویت اول: آیا برای این روز استثنا (مرخصی یا تغییر ساعت) ثبت شده؟ //
            if (isset($exceptions[$dateString])) {
                $exception = $exceptions[$dateString];

                //اگر روز مرخصی/تعطیل کلاً بود، این روز را رد کن//
                if ($exception->is_off) {
                    continue;
                }

                //اگر ساعت کاری تغییر کرده بود، از ساعت استثنا استفاده می‌کنیم//
                $startTime = $exception->start_time;
                $endTime = $exception->end_time;
                // طول زمان ویزیت را ازجدول استثنا ان روز می گیریم  //
                $slotDuration = $exception->slot_duration_minutes ?? 30;

                $daySlots = $this->generateSlotsForTimes($dateString, $startTime, $endTime, $slotDuration);
            }
            else {
                // ۲. اگر استثنایی نبود، بررسی قوانین هفتگی RRule//
                foreach ($availabilities as $availability) {
                    if ($dateString < $availability->start_date || ($availability->end_date && $dateString > $availability->end_date)) {
                        continue;
                    }

                    // ثبت rrule  //
                    $rruleOptions = [];
                    foreach (explode(';', $availability->rrule) as $part) {
                        [$key, $value] = explode('=', $part);
                        $rruleOptions[$key] = $value;
                    }

                    $rruleOptions['DTSTART'] = $availability->start_date;

                    $rrule = new RRule($rruleOptions);

                    if ($rrule->occursAt($date)) {
                        $slots = $this->generateSlotsForTimes(
                            $dateString,
                            $availability->start_time,
                            $availability->end_time,
                            $availability->slot_duration_minutes
                        );

                        $daySlots = array_merge($daySlots, $slots);
                    }
                }
            }

           // اگر در این روز اسلاتی وجود داشت، به لیست نهایی اضافه کن //
            if (!empty($daySlots)) {
                $schedule[] = [
                    'date' => $dateString,
                    'day_of_week' => $dayName,
                    'slots' => $daySlots
                ];
            }
        }

        return $schedule;
    }

    private function generateSlotsForTimes(string $date, string $startTime, string $endTime, int $durationMinutes): array
    {
        $slots = [];
        $current = Carbon::parse("$date $startTime");
        $end = Carbon::parse("$date $endTime");

        while ($current->copy()->addMinutes($durationMinutes)->lte($end)) {
            $slotStart = $current->format('H:i');
            $current->addMinutes($durationMinutes);
            $slotEnd = $current->format('H:i');

            $slots[] = [
                'start_time' => $slotStart,
                'end_time' => $slotEnd,
                'status' => 'available'
            ];
        }

        return $slots;
    }
}
