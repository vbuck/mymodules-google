<?php

/**
 * Google Calendar event recurrence model.
 *
 * PHP Version 5
 *
 * @todo      Add support for EXRULE, RDATE, and EXDATE rules.
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
 * @uses      When
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

class Mymodules_Google_Model_Calendar_Event_Recurrence 
    extends Varien_Object
{

    /**
     * Partial RFC 2445 compliance format.
     *
     * @see Mymodules_Google_Model_Calendar_Event_Recurrence::assemble
     */
    const RFC_2445 = 'yyyyMMddTHHmmss';

    /* @var $_builder When_Recurrence */
    protected $_builder;
    protected $_listDelimiter = ',';
    /* @var $_rrule string */
    protected $_rrule;

    /**
     * Cast object as string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->assemble();
    }

    /**
     * Build an RFC 2445-compatible rule.
     *
     * @see https://developers.google.com/google-apps/calendar/concepts#recurring_events
     * @see https://developers.google.com/google-apps/calendar/recurringevents
     * 
     * @return string
     */
    public function assemble()
    {
        $builder = $this->getBuilder();

        $builder->generateOccurrences();

        $parts = array();

        if ($builder->freq) {
            $freq = strtoupper($builder->freq);

            $parts[] = "FREQ={$freq}";

            unset($freq);
        }

        if ($builder->until && !$builder->count) {
            $date = new Zend_Date($builder->until->getTimestamp());

            // API requires timezone to be set in event start.timeZone
            // Offset the time by the calendar timezone
            $date->setTimezone($this->getEvent()->getTimeZone());

            // Append a generic timezone delimiter, as offset was already applied
            $parts[] = "UNTIL={$date->toString(self::RFC_2445)}Z";
        }

        if ($builder->count) {
            $parts[] = "COUNT={$builder->count}";
        }

        if ($builder->interval) {
            $parts[] = "INTERVAL={$builder->interval}";
        }

        // Does not account for timezone offset
        if ($builder->byseconds) {
            $list = implode($this->_listDelimiter, $builder->byseconds);

            $parts[] = "BYSECOND={$list}";
        }

        // Does not account for timezone offset
        if ($builder->byminutes) {
            $list = implode($this->_listDelimiter, $builder->byminutes);

            $parts[] = "BYMINUTE={$list}";
        }

        // Does not account for timezone offset
        if ($builder->byhours) {
            $list = implode($this->_listDelimiter, $builder->byhours);

            $parts[] = "BYHOUR={$list}";
        }

        // Does not appear to be supported in Google Calendar API V3
        /*if ($builder->bydays) {
            $list = strtoupper( (implode($this->_listDelimiter, $builder->bydays)) );

            $parts[] = "BYDAY={$list}";
        }*/

        // @todo test
        if ($builder->bymonthdays) {
            $list = implode($this->_listDelimiter, $builder->bymonthdays);

            $parts[] = "BYMONTHDAY={$list}";
        }

        // @todo test
        if ($builder->byyeardays) {
            $list = implode($this->_listDelimiter, $builder->byyeardays);

            $parts[] = "BYYEARDAY={$list}";
        }

        // @todo test
        if ($builder->byweeknos) {
            $list = implode($this->_listDelimiter, $builder->byweeknos);

            $parts[] = "BYWEEKNO={$list}";
        }

        // @todo test
        if ($builder->bymonths) {
            $list = implode($this->_listDelimiter, $builder->bymonths);

            $parts[] = "BYMONTH={$list}";
        }

        // @todo test
        if ($builder->bysetpos) {
            $list = implode($this->_listDelimiter, $builder->bysetpos);

            $parts[] = "BYSETPOS={$list}";
        }

        if ($builder->wkst) {
            $wkst = strtoupper($builder->wkst);

            $parts[] = "WKST={$wkst}";

            unset($wkst);
        }

        $this->_rrule = 'RRULE:' . implode(';', $parts);

        return $this->_rrule;
    }

    /**
     * Clear the recurrence data.
     * 
     * @return Mymodules_Google_Model_Calendar_Event_Recurrence
     */
    public function clear()
    {
        $this->_rrules = array();
        $this->_builder = null;

        return $this;
    }

    /**
     * Get the recurrence factory object.
     * 
     * @return When_Recurrence
     */
    public function getBuilder()
    {
        if (!$this->_builder) {
            $this->_builder = new When_Recurrence();
        }

        return $this->_builder;
    }

    /**
     * Get the RRULE.
     * 
     * @return string
     */
    public function getRrule()
    {
        return $this->assemble();
    }

    /**
     * Get the generated occurrences.
     * 
     * @return array
     */
    public function getOccurrences()
    {
        $this->getBuilder()
            ->generateOccurences();

        return $this->getBuilder()->occurrences;
    }

    /**
     * Set the raw RRULE.
     * 
     * @param string $rrule The RRULE string.
     *
     * @return Mymodules_Google_Model_Calendar_Event_Recurrence
     */
    public function setRrule($rrule)
    {
        $this->clear();

        $this->getBuilder()->rrule($rrule);

        return $this;
    }

}