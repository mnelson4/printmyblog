<?php

namespace Twine\helpers;

use DateTime;

/**
 * Class DateTimeHelper
 * @package Twine\helpers
 */
class DateTimeHelper
{
    const MYSQL_DATE_FORMAT = 'Y-m-d';
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param string $date_string
     *
     * @return DateTime|false
     */
    public static function createFromDateString($date_string)
    {
        return DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $date_string);
    }

    /**
     * @param string $datetime_string
     *
     * @return DateTime|false
     */
    public static function createFromDateTimeString($datetime_string)
    {
        return DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $datetime_string);
    }
    /**
     * Adds a month, rounding down in case the current month doesn't have 31 days
     * @see https://stackoverflow.com/a/34896101/1493883
     *
     * @param DateTime $datetime
     *
     * @return DateTime modified datetime passed in
     */
    public static function addMonth(DateTime $datetime)
    {
        $day = $datetime->format('j');
        $datetime->modify('first day of +1 month');
        $datetime->modify('+' . (min($day, $datetime->format('t')) - 1) . ' days');
        return $datetime;
    }

    /**
     * @param DateTime $datetime
     * @return DateTime
     */
    public static function subtractMonth(DateTime $datetime)
    {
        $datetime = clone $datetime;
        $day = $datetime->format('j');
        $datetime->modify('first day of -1 month');
        $datetime->modify('+' . (min($day, $datetime->format('t')) - 1) . ' days');
        return $datetime;
    }
}
