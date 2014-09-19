<?php

/**
 * Google Calendar resource model version 3.
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

class Mymodules_Google_Model_Resource_Calendar 
    extends Mymodules_Google_Model_Resource_Abstract
{

    protected $_cache                   = array(
        'colors'    => array(
            'calendar'  => null,
            'event'     => null
        ),
        'freebusy'  => null
    );
    protected $_loadExtendedDataFlag    = false;

    /**
     * Local constructor.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function _construct() 
    {
        // By default, extended [calendarList resource] data is loaded.
        // If looking to save memory, you can set this flag to false.
        // Example of extended data would be the calendar description.
        $this->setLoadExtendedDataFlag(true);

        return $this;
    }

    /**
     * Expand extended model data into collections and objects.
     *
     * @param Mage_Core_Model_Abstract $model The calendar model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $model)
    {
        $this->setReminders($model)
            ->setNotifications($model);

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
     * Write an item to the resource cache.
     * 
     * @param string $path       The cache path.
     * @param mixed  $data       The data to cache.
     * @param mixed  $cachedItem The referenced item at the current level.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    protected function _setCache($path = '', $data = null, &$cachedItem = null)
    {
        $parts  = explode('/', $path);
        $part   = array_shift($parts);

        if ($path && !$cachedItem) {
            $cachedItem = &$this->_cache;
        }

        if (
            count($parts) && 
            is_array($cachedItem) && 
            array_key_exists($part, $cachedItem)
        )
        {
            return $this->_setCache(implode('/', $parts), $data, $cachedItem[$part]);
        } else {
            $cachedItem[$part] = $data;
        }

        return $this;
    }

    /**
     * Add ownership to a calendar. This method solves the problem
     * presented by service accounts when trying to create secondary
     * calendars on behalf of other users.
     *
     * @see    https://groups.google.com/forum/#!topic/google-calendar-api/IZOnr79a-jk
     * 
     * @param  Mage_Core_Model_Abstract $model The calendar model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    protected function _setOwnership(Mage_Core_Model_Abstract $model)
    {
        if ( ($target = $model->getOwnership()) ) {
            $acl = Mage::getModel('google/calendar_acl')
                ->setCalendarId($model->getId())
                ->setRole('owner')
                ->setScope('user', $target)
                ->save();

            // Clear when done
            $model->unsOwnership();
        }

        return $this;
    }

    /**
     * Shorthand method to add an event to a calendar.
     *
     * @param Mage_Core_Model_Abstract $model             The calendar model.
     * @param string                   $text              The text describing the event.
     * @param boolean                  $sendNotifications Whether to send notifications about
     *                                                    the creation of the event.
     *
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function addQuickEvent(Mage_Core_Model_Abstract $model, $text, $sendNotifications = false)
    {
        $this->_getWriteAdapter()
            ->query(
                'events', 
                'quickAdd', 
                array(
                    $model->getId(),
                    $text,
                    array(
                        'sendNotifications' => $sendNotifications,
                    )
                )
            );

        return $this;
    }

    /**
     * Delete a model.
     * 
     * @param  Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function delete(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeDelete($model);

        // Delete for both data and UI resources
        // Cannot delete primary calendar entries
        if (!$model->getPrimary()) {
            $this->_getWriteAdapter()
                ->delete('calendars', array('calendar_id' => $model->getId()));

            // First delete action appears to cascade
            //$this->_getWriteAdapter()
            //    ->delete('calendarList', array('calendar_id' => $model->getId()));
        }

        $this->_afterDelete($model);

        return $this;
    }

    /**
     * Get a cached item.
     * 
     * @param string $path       The cache path.
     * @param mixed  $cachedItem The cached item at the current level.
     * 
     * @return mixed
     */
    public function getCache($path = '', $cachedItem = null)
    {
        $parts  = explode('/', $path);
        $part   = array_shift($parts);

        if ($path && !$cachedItem) {
            $cachedItem = $this->_cache;
        }

        if (is_array($cachedItem) && array_key_exists($part, $cachedItem)) {
            return $this->getCache(implode('/', $parts), $cachedItem[$part]);
        }

        return $cachedItem;
    }

    /**
     * Get all calendar color schemes.
     * 
     * @param string $type The resource type.
     * 
     * @return array
     */
    public function getColorSchemes($type = 'calendar')
    {
        $colors = $this->getCache("colors/{$type}");

        if (is_null($colors)) {
            $colors = $this->_getReadAdapter()
                ->fetchAll('colors');

            $this->_setCache('colors', $colors);
        }

        return $this->getCache("colors/{$type}");
    }

    /**
     * Get the extended data load flag.
     * 
     * @return boolean
     */
    public function getLoadExtendedDataFlag()
    {
        return $this->_loadExtendedDataFlag;
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
     * @param Mage_Core_Model_Abstract  $model The calendar model.
     * @param mixed                     $value The calendar ID.
     * @param string                    $field The ID field by which to load.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function load(Mage_Core_Model_Abstract $model, $value, $field = null)
    {
        $this->_beforeLoad($model);

        if ($value) {
            $read = $this->_getReadAdapter();

            // Read from both resources for base and UI data
            if ($this->getLoadExtendedDataFlag()) {
                $data = array_merge(
                    $read->fetchRow('calendars', array('calendar_id' => $value)),
                    $read->fetchRow('calendarList', array('calendar_id' => $value))
                );
            }
            // Else fetch only the UI meta data
            else {
                $data = $read->fetchRow('calendarList', array('calendar_id' => $value));
            }

            if ($data) {
                $model->setData($data);
            }
        }
        
        $this->_afterLoad($model);

        return $this;
    }

    /**
     * Save the model data.
     * 
     * @param Mage_Core_Model_Abstract $model The calendar model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function save(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeSave($model);

        if (!is_null($model->getId())) {
            $this->_getWriteAdapter()
                ->update('calendars', $model);

            // Only update extended data if present
            if ($this->getLoadExtendedDataFlag()) {
                $this->_getWriteAdapter()
                    ->update('calendarList', $model);
            }
        } else {
            // Insert action depends on primary status; cannot add a new primary calendar
            if (!$model->getPrimary()) {
                $results = $this->_getWriteAdapter()
                    ->insert('calendars', $model);

                if (is_array($results)) {
                    $model->setId($results['id'])
                        ->setEtag($results['etag'])
                        ->setKind($results['kind']);
                }

                // Only insert extended data if present
                if ($this->getLoadExtendedDataFlag()) {
                    // Previous insert appears to cascade; update newly created calendar
                    $results = $this->_getWriteAdapter()
                        ->update('calendarList', $model);
                }

                $this->_setOwnership($model);

                unset($results);
            }
        }

        $this->_afterSave($model);

        return $this;
    }

    /**
     * Set the extended data load flag.
     * 
     * @param boolean $state The flag state.
     *
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function setLoadExtendedDataFlag($state)
    {
        if ($state == true) {
            $this->_loadExtendedDataFlag = true;
        } else {
            $this->_loadExtendedDataFlag = false;
        }

        return $this;
    }

    /**
     * Expand the notification settings data as a collection.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function setNotifications(Mage_Core_Model_Abstract $model)
    {
        $notifications  = $model->getData('notification_settings');
        $items          = Mage::getModel('google/calendar_event_notification')->getCollection();

        if (isset($notifications['notifications']) && is_array($notifications['notifications'])) {
            foreach ($notifications['notifications'] as $item) {
                $notification = Mage::getModel('google/calendar_event_notification', $item);

                $items->addItem($notification);
            }
        }

        $model->setData('notifications', $items);
        unset($notifications);

        return $this;
    }

    /**
     * Expand the reminder data as a collection.
     * 
     * @param Mage_Core_Model_Abstract $model The event model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar
     */
    public function setReminders(Mage_Core_Model_Abstract $model)
    {
        $reminders  = $model->getData('default_reminders');
        $items      = Mage::getModel('google/calendar_event_reminder')->getCollection();

        if (is_array($reminders)) {
            foreach ($reminders as $item) {
                $reminder = Mage::getModel('google/calendar_event_reminder', $item);

                $items->addItem($reminder);
            }
        }

        $model->setData('reminders', $items);
        unset($reminders);

        return $this;
    }

}