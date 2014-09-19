<?php

/**
 * Google Calendar event model.
 *
 * PHP Version 5
 *
 * @see       https://developers.google.com/google-apps/calendar/v3/reference/events
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

class Mymodules_Google_Model_Calendar_Event 
    extends Mage_Core_Model_Abstract
{

    /* @var $_apiObject Google_Service_Calendar_Event */
    protected $_apiObject = null;
    /* @var $_calendar Mymodules_Google_Model_Calendar */
    protected $_calendar;
    /* @var $_colorScheme array */
    protected $_colorScheme;
    /**
     * This managed flag helps to reduce overhead on
     * calendar and event collection save operations.
     * Only those events which have been changed will
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
        $this->_init('google/calendar_event');
    }

    /**
     * Add an attendee to the event.
     * 
     * @param array|string $data The attendee data.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function addAttendee($data = array())
    {
        $attendee = null;

        if (is_string($data)) {
            $data = array('email' => $data);
        }

        if (is_array($data)) {
            $attendee = Mage::getModel('google/calendar_event_attendee', $data);
        } else if ($data instanceof Varien_Object) {
            $attendee = $data;
        }

        $this->getAttendees()->addItem($attendee);

        $this->setHasChanged(true);

        return $this;
    }

    /**
     * Add recurrence to event. If left blank, the default
     * recurrence will be every day beginning from the event
     * start date.
     *
     * @see   http://www.ietf.org/rfc/rfc2445
     * @see   https://developers.google.com/google-apps/calendar/concepts#recurring_events
     * @see   For full recurrence control, use Mymodules_Google_Model_Calendar_Event::addRecurrenceByObject
     * 
     * @param string $frequency The recurrence frequency, can be:
     *                              secondly
     *                              minutely
     *                              hourly
     *                              daily
     *                              weekly
     *                              monthly
     *                              yearly
     * @param string $until     The end datetime string.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function addRecurrence($frequency = 'daily', $until = null)
    {
        $recurrence = Mage::getModel('google/calendar_event_recurrence');

        // Attach to event for access to parent data
        $recurrence->setEvent($this);

        $recurrence->getBuilder()
            ->startDate(
                new DateTime(
                    $this->getStart()->getDateTime(), 
                    new DateTimeZone($this->getTimeZone())
                )
            );

        if ($until) {
            $recurrence->getBuilder()
                ->until(new DateTime($until, new DateTimeZone($this->getTimeZone())));
        }

        $recurrence->getBuilder()
            ->freq($frequency);

        return $this->addRecurrenceByObject($recurrence);
    }

    /**
     * Add event recurrence by object.
     * 
     * @param Mymodules_Google_Model_Calendar_Event_Recurrence $recurrence The recurrence model.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function addRecurrenceByObject(Mymodules_Google_Model_Calendar_Event_Recurrence $recurrence)
    {
        $items = $this->getRecurrence();

        $items[] = $recurrence->assemble();

        $this->setData('recurrence', $items);
        unset($items);
        unset($recurrence);

        return $this;
    }

    /**
     * Add event recurrence by RFC 2445 RRULE.
     *
     * @see   http://www.ietf.org/rfc/rfc2445
     * 
     * @param string $rrule The RRULE definition.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function addRecurrenceByRrule($rrule)
    {
        $recurrence = Mage::getModel('google/calendar_event_recurrence');

        $recurrence->getBuilder()
            ->rrule($rrule);

        return $this->addRecurrenceByObject($recurrence);
    }

    /**
     * Add a reminder to the event.
     * 
     * @param string  $method  The reminder method (email|sms|popup).
     * @param integer $minutes The number of minutes before the start of the event
     *                         when reminder should trigger.
     *
     * @return Mymodules_Google_Model_Calendar_Event
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

        $this->getReminders()
            ->setUseDefault(false)
            ->addItem($reminder);

        $this->setHasChanged(true);

        return $this;
    }

    /**
     * Get an attendee.
     * 
     * @param string $email The attendee email address.
     *
     * @return Varien_Object|null
     */
    public function getAttendeeByEmail($email)
    {
        return $this->getAttendees()
            ->getItemByColumnValue('email', $email);
    }

    /**
     * Get all event attendees.
     * 
     * @return Varien_Data_Collection
     */
    public function getAttendees()
    {
        if ( !($this->getData('attendees') instanceof Varien_Data_Collection) ) {
            $this->setData('attendees', Mage::getModel('google/calendar_event_attendee')->getCollection());
        }

        return $this->getData('attendees');
    }

    /**
     * Get the calendar model to which the event belongs.
     * 
     * @return Mymodules_Google_Model_Calendar
     */
    public function getCalendar()
    {
        if (!$this->_calendar) {
            $calendarId = null;
            
            if ($this->getCalendarId()) {
                $calendarId = $this->getCalendarId();
            } else if ($this->getOrganizer()) {
                // Organizer e-mail should always be the calendar ID to which the event belongs
                $calendarId = $this->getOrganizer()->getEmail();
            }

            if (!is_null($calendarId)) {
                $this->_calendar = Mage::getModel('google/calendar')->load($calendarId);
            }
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
     * Get the color scheme.
     * 
     * @return array
     */
    public function getColorScheme()
    {
        if (!$this->_colorScheme) {
            $schemes = $this->getCalendar()
                ->getResource()
                ->getColorSchemes('event');

            if (isset($schemes[$this->getColorId()])) {
                $this->_colorScheme = new Varien_Object($schemes[$this->getColorId()]);
            }
        }

        return $this->_colorScheme;
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
     * Get the recurrence rule collection.
     * 
     * @return Varien_Data_Collection
     */
    public function getRecurrence()
    {
        if (!is_array($this->getData('recurrence'))) {
            $this->setData('recurrence', array());
        }

        return $this->getData('recurrence');
    }

    /**
     * Get all event reminders.
     * 
     * @return Varien_Data_Collection
     */
    public function getReminders()
    {
        if ( !($this->getData('reminders') instanceof Varien_Data_Collection) ) {
            $this->setData('reminders', Mage::getModel('google/calendar_event_reminder')->getCollection());
        }

        return $this->getData('reminders');
    }

    /**
     * Get the time zone of the calendar.
     * 
     * @return string
     */
    public function getTimeZone()
    {
        return $this->getCalendar()->getTimeZone();
    }

    /**
     * Remove an attendee.
     * 
     * @param string $email The attendee email address.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function removeAttendeeByEmail($email)
    {
        $attendee = $this->getAttendeeByEmail($email);

        if (!is_null($attendee)) {
            $this->getAttendees()
                ->removeItemByKey($attendee->getId());
        }

        return $this;
    }

    /**
     * Save implementation.
     * 
     * @return Mymodules_Google_Model_Calendar_Event
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
     * Set the event attendees. Enforces strict type.
     * 
     * @param Mymodules_Google_Model_Resource_Calender_Event_Attendee_Collection $attendees The attendees collection.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setAttendees(Mymodules_Google_Model_Resource_Calender_Event_Attendee_Collection $attendees)
    {
        $this->setData('attendees', $attendees);

        return $this;
    }

    /**
     * Set the parent calendar model.
     * 
     * @param Mymodules_Google_Model_Calendar $calendar The calendar model.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setCalendar(Mymodules_Google_Model_Calendar $calendar)
    {
        $this->_calendar = $calendar;

        $this->setHasChanged(true);

        return $this;
    }

    /**
     * Set the creator.
     *
     * @param string|array $input The input email or array of settings.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setCreator($input)
    {
        // Convert strings to email part
        if (!is_array($input)) {
            $input = array('email' => $input);
        }

        $this->setData('creator', $input);

        // Re-initialize as object via resource model
        $this->_getResource()
            ->setCreator($this);

        return $this;
    }


    /**
     * Data setter implementation.
     * 
     * @param string $key   The data key.
     * @param mixed  $value The data value.
     *
     * @return Mymodules_Google_Model_Calendar_Event
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
     * Set the end time.
     * 
     * @param string|array $input The input datetime or array of settings.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setEnd($input)
    {
        return $this->setTimeByType('end', $input);
    }

    /**
     * Set the event extended properties. Enforces strict type.
     *
     * @param Mymodules_Google_Model_Calendar_Event_Extendedproperty $properties The extended properties.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setExtendedProperties(Mymodules_Google_Model_Calendar_Event_Extendedproperty $properties)
    {
        $this->setData('extended_properties', $properties);

        return $this;
    }

    /**
     * Set the event gadget. Enforces strict type.
     *
     * @param Mymodules_Google_Model_Calendar_Event_Gadget $gadget The extended properties.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setGadget(Mymodules_Google_Model_Calendar_Event_Gadget $gadget)
    {
        $this->setData('gadget', $gadget);

        return $this;
    }

    /**
     * Set the data changed flag
     * 
     * @param boolean $state The changed state.
     *
     * @return Mymodules_Google_Model_Calendar_Event
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
     * Set the organizer.
     *
     * @param string|array $input The input email or array of settings.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setOrganizer($input)
    {
        // Convert strings to email part
        if (!is_array($input)) {
            $input = array('email' => $input);
        }

        $this->setData('organizer', $input);

        // Re-initialize as object via resource model
        $this->_getResource()
            ->setOrganizer($this);

        return $this;
    }

    /**
     * Set the original start time.
     * 
     * @param string|array $input The input datetime or array of settings.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setOriginalStartTime($input)
    {
        return $this->setTimeByType('original_start_time', $input);
    }

    /**
     * Set the event reminders. Enforces strict type.
     * 
     * @param Mymodules_Google_Model_Resource_Calender_Event_Reminder_Collection $attendees The attendees collection.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setReminders(Mymodules_Google_Model_Resource_Calender_Event_Reminder_Collection $reminders)
    {
        $this->setData('reminders', $reminders);

        return $this;
    }

    /**
     * Set the event source. Enforces strict type.
     *
     * @param Mymodules_Google_Model_Calendar_Event_Source $source The extended properties.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setSource(Mymodules_Google_Model_Calendar_Event_Source $source)
    {
        $this->setData('source', $source);

        return $this;
    }

    /**
     * Set the start time.
     * 
     * @param string|array $input The input datetime or array of settings.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setStart($input)
    {
        return $this->setTimeByType('start', $input);
    }

    /**
     * Set the start time.
     *
     * @param string       $type  The time part to set.
     * @param string|array $input The input datetime or array of settings.
     *
     * @return Mymodules_Google_Model_Calendar_Event
     */
    public function setTimeByType($type = 'start', $input)
    {
        // Convert strings to datetime part
        if (!is_array($input)) {
            $input = array(
                'date_time' => $input,
                'time_zone' => $this->getTimeZone(),
            );
        }

        // Catch missing time zone part
        if (!isset($input['time_zone'])) {
            $input['time_zone'] = $this->getTimeZone();
        }

        $this->setData($type, $input);

        // Re-initialize as object via resource model
        $this->_getResource()
            ->setTimes($this);

        return $this;
    }

    /**
     * Convert the data to an API-compatible object.
     * 
     * @return Google_Service_Calendar_Event
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

            $this->_apiObject = new Google_Service_Calendar_Event($data);

            unset($data);
        }

        return $this->_apiObject;
    }

}