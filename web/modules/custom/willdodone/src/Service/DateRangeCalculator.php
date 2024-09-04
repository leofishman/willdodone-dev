<?php

namespace Drupal\willdodone\Service;

use Drupal\Core\Datetime\DrupalDateTime;

class DateRangeCalculator {

    /**
     * Calculate the total time in seconds for a Date Range field.
     *
     * @param array $date_range_values
     *   The values of the Date Range field.
     *
     * @return int
     *   The total time in seconds.
     */
    public static function calculateTotalTime(array $date_range_values) {
        $total_time = 0;

        foreach ($date_range_values as $date_range) {
//            $start_date = DrupalDateTime::createFromFormat(DrupalDateTime::FORMAT, $date_range['value']);
//            $end_date = DrupalDateTime::createFromFormat(DrupalDateTime::FORMAT, $date_range['end_value']);
            $start_date = $date_range['value'];
            $end_date = $date_range['end_value'];

            if ($start_date && $end_date) {
                $interval = date_diff($end_date, $start_date);
                $total_time += $interval->days * 24 * 3600 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
            }
        }

        return $total_time;
    }
}