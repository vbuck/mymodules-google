<?php

/**
 * Google Calendar event resource model.
 *
 * PHP Version 5
 *
 * @todo      Replace recurring events with instance data on after load.
 *            Will also affect collection ordering.
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
 */

class Mymodules_Google_Model_Resource_Calendar_Event 
    extends Mymodules_Google_Model_Resource_Abstract
{

    /**
     * Expand extended model data into collections and objects.
     *
     * @param Mage_Core_Model_Abstract $model The event model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $model)
    {
        $this->setAttendees($model)
            ->setCreator($model)
            ->setExtendedProperties($model)
            ->setGadget($model)
            ->setOrganizer($model)
            ->setReminders($model)
            ->setSource($model)
            ->setTimes($model)
            // Set last to access time data
            ->setRecurrence($model);

        // Reset changed state
        $model->setHasChanged(false);

        return $this;
    }

    /**
     * Get the read connection.
     * 
     * @return Mymodules_Google_Model_Calendar_Service
     */
    protected function _getReadAdapter()
    {
        return Mage::getSingleton('google/calendar_service');
    }

    /**
     * Get the write connection.
     * 
     * @return Mymodules_Google_Model_Calendar_Service
     */
    protected function _getWriteAdapter()
    {
        return Mage::getSingleton('google/calendar_service');
    }

    /**
     * Delete a model. Deleted events receive the status 'cancelled.'
     * 
     * @param  Mage_Core_Model_Abstract $model The calendar event model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function delete(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeDelete($model);

        $this->_getWriteAdapter()
            ->delete(
                'events', 
                array(
                    // Optimization on calendar ID
                    // @see Mymodules_Google_Model_Resource_Calendar_Event::load
                    'calendar_id'   => $model->getCalendarId(),
                    'event_id'      => $model->getId(),
                )
            );

        $this->_afterDelete($model);

        return $this;
    }

    /**
     * Expose read connection to collections.
     *
     * @return Mymodules_Google_Model_Calendar_Service
     */
    public function getReadConnection()
    {
        return $this->_getReadAdapter();
    }

    /**
     * Load the model data.
     * 
     * @param Mage_Core_Model_Abstract  $model The calendar event model.
     * @param array                     $value An indexed array [calendar ID, event ID].
     * @param string                    $field The ID field by which to load.
     * 
     * @return  Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function load(Mage_Core_Model_Abstract $model, $value, $field = null)
    {
        $this->_beforeLoad($model);

        if ($value) {
            $data = $this->_getReadAdapter()
                ->fetchRow('events', array(
                    'calendar_id'   => $value[0],
                    'event_id'      => $value[1]
                ));

            if ($data) {
                $model->setData($data);

                // Events contain no reference to their parent calendar,
                // so we can store it post-load for easy reference later.
                $model->setCalendarId($value[0]);
            }
        }

        $this->_afterLoad($model);

        return $this;
    }

    /**
     * Expand the attendee data as a collection.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setAttendees(Mage_Core_Model_Abstract $model)
    {
        $attendees  = $model->getData('attendees');
        $items      = Mage::getModel('google/calendar_event_attendee')->getCollection();

        if (is_array($attendees)) {
            foreach ($attendees as $item) {
                $attendee = Mage::getModel('google/calendar_event_attendee', $item);

                $items->addItem($attendee);
            }
        }

        $model->setData('attendees', $items);
        unset($attendees);

        return $this;
    }

    /**
     * Expand the creator data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setCreator(Mage_Core_Model_Abstract $model)
    {
        $creator = $model->getData('creator');

        if (is_array($creator)) {
            $model->setData('creator', Mage::getModel('google/calendar_event_creator', $creator));
        }

        unset($creator);

        return $this;
    }

    /**
     * Expand the extended properties data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setExtendedProperties(Mage_Core_Model_Abstract $model)
    {
        $extendedProperties = $model->getData('extendedProperties');

        if (is_array($extendedProperties)) {
            $extendedProperty = Mage::getModel('google/calendar_event_extendedproperty');

            if (isset($extendedProperties['private'])) {
                $extendedProperty->getPrivate()->addData($extendedProperties['private']);
            }

            if (isset($extendedProperties['shared'])) {
                $extendedProperty->getShared()->addData($extendedProperties['shared']);
            }

            $model->setData('extended_properties', $extendedProperty);
        }

        unset($extendedProperties);

        return $this;
    }

    /**
     * Expand the gadget data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setGadget(Mage_Core_Model_Abstract $model)
    {
        $gadget = $model->getData('gadget');

        if (is_array($gadget)) {
            $model->setData('gadget', Mage::getModel('google/calendar_event_gadget', $gadget));
        }

        unset($gadget);

        return $this;
    }

    /**
     * Expand the organizer data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setOrganizer(Mage_Core_Model_Abstract $model)
    {
        $organizer = $model->getData('organizer');

        if (is_array($organizer)) {
            $model->setData('organizer', Mage::getModel('google/calendar_event_organizer', $organizer));
        }

        unset($organizer);

        return $this;
    }

    /**
     * Expand the recurrence data as an array.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setRecurrence(Mage_Core_Model_Abstract $model)
    {
        $recurrence = $model->getData('recurrence');
        $items      = array();

        if (is_array($recurrence)) {
            foreach ($recurrence as $item) {
                $items[] = $item;
            }
        }

        $model->setData('recurrence', $items);
        unset($items);

        return $this;
    }

    /**
     * Expand the reminder data as a collection.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setReminders(Mage_Core_Model_Abstract $model)
    {
        $reminders  = $model->getData('reminders');
        $items      = Mage::getModel('google/calendar_event_reminder')->getCollection();

        if (isset($reminders['overrides']) && is_array($reminders['overrides'])) {
            foreach ($reminders['overrides'] as $item) {
                $reminder = Mage::getModel('google/calendar_event_reminder', $item);

                $items->addItem($reminder);
            }
        }

        if (isset($reminders['use_default'])) {
            $items->setUseDefault($reminders['use_default']);
        }

        $model->setData('reminders', $items);
        unset($reminders);

        return $this;
    }

    /**
     * Expand the source data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setSource(Mage_Core_Model_Abstract $model)
    {
        $source = $model->getData('source');

        if (is_array($source)) {
            $model->setData('source', Mage::getModel('google/calendar_event_source', $source));
        }

        unset($source);

        return $this;
    }

    /**
     * Expand the time data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function setTimes(Mage_Core_Model_Abstract $model)
    {
        $created = $model->getData('created');

        // Not given as an array
        if ( !($created instanceof Mymodules_Google_Model_Calendar_Event_Datetime) ) {
            $model->setData(
                'created', 
                Mage::getModel(
                    'google/calendar_event_datetime', 
                    array(
                        'date_time' => $created,
                        'time_zone' => $model->getTimeZone(),
                    )
                )
            );
        }

        $updated = $model->getData('updated');

        // Not given as an array
        if ( !($updated instanceof Mymodules_Google_Model_Calendar_Event_Datetime) ) {
            $model->setData(
                'updated', 
                Mage::getModel(
                    'google/calendar_event_datetime', 
                    array(
                        'date_time' => $updated,
                        'time_zone' => $model->getTimeZone(),
                    )
                )
            );
        }

        $originalStartTime = $model->getData('original_start_time');

        if (is_array($originalStartTime)) {
            $model->setData('original_start_time', Mage::getModel('google/calendar_event_datetime', $originalStartTime));
        }

        unset($originalStartTime);

        $start = $model->getData('start');

        if (is_array($start)) {
            $model->setData('start', Mage::getModel('google/calendar_event_datetime', $start));
        }
        
        unset($start);

        $end = $model->getData('end');

        if (is_array($end)) {
            $model->setData('end', Mage::getModel('google/calendar_event_datetime', $end));
        }
        
        unset($end);

        return $this;
    }

    /**
     * Save the model data.
     * 
     * @param Mage_Core_Model_Abstract $model The calendar event model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Event
     */
    public function save(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeSave($model);

        if (!is_null($model->getId())) {
            $this->_getWriteAdapter()
                ->update('events', $model);
        } else {
            $results = $this->_getWriteAdapter()
                ->insert('events', $model);

            if (is_array($results)) {
                $model->setId($results['id'])
                    ->setEtag($results['etag'])
                    ->setKind($results['kind']);
            }

            unset($results);
        }

        $this->_afterSave($model);

        return $this;
    }

}