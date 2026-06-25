<?php

namespace App\Helpers;

use DateTime;

class Time {
    // helper functions

    static function formatEventDates(array $eventDetails): string
    {
        // Extract dates from the array
        $startDateStr = $eventDetails['start_date'];
        $endDateStr = $eventDetails['end_date'];

        // Convert date strings to DateTime objects for easy comparison and formatting
        $startDate = new DateTime($startDateStr);
        $endDate = new DateTime($endDateStr);

        // Check if it's a single-day event
        if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            // Format: "Month Day, Year" (e.g., October 15, 2025)
            return $startDate->format('F j, Y');
        }

        // Check if it's a multi-day event in the same month and year
        if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
            // Format: "Month StartDay-EndDay, Year" (e.g., August 25-27, 2025)
            $month = $startDate->format('F');
            $startDay = $startDate->format('j');
            $endDay = $endDate->format('j');
            $year = $startDate->format('Y');
            return "{$month} {$startDay}-{$endDay}, {$year}";
        }

        // Check if it's a multi-month event in the same year
        if ($startDate->format('Y') === $endDate->format('Y')) {
            // Format: "StartMonth StartDay - EndMonth EndDay, Year" (e.g., Dec 25 - Jan 05, 2026)
            $startFmt = $startDate->format('M j');
            $endFmt = $endDate->format('M j, Y');
            return "{$startFmt} - {$endFmt}";
        }

        // Default to a full format for events spanning different years
        // Format: "StartMonth StartDay, StartYear - EndMonth EndDay, EndYear"
        $startFmt = $startDate->format('M j, Y');
        $endFmt = $endDate->format('M j, Y');
        return "{$startFmt} - {$endFmt}";
    }

    static function formatEventTime(array $eventDetails): string
    {
        $startTimeStr = $eventDetails['start_time'] ?? null;
        $endTimeStr   = $eventDetails['end_time'] ?? null;

        if (!$startTimeStr && !$endTimeStr) {
            return '';
        }

        // Convert to DateTime objects
        $startTime = $startTimeStr ? DateTime::createFromFormat('H:i:s', $startTimeStr) : null;
        $endTime   = $endTimeStr ? DateTime::createFromFormat('H:i:s', $endTimeStr) : null;

        // Only start time
        if ($startTime && !$endTime) {
            return $startTime->format('g:i A'); // e.g., 9:00 AM
        }

        // Only end time
        if (!$startTime && $endTime) {
            return $endTime->format('g:i A'); // e.g., 5:00 PM
        }

        // Both start and end times
        if ($startTime && $endTime) {
            // If both times are in same AM/PM period, show concise: 9:00-11:00 AM
            $startPeriod = $startTime->format('A');
            $endPeriod   = $endTime->format('A');

            if ($startPeriod === $endPeriod) {
                return $startTime->format('g:i') . '-' . $endTime->format('g:i A'); // e.g., 9:00-11:00 AM
            } else {
                // Different periods, show full: 11:00 AM - 1:00 PM
                return $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A');
            }
        }

        return '';
    }
}