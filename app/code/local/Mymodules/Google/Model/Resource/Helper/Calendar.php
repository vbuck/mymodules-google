<?php

/**
 * Google Calendar resource helper. Primary role is to
 * translate DB adapter operations into Google client
 * library method calls.
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

class Mymodules_Google_Model_Resource_Helper_Calendar
{

    /**
     * When no calendar ID is given, the default is
     * the primary calendar of the service account
     * used to make the request.
     */
    const DEFAULT_CALENDAR      = 'primary';

    protected $_argMappers      = array(
        'delete'    => array(
            'acl'           => 'mapDeleteArgAcl',
            'calendarList'  => 'mapDeleteArgCalendarList',
            'calendars'     => 'mapDeleteArgCalendars',
            'events'        => 'mapDeleteArgEvents',
            // No API delete actions for colors, freebusy, or settings
        ),
        'fetchAll'  => array(
            'acl'           => 'mapAllArgAcl',
            'calendarList'  => 'mapAllArgCalendarList',
            'calendars'     => 'mapAllArgCalendars',
            'colors'        => 'mapAllArgColors',
            'events'        => 'mapAllArgEvents',
            'freebusy'      => 'mapAllArgFreebusy',
            'settings'      => 'mapAllArgSettings',
        ),
        'fetchOne'  => array(
            'acl'           => 'mapOneArgAcl',
            'calendarList'  => 'mapOneArgCalendarList',
            'calendars'     => 'mapOneArgCalendars',
            'colors'        => 'mapOneArgColors',
            'events'        => 'mapOneArgEvents',
            'freebusy'      => 'mapOneArgFreebusy',
            'settings'      => 'mapOneArgSettings',
        ),
        'insert'    => array(
            'acl'           => 'mapInsertArgAcl',
            'calendarList'  => 'mapInsertArgCalendarList',
            'calendars'     => 'mapInsertArgCalendars',
            'events'        => 'mapInsertArgEvents',
            // No API insert actions for colors, freebusy, or settings
        ),
        'update'    => array(
            'acl'           => 'mapUpdateArgAcl',
            'calendarList'  => 'mapUpdateArgCalendarList',
            'calendars'     => 'mapUpdateArgCalendars',
            'events'        => 'mapUpdateArgEvents',
            // No API update actions for colors, freebusy, or settings
        ),
    );

    protected $_deleteMap     = array(
        'acl'           => 'delete',
        'calendarList'  => 'delete',
        'calendars'     => 'delete',
        'events'        => 'delete',
    );

    protected $_fetchAllMap     = array(
        'acl'           => 'listAcl',
        'calendarList'  => 'listCalendarList',
        'calendars'     => 'get',
        'colors'        => 'get',
        'events'        => 'listEvents',
        'freebusy'      => 'query',
        'settings'      => 'listSettings',
    );

    protected $_fetchOneMap     = array(
        'acl'           => 'get',
        'calendarList'  => 'get',
        'calendars'     => 'get',
        'colors'        => 'get',
        'events'        => 'get',
        'freebusy'      => 'query',
        'settings'      => 'get',
    );

    protected $_insertMap       = array(
        'acl'           => 'insert',
        'calendarList'  => 'insert',
        'calendars'     => 'insert',
        'events'        => 'insert',
    );

    protected $_updateMap       = array(
        'acl'           => 'patch',
        'calendarList'  => 'patch',
        'calendars'     => 'patch',
        'events'        => 'patch',
    );

    /**
     * Convert a datetime string for API use.
     *
     * Input must always be in UTC, it will be adjusted by input
     * timezone. If an output timezone is given, the date will
     * be adjusted to match in the output.
     *
     * Example:
     *
     *  > 2014-06-01 16:00:00
     *  > America/New_York in (4:00 PM EST)
     *  > UTC out
     *
     *  < 2014-06-01 20:00:00 (8:00 PM GMT)
     *
     * This method makes it convenient to express dates in UTC which
     * imply the target timezone for a calendar, without having
     * to specify their timezone offsets.
     * 
     * @param string|integer $date           Input datetime in 'Y-m-d h:i:s' UTC format.
     * @param string         $outputFormat   The output datetime format.
     * @param string         $inputTimezone  The input timezone.
     * @param string         $outputTimezone The destination timezone.
     * 
     * @return string   
     */
    public function convertDatetime(
        $date, 
        $outputFormat = null, 
        $inputTimezone = 'UTC', 
        $outputTimezone = null
    )
    {
        $date = new Zend_Date(strtotime($date));

        $date->setTimezone($inputTimezone);

        // Forces re-calcuation of current time
        if ($outputTimezone && $inputTimezone != $outputTimezone) {
            $offsetFrom = -($date->getGmtOffset());

            $date->setTimezone($outputTimezone);

            $offsetTo = -($date->getGmtOffset());

            $date->addTimestamp( ($offsetTo - $offsetFrom) );
        }

        if (!$outputFormat) {
            $outputFormat = Zend_Date::RFC_3339;
        }

        return $date->toString($outputFormat);
    }

    /**
     * Get the deleted mapped entry for a resource.
     * 
     * @param string $resource The requested API resource.
     * 
     * @return mixed
     */
    public function getDeleteMap($resource = '')
    {
        return $this->getMap('delete', $resource);
    }

    /**
     * Get the fetch-all mapped entry for a resource.
     * 
     * @param string $resource The requested API resource.
     * 
     * @return mixed
     */
    public function getFetchAllMap($resource = '')
    {
        return $this->getMap('fetchAll', $resource);
    }

    /**
     * Get the fetch-one mapped entry for a resource.
     * 
     * @param string $resource The requested API resource.
     * 
     * @return mixed
     */
    public function getFetchOneMap($resource = '')
    {
        return $this->getMap('fetchOne', $resource);
    }

    /**
     * Get the insert mapped entry for a resource.
     * 
     * @param string $resource The requested API resource.
     * 
     * @return mixed
     */
    public function getInsertMap($resource = '')
    {
        return $this->getMap('insert', $resource);
    }

    /**
     * Get a mapped entry for a resource.
     * 
     * @param string $type     The operation type (fetchOne|fetchAll|etc).
     * @param string $resource The requested API resource.
     * 
     * @return mixed
     */
    public function getMap($type, $resource = '') {
        $map = "_{$type}Map";

        if (empty($this->$map)) {
            return false;
        }

        $entries = $this->$map;

        if (isset($entries[$resource])) {
            return $entries[$resource];
        } else if($resource != '') {
            return false;
        }

        return $entries;
    }

    /**
     * Prepare the parameters correctly for a client API call.
     * 
     * @param array|Varien_Object  $parameters The request parameters or object.
     * @param string               $type       The operation type (fetchOne|fetchAll|etc).
     * @param string               $resource   The requested API resource.
     * 
     * @return array
     */
    public function getParameters($parameters = array(), $type, $resource)
    {
        // Convert to Varien_Object as convenience through mapping process
        if (is_array($parameters)) {
            $parameters = new Varien_Object($parameters);
        }

        if (isset($this->_argMappers[$type][$resource])) {
            return call_user_func(array($this, $this->_argMappers[$type][$resource]), $parameters);
        }

        return $parameters;
    }

    /**
     * Get the update mapped entry for a resource.
     * 
     * @param string $resource The requested API resource.
     * 
     * @return mixed
     */
    public function getUpdateMap($resource = '')
    {
        return $this->getMap('update', $resource);
    }

    /**
     * Parameter map-all method for client API ACL resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/acl/list
     * @see   Google_Service_Calendar_Acl_Resource::list
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgAcl(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();

        $calendarId = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Translate parameters for API call before going out
        return array($calendarId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-delete method for client API ACL resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/acl/delete
     * @see   Google_Service_Calendar_Acl_Resource::delete
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapDeleteArgAcl(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();

        $calendarId = null;
        $ruleId     = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        if (isset($parameters['rule_id'])) {
            $ruleId = $parameters['rule_id'];
            unset($parameters['rule_id']);
        }

        // Translate parameters for API call before going out
        return array($calendarId, $ruleId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-insert method for client API ACL resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/acl/insert
     * @see   Google_Service_Calendar_Acl_Resource::insert
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapInsertArgAcl(Varien_Object $parameters)
    {
        $calendarId = null;

        if ( !($calendarId = $parameters->getCalendarId()) ) {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Convert parameters to Google_Service_Calendar_AclRule
        return array($calendarId, $parameters->toApiObject(), array());
    }

    /**
     * Parameter map-one method for client API ACL resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/acl/get
     * @see   Google_Service_Calendar_Acl_Resource::get
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgAcl(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;
        $ruleId     = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        if (isset($parameters['rule_id'])) {
            $ruleId = $parameters['rule_id'];
            unset($parameters['rule_id']);
        }

        // Translate parameters for API call before going out
        return array($calendarId, $ruleId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-update method for client API ACL resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/acl/update
     * @see   Google_Service_Calendar_Acl_Resource::update
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapUpdateArgAcl(Varien_Object $parameters)
    {
        $calendarId = null;
        $ruleId     = $parameters->getId();

        if ( !($calendarId = $parameters->getCalendarId()) ) {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Convert parameters to Google_Service_Calendar_AclRule
        return array($calendarId, $ruleId, $parameters->toApiObject(), array());
    }

    /**
     * Parameter map-all method for client API calendarList resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendarList/list
     * @see   Google_Service_Calendar_CalendarList_Resource::list
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgCalendarList(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();

        // Translate parameters for API call before going out
        return array($this->translateFields($parameters));
    }

    /**
     * Parameter map-delete method for client API calendarList resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendarList/delete
     * @see   Google_Service_Calendar_CalendarList_Resource::delete
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapDeleteArgCalendarList(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Translate parameters for API call before going out
        return array($calendarId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-insert method for client API calendarList resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendars/insert
     * @see   Google_Service_Calendar_CalendarList_Resource::insert
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapInsertArgCalendarList(Varien_Object $parameters)
    {
        // Convert parameters to Google_Service_Calendar_CalendarListEntry
        return array($parameters->toApiObject('CalendarListEntry'), array());
    }

    /**
     * Parameter map-one method for client API calendarList resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendarList/get
     * @see   Google_Service_Calendar_CalendarList_Resource::get
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgCalendarList(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Translate parameters for API call before going out
        return array($calendarId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-update method for client API calendarList resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendarList/update
     * @see   Google_Service_Calendar_CalendarList_Resource::update
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapUpdateArgCalendarList(Varien_Object $parameters)
    {
        $optParams = array();

        if ( ($parameters->getColorRgbFormat()) ) {
            $optParams['colorRgbFormat'] = true;
        }

        // Convert parameters to Google_Service_Calendar_CalendarListEntry
        return array($parameters->getId(), $parameters->toApiObject('CalendarListEntry'), $optParams);
    }

    /**
     * Parameter map-all method for client API calendars resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendarList/list
     * @see   Google_Service_Calendar_CalendarList_Resource::list
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgCalendars(Varien_Object $parameters)
    {
        // No "list" equivalent in Calendars resource
        // Forward as CalendarList resource request
        return $this->mapAllArgCalendarList($parameters);
    }

    /**
     * Parameter map-delete method for client API calendars resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendars/delete
     * @see   Google_Service_Calendar_Calendars_Resource::delete
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapDeleteArgCalendars(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Translate parameters for API call before going out
        return array($calendarId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-insert method for client API calendars resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendars/insert
     * @see   Google_Service_Calendar_Calendars_Resource::insert
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapInsertArgCalendars(Varien_Object $parameters)
    {
        // Convert parameters to Google_Service_Calendar_Calendar
        return array($parameters->toApiObject('Calendar'), array());
    }

    /**
     * Parameter map-one method for client API calendars resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendars/get
     * @see   Google_Service_Calendar_Calendars_Resource::get
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgCalendars(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Translate parameters for API call before going out
        return array($calendarId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-update method for client API calendars resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/calendars/update
     * @see   Google_Service_Calendar_Calendars_Resource::update
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapUpdateArgCalendars(Varien_Object $parameters)
    {
        // Convert parameters to Google_Service_Calendar_Calendar
        return array($parameters->getId(), $parameters->toApiObject('Calendar'), array());
    }

    /**
     * Parameter map-all method for client API colors resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/colors/get
     * @see   Google_Service_Calendar_Colors_Resource::get
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgColors(Varien_Object $parameters)
    {
        // "list" equivalent in Colors resource is "get"
        // Forward as Colors::get resource request
        return $this->mapOneArgColors($parameters);
    }

    /**
     * Parameter map-one method for client API colors resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/colors/get
     * @see   Google_Service_Calendar_Colors_Resource::get
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgColors(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();

        // Translate parameters for API call before going out
        return array($this->translateFields($parameters));
    }

    /**
     * Parameter map-all method for client API events resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/events/list
     * @see   Google_Service_Calendar_Events_Resource::list
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgEvents(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Translate parameters for API call before going out
        return array($calendarId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-delete method for client API events resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/events/delete
     * @see   Google_Service_Calendar_Events_Resource::delete
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapDeleteArgEvents(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;
        $eventId    = null;

        if (isset($parameters['calendarId'])) {
            $calendarId = $parameters['calendarId'];
            unset($parameters['calendarId']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        if (isset($parameters['eventId'])) {
            $eventId = $parameters['eventId'];
            unset($parameters['eventId']);
        }

        // Translate parameters for API call before going out
        return array($calendarId, $eventId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-insert method for client API events resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/events/insert
     * @see   Google_Service_Calendar_Events_Resource::insert
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapInsertArgEvents(Varien_Object $parameters)
    {
        $calendarId = null;

        if ( !($calendarId = $parameters->getCalendarId()) ) {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Convert parameters to Google_Service_Calendar_Event
        return array($calendarId, $parameters->toApiObject(), array());
    }

    /**
     * Parameter map-one method for client API events resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/events/get
     * @see   Google_Service_Calendar_Events_Resource::get
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgEvents(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $calendarId = null;
        $eventId    = null;

        if (isset($parameters['calendar_id'])) {
            $calendarId = $parameters['calendar_id'];
            unset($parameters['calendar_id']);
        } else {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        if (isset($parameters['event_id'])) {
            $eventId = $parameters['event_id'];
            unset($parameters['event_id']);
        }
        
        // Translate parameters for API call before going out
        return array($calendarId, $eventId, $this->translateFields($parameters));
    }

    /**
     * Parameter map-update method for client API events resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/events/update
     * @see   Google_Service_Calendar_Events_Resource::update
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapUpdateArgEvents(Varien_Object $parameters)
    {
        $calendarId = null;

        if ( !($calendarId = $parameters->getCalendarId()) ) {
            $calendarId = self::DEFAULT_CALENDAR;
        }

        // Convert parameters to Google_Service_Calendar_Event
        return array($calendarId, $parameters->getId(), $parameters->toApiObject(), array());
    }

    /**
     * Parameter map-all method for client API freebusy resource.
     *
     * If including a post_body parameter as an array, expected format is:
     *
     * {
     *     "timeMin"              : datetime,
     *     "timeMax"              : datetime,
     *     "timeZone"             : string,
     *     "groupExpansionMax"    : integer,
     *     "calendarExpansionMax" : integer,
     *     "items"                : [
     *         {
     *             "id" : string
     *         }
     *     ]
     * }
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/freebusy/query
     * @see   Google_Service_Calendar_Freebusy_Resource::query
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgFreebusy(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $postBody   = null;

        if (isset($parameters['post_body'])) {
            $postBody = $parameters['post_body'];

            if (is_array($postBody)) {
                $postBody = Mage::helper('core')->jsonEncode($postBody);
            }

            unset($parameters['post_body']);
        }

        // Translate parameters for API call before going out
        return array($postBody, $this->translateFields($parameters));
    }

    /**
     * Parameter map-one method for client API freebusy resource.
     *
     * @see   https://developers.google.com/google-apps/calendar/v3/reference/freebusy/query
     * @see   Google_Service_Calendar_Freebusy_Resource::query
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgFreebusy(Varien_Object $parameters)
    {
        // No "get" equivalent for Freebusy resource
        // Forward as Freebusy::query resource request.
        return $this->mapAllArgFreebusy($parameters);
    }

    /**
     * Parameter map-all method for client API settings resource.
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapAllArgSettings(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();

        // Translate parameters for API call before going out
        return array($this->translateFields($parameters));
    }

    /**
     * Parameter map-one method for client API settings resource.
     * 
     * @param Varien_Object $parameters The request object.
     * 
     * @return array
     */
    public function mapOneArgSettings(Varien_Object $parameters)
    {
        // Immediately flatten, not using a model here
        $parameters = $parameters->getData();
        $setting    = null;

        if (isset($parameters['setting'])) {
            $setting = $parameters['setting'];
            unset($parameters['setting']);
        }

        // Translate parameters for API call before going out
        return array($setting, $this->translateFields($parameters));
    }

    /**
     * Translate Magento-style field names to proper API field names.
     *
     * @see [uc_words] app/code/core/Mage/Core/functions.php:108
     * 
     * @param array $data The parameters array.
     * 
     * @return array
     */
    public function translateFieldNames($data)
    {
        $_data = array();
        foreach ($data as $key => $value) {
            // eg: calendar_id = calendarId
            $_data[lcfirst(uc_words($key, ''))] = $value;
        }

        unset($data);

        return $_data;
    }

    /**
     * Translate parameters for API call.
     * 
     * @param array $data The parameters array.
     * 
     * @return array
     */
    public function translateFields($data)
    {
        $data = $this->translateFieldNames($data);

        return $data;
    }

}