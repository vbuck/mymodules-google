<?php

/**
 * Google Calendar ACL model.
 *
 * PHP Version 5
 *
 * @see       https://developers.google.com/google-apps/calendar/v3/reference/acl
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

class Mymodules_Google_Model_Calendar_Acl extends Mage_Core_Model_Abstract
{

    /* @var $_apiObject Google_Service_Calendar_Acl */
    protected $_apiObject = null;
    /* @var $_calendar Mymodules_Google_Model_Calendar */
    protected $_calendar;
    /**
     * This managed flag helps to reduce overhead on
     * calendar and ACL collection save operations.
     * Only those entries which have been changed will
     * be updated on Google.
     * 
     * @var $_hasChanged boolean
     */
    protected $_hasChanged = false;

    /**
     * Initialize model.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar_acl');
    }

    /**
     * Get the calendar model to which the ACL entry belongs.
     * 
     * @return Mymodules_Google_Model_Calendar
     */
    public function getCalendar()
    {
        if (!$this->_calendar && $this->getCalendarId()) {
            $this->_calendar = Mage::getModel('google/calendar')->load($this->getCalendarId());
        }

        return $this->_calendar;
    }

    /**
     * Get the calendar ID from data or parent model.
     * 
     * @return string
     */
    public function getCalendarId()
    {
        if (!$this->getData('calendar_id') && $this->_calendar instanceof Mymodules_Google_Model_Calendar) {
            return $this->getCalendar()->getId();
        }

        return $this->getData('calendar_id');
    }

    /**
     * Get the data changed flag.
     * 
     * @return boolean
     */
    public function getHasChanged()
    {
        return $this->_hasChanged;
    }

    /**
     * Save implementation.
     * 
     * @return Mymodules_Google_Model_Calendar_Acl
     */
    public function save()
    {
        // Only save if data has changed
        if ($this->_hasChanged) {
            return parent::save();
        }

        return $this;
    }

    /**
     * Set the parent calendar model.
     * 
     * @param Mymodules_Google_Model_Calendar $calendar The calendar model.
     *
     * @return Mymodules_Google_Model_Calendar_Acl
     */
    public function setCalendar(Mymodules_Google_Model_Calendar $calendar)
    {
        $this->_calendar = $calendar;

        return $this;
    }

    /**
     * Data setter implementation.
     * 
     * @param string $key   The data key.
     * @param mixed  $value The data value.
     *
     * @return Mymodules_Google_Model_Calendar_Acl
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        // Manage changed flag state
        if (is_scalar($key) && $this->dataHasChangedFor($key)) {
            $this->_hasChanged = true;
        }

        return $this;
    }

    /**
     * Set the data changed flag
     * 
     * @param boolean $state The changed state.
     *
     * @return Mymodules_Google_Model_Calendar_Acl
     */
    public function setHasChanged($state = false)
    {
        if ($state == true) {
            $this->_hasChanged = true;
        } else {
            $this->_hasChanged = false;
        }

        return $this;
    }

    /**
     * Set the rule scope.
     * 
     * @param string $type  The type of scope (default|user|group|domain).
     * @param string $value The email address of a user or group, or domain name.
     */
    public function setScope($type = 'default', $value = null)
    {
        $scope = $this->getData('scope');

        if ( !($scope instanceof Mymodules_Google_Model_Calendar_Acl_Rule_Scope) ) {
            $scope = Mage::getModel('google/calendar_acl_rule_scope');
        }

        $scope->setType($type)
            ->setValue($value);

        $this->setData('scope', $scope);

        return $this;
    }

    /**
     * Convert the data to an API-compatible object.
     * 
     * @return Google_Service_Calendar_AclRule
     */
    public function toApiObject()
    {
        // Re-build existing instance
        if ($this->_apiObject) {
            unset($this->_apiObject);
            $this->_apiObject = null;
        }

        if (is_null($this->_apiObject)) {
            $data = Mage::getSingleton('google/resource_helper_calendar')->translateFields($this->getData());

            // Also trigger conversions on object data
            foreach ($data as $key => $value) {
                if (
                    is_object($value) &&
                    method_exists($value, 'toApiObject')
                )
                {
                    $data[$key] = $value->toApiObject();
                }
            }

            $this->_apiObject = new Google_Service_Calendar_AclRule($data);

            unset($data);
        }

        return $this->_apiObject;
    }

}