<?php

namespace CodeDistortion\ClarityLogger\Helpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Format a point in time nicely for reading.
 */
class PointInTimeHelper
{
    /**
     * Constructor.
     *
     * @param Carbon|CarbonImmutable $carbon The point in time to render.
     */
    public function __construct(
        private Carbon|CarbonImmutable $carbon
    ) {
    }



    /**
     * Render and add the date/time neatly for reading as a string.
     *
     * @param string[]|string $format   The Carbon formatting to use.
     * @param string[]|string $timezone The timezone/s to render.
     * @return string
     */
    public function renderAsString(array|string $format, array|string $timezone): string
    {
        $lines = $this->renderAsArray($format, $timezone);
        return implode(PHP_EOL, $lines);
    }

    /**
     * Render the date/time neatly for reading, as an array of lines.
     *
     * @param string[]|string $format   The Carbon formatting to use.
     * @param string[]|string $timezone The timezones to render.
     * @return array<string,string>
     */
    private function renderAsArray(array|string $format, array|string $timezone): array
    {
        $format = is_array($format)
            ? $format
            : [$format];
        $timezones = is_array($timezone)
            ? $timezone
            : [$timezone];

        // render the date/time parts, for each timezone
        $dateRows = [];
        foreach ($timezones as $timezone) {

            $pointInTimeLocal = $this->carbon->setTimezone($timezone);
            $dateRow = [];
            foreach ($format as $tempFormat) {
                $dateRow[] = $pointInTimeLocal->format($tempFormat);
            }

            $tz = $pointInTimeLocal->format('T');
            $dateRows[$tz] = $dateRow;
        }

        // for each part, work out what the longest string is between the timezones.
        $maxWidths = [];
        foreach ($dateRows as $dateRow) {
            foreach ($dateRow as $index => $datePart) {
                $maxWidths[$index] = max($maxWidths[$index] ?? 0, mb_strlen($datePart));
            }
        }

        // put the parts together and add the row
        $lines = [];
        foreach ($dateRows as $tz => $dateRow) {

            $sentenceParts = [];
            foreach ($dateRow as $index => $datePart) {
                $sentenceParts[] = str_pad($datePart, $maxWidths[$index]);
            }

            $lines[$tz] = trim(implode(' ', $sentenceParts));
        }

        return $lines;
    }
}
