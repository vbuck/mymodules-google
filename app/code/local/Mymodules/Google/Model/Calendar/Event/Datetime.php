<?php

/**
 * Simple wrapper model for event datetime objects.
 *
 * PHP Version 5
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
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

class Mymodules_Google_Model_Calendar_Event_Datetime 
    extends Mage_Core_Model_Abstract
{

    /* @var $_apiObject Google_Service_Calendar_EventDateTime */
    protected $_apiObject = null;

    /**
     * Local constructor.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar_event_datetime');

        if ($this->getData('date')) {
            $this->setDate($this->getData('date'));
        }

        if ($this->getData('date_time')) {
            $this->setDatetime($this->getData('date_time'));
        }
    }

    /**
     * Get the date as an object.
     * 
     * @return DateTime
     */
    public function getDateObject()
    {
        return new DateTime($this->getData('date'), new DateTimeZone($this->getTimeZone()));
    }

    /**
     * Get the datetime as an object.
     * 
     * @return DateTime
     */
    public function getDateTimeObject()
    {
        return new DateTime($this->getData('date_time'), new DateTimeZone($this->getTimeZone()));
    }

    /**
     * Convert date before setting.
     * 
     * @param string $date The date input.
     *
     * @return Mymodules_Google_Model_Calendar_Event_Datetime
     */
    public function setDate($date = 'now')
    {
        $this->setData(
            'date', 
            Mage::getSingleton('google/resource_helper_calendar')->convertDatetime(
                $date, 
                'y-MM-dd', 
                $this->getTimeZone(),
                'UTC'
            )
        );

        return $this;
    }

    /**
     * Convert datetime before setting.
     * 
     * @param string $datetime The datetime input.
     *
     * @return Mymodules_Google_Model_Calendar_Event_Datetime
     */
    public function setDateTime($datetime = 'now')
    {
        $this->setData(
            'date_time', 
            Mage::getSingleton('google/resource_helper_calendar')->convertDatetime(
                $datetime, 
                null, 
                $this->getTimeZone(),
                'UTC'
            )
        );

        return $this;
    }

    /**
     * Convert the data to an API-compatible object.
     * 
     * @return Google_Service_Calendar_EventDatetime
     */
    public function toApiObject()
    {
        // Re-build existing instance
        if ($this->_apiObject) {
            unset($this->_apiObject);
            $this->_apiObject = null;
        }

        if (is_null($this->_apiObject)) {
            $helper = Mage::getSingleton('google/resource_helper_calendar');
            $data   = $helper->translateFields($this->getData());

            $this->_apiObject = new Google_Service_Calendar_EventDateTime($data);

            unset($data);
        }

        return $this->_apiObject;
    }

}