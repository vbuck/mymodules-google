<?php

/**
 * Google Calendar model version 3.
 *
 * PHP Version 5
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
 *
 * Copyright (c) 2014 Rick Buczynski
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

class Mymodules_Google_Model_Calendar 
    extends Mage_Core_Model_Abstract
{

    /* @var $_acl Mymodules_Google_Model_Resource_Calendar_Acl_Collection */
    protected $_acl;
    /* @var $_apiObject Google_Service_Calendar_CalendarListEntry|Google_Service_Calendar_Calendar */
    protected $_apiObject;
    /* @var $_colorScheme array */
    protected $_colorScheme;
    /* @var $_events Mymodules_Google_Model_Resource_Calendar_Event_Collection */
    protected $_events;
    protected $_quickEventCache = array();

    /**
     * Initialize model.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar');
    }

    /**
     * After-save implementation.
     * 
     * @return Mymodules_Google_Model_Calendar
     */
    protected function _afterSave()
    {
        // Publish any quick events
        foreach ($this->_quickEventCache as $event) {
            $this->_getResource()
                ->addQuickEvent($this, $event[0], $event[1]);
        }

        $this->_quickEventCache = array();

        // Save events
        // Will only update modified or new events
        $this->getEvents()->save();

        // Save ACL entries
        // Will only update modified or new ACL entries
        $this->getAcl()->save();

        return $this;
    }

    /**
     * Add an event to the calendar. Attempts to convert input times
     * to RFC 3339 format automatically.
     * 
     * @param string $summary The event summary.
     * @param string $start   The event start time.
     * @param string $end     The event end time.
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function addEvent($summary = '', $start = null, $end = null)
    {
        $event  = Mage::getModel('google/calendar_event')
            ->setCalendarId($this->getId());

        $helper = Mage::getSingleton('google/resource_helper_calendar');

        // Use current time if start is not given
        if (is_null($start)) {
            $start = date('Y-m-d h:i:s', time());
        }

        $start = $helper->convertDatetime($start);

        // Use start + 1 hour if end is not given
        if (is_null($end)) {
            $end = date('Y-m-d h:i:s', strtotime($start) + 3600);
        }

        $end = $helper->convertDatetime($end);

        $event->setSummary($summary)
            ->setStart($start)
            ->setEnd($end);

        $this->addEventByObject($event);

        return $this;
    }

    /**
     * Add an event model to the internal collection.
     * 
     * @param Mymodules_Google_Model_Calendar_Event $event The event model.
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function addEventByObject(Mymodules_Google_Model_Calendar_Event $event)
    {
        $this->getEvents()->addItem($event);

        return $this;
    }

    /**
     * Add a notification option to the calendar.
     * 
     * @param string  $method  The reminder method (email|sms[read-only]).
     * @param integer $type    The type of notification, can be:
     *                             eventCreation
     *                             eventChange
     *                             eventCancellation
     *                             eventResponse
     *                             agenda
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function addNotification($method = 'email', $type = null)
    {
        $reminder = Mage::getModel(
            'google/calendar_event_notification', 
            array(
                'method' => $method,
                'type'   => $type,
            )
        );

        $this->getNotificationSettings()->addItem($reminder);
        
        return $this;
    }

    /**
     * Shorthand method to add an event to the calendar.
     *
     * @param string  $text              The text describing the event.
     * @param boolean $sendNotifications Whether to send notifications about
     *                                   the creation of the event.
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function addQuickEvent($text = '', $sendNotifications = false)
    {
        $this->_quickEventCache[] = array($text, $sendNotifications);

        return $this;
    }

    /**
     * Add a default reminder to the calendar.
     * 
     * @param string  $method  The reminder method (email|sms|popup).
     * @param integer $minutes The number of minutes before the start of the event
     *                         when reminder should trigger.
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function addReminder($method = 'email', $minutes = null)
    {
        $reminder = Mage::getModel(
            'google/calendar_event_reminder', 
            array(
                'method'    => $method,
                'minutes'   => $minutes,
            )
        );

        $this->getDefaultReminders()->addItem($reminder);

        return $this;
    }

    /**
     * Get the ACL entries for the calendar.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Acl_Collection
     */
    public function getAcl()
    {
        if (!$this->_acl) {
            $this->_acl = Mage::getModel('google/calendar_acl')->getCollection()
                ->addFilter('calendar_id', $this->getId());
        }

        return $this->_acl;
    }

    /**
     * Get the color scheme.
     * 
     * @return array
     */
    public function getColorScheme()
    {
        if (!$this->_colorScheme) {
            $schemes = $this->_getResource()
                ->getColorSchemes('calendar');

            if (isset($schemes[$this->getColorId()])) {
                $this->_colorScheme = new Varien_Object($schemes[$this->getColorId()]);
            }
        }

        return $this->_colorScheme;
    }

    /**
     * Get all default calendar reminders.
     * 
     * @return Varien_Data_Collection
     */
    public function getDefaultReminders()
    {
        if ( !($this->getData('default_reminders') instanceof Varien_Data_Collection) ) {
            $this->setData('default_reminders', Mage::getModel('google/calendar_event_reminder')->getCollection());
        }

        return $this->getData('default_reminders');
    }

    /**
     * Get the events for this calendar.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Event_Collection
     */
    public function getEvents($timeMin = null, $timeMax = null)
    {
        if (!$this->_events) {
            // Use start of today if not given
            if (is_null($timeMin)) {
                $timeMin = date('Y-m-d 00:00:00', time());
            }

            // Use end of today if not given
            if (is_null($timeMax)) {
                $timeMax = date('Y-m-d 23:59:59', time());
            }

            $this->_events = Mage::getModel('google/calendar_event')->getCollection()
                ->addFilter('calendar_id', $this->getId())
                ->addFilter('time_min', $timeMin)
                ->addFilter('time_max', $timeMax);
        }

        return $this->_events;
    }

    /**
     * Get all calendar notifications.
     * 
     * @return Varien_Data_Collection
     */
    public function getNotificationSettings()
    {
        if ( !($this->getData('notification_settings') instanceof Varien_Data_Collection) ) {
            $this->setData('notification_settings', Mage::getModel('google/calendar_event_notification')->getCollection());
        }

        return $this->getData('notification_settings');
    }

    /**
     * Set the calendar notification settings. Enforces strict type.
     * 
     * @param Mymodules_Google_Model_Resource_Calender_Event_Notification_Collection $notifications The notifications collection.
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function setNotificationSettings(Mymodules_Google_Model_Resource_Calender_Event_Notification_Collection $notifications)
    {
        $this->setData('notification_settings', $notifications);

        return $this;
    }

    /**
     * Set the default calendar reminders. Enforces strict type.
     * 
     * @param Mymodules_Google_Model_Resource_Calender_Event_Reminder_Collection $reminders The reminders collection.
     *
     * @return Mymodules_Google_Model_Calendar
     */
    public function setDefaultReminders(Mymodules_Google_Model_Resource_Calender_Event_Reminder_Collection $reminders)
    {
        $this->setData('default_reminders', $reminders);

        return $this;
    }

    /**
     * Convert the data to an API-compatible object.
     *
     * @param string $type The API object type, accepts CalendarListEntry|Calendar.
     * 
     * @return mixed
     */
    public function toApiObject($type = 'Calendar')
    {
        // Re-build existing instance
        if ($this->_apiObject) {
            unset($this->_apiObject);
            $this->_apiObject = null;
        }

        if (is_null($this->_apiObject)) {
            $data           = Mage::getSingleton('google/resource_helper_calendar')->translateFields($this->getData());
            $objectClass    = "Google_Service_Calendar_{$type}";

            if (class_exists($objectClass)) {
                $this->_apiObject = new $objectClass($data);
            }

            unset($data);
        }

        return $this->_apiObject;
    }

}