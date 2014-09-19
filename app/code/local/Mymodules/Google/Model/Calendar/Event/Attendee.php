<?php

/**
 * Simple wrapper model for event attendees.
 *
 * PHP Version 5
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
 */

class Mymodules_Google_Model_Calendar_Event_Attendee 
    extends Mage_Core_Model_Abstract
{

    /* @var $_apiObject Google_Service_Calendar_EventAttendee */
    protected $_apiObject = null;

    /**
     * Local constructor.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar_event_attendee');
    }

    /**
     * Convert the data to an API-compatible object.
     * 
     * @return Google_Service_Calendar_EventAttendee
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

            $this->_apiObject = new Google_Service_Calendar_EventAttendee($data);

            unset($data);
        }

        return $this->_apiObject;
    }

}