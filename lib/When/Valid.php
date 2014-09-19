<?php

/**
 * iCalendar RFC 2445-compatible recurrence library.
 * Modified for use as a Magento vendor library.
 *
 * PHP Version 5
 * 
 * @package   When
 * @author    Tom Planer <tplaner@gmail.com>
 * @author    Rick Buczynski <me@rickbuczynski.com>
 *
 * Copyright (c) Tom Planer
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class When_Valid
{
    public static $frequencies = array(
                                    'secondly', 'minutely', 'hourly',
                                    'daily', 'weekly', 'monthly', 'yearly'
                                );

    public static  $weekDays = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');

    /**
     * Test if array of days is valid
     *
     * @param  array    $days
     * @return bool
     */
    public static function daysList($days)
    {
        foreach($days as $day)
        {
            // if it isn't negative, it's positive
            $day = ltrim($day, "+");
            $day = trim($day);

            $ordwk = 1;
            $weekday = false;

            if (strlen($day) === 2)
            {
                $weekday = $day;
            }
            else
            {
                list($ordwk, $weekday) = sscanf($day, "%d%s");
            }

            if (!self::weekDay($weekday) || !self::ordWk(abs($ordwk)))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Test for valid itemsList
     *
     * @param  array    $items
     * @param  string   $validator  Validator to use agains the list (second, minute, hour)
     * @return bool
     */
    public static function itemsList($items, $validator)
    {
        foreach ($items as $item)
        {
            if (!self::$validator($item))
            {
                return false;
            }
        }

        return true;
    }

    public static function byFreqValid($freq, $byweeknos, $byyeardays, $bymonthdays)
    {
        if (isset($byweeknos) && $freq !== "yearly")
        {
            throw new InvalidCombination();
        }

        if (isset($byyeardays) && !in_array($freq, array("daily", "weekly", "monthly")))
        {
            throw new InvalidCombination();
        }

        if (isset($bymonthdays) && $freq === "weekly")
        {
            throw new InvalidCombination();
        }

        return true;
    }

    public static function yearDayNum($day)
    {
        return self::ordYrDay(abs($day));
    }

    public static function ordYrDay($ordyrday)
    {
        return ($ordyrday >= 1 && $ordyrday <= 366);
    }

    public static function monthDayNum($day)
    {
        return self::ordMoDay(abs($day));
    }

    public static function monthNum($month)
    {
        return ($month >= 1 && $month <= 12);
    }

    public static function setPosDay($day)
    {
        return self::yearDayNum($day);
    }

    /**
     * Tests for valid ordMoDay
     *
     * @param  integer $ordmoday
     * @return bool
     */
    public static function ordMoDay($ordmoday)
    {
        return ($ordmoday >= 1 && $ordmoday <= 31);
    }

    /**
     * Test for a valid weekNum
     *
     * @param  integer $week
     * @return bool
     */
    public static function weekNum($week)
    {
        return self::ordWk(abs($week));
    }

    /**
     * Test for valid ordWk
     *
     * TODO: ensure this doesn't suffer from Y2K bug since there can be 54 weeks in a year
     *
     * @param  integer $ordwk
     * @return bool
     */
    public static function ordWk($ordwk)
    {
        return ($ordwk >= 1 && $ordwk <= 53);
    }

    /**
     * Test for valid hour
     *
     * @param  integer $hour
     * @return bool
     */
    public static function hour($hour)
    {
        return ($hour >= 0 && $hour <= 23);
    }

    /**
     * Test for valid minute
     *
     * @param  integer $minute
     * @return bool
     */
    public static function minute($minute)
    {
        return ($minute >= 0 && $minute <= 59);
    }

    /**
     * Test for valid second
     *
     * @param  integer $second
     * @return bool
     */
    public static function second($second)
    {
        return ($second >= 0 && $second <= 60);
    }

    /**
     * Test for valid weekDay
     *
     * @param  string $weekDay
     * @return bool
     */
    public static function weekDay($weekDay)
    {
        return in_array(strtolower($weekDay), self::$weekDays);
    }

    /**
     * Test for valid frequency
     *
     * @param  string $frequency
     * @return bool
     */
    public static function freq($frequency)
    {
        return in_array(strtolower($frequency), self::$frequencies);
    }

    /**
     * Test for valid DateTime object
     *
     * @param  DateTime $dateTime
     * @return bool
     */
    public static function dateTimeObject($dateTime)
    {
        return (is_object($dateTime) && $dateTime instanceof \DateTime);
    }
}
