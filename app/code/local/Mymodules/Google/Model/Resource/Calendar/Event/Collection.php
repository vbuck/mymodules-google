<?php

/**
 * Google Calendar event collection model.
 *
 * PHP Version 5
 *
 * @see       https://developers.google.com/google-apps/calendar/v3/reference/events/list
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

class Mymodules_Google_Model_Resource_Calendar_Event_Collection 
    extends Mymodules_Google_Model_Resource_Collection_Abstract
{

    /**
     * Initialize collection.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar_event', 'events');
    }

    /**
     * Filter implementation.
     * 
     * @param string $field The field to filter.
     * @param string $value The filter criteria.
     * @param string $type  The filter type.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Event_Collection
     */
    public function addFilter($field, $value, $type = null)
    {
        if ( in_array($field, array('time_min', 'time_max', 'updated_min')) ) {
            $value = Mage::getSingleton('google/resource_helper_calendar')->convertDatetime($value);
        }

        return parent::addFilter($field, $value, $type);
    }

    /**
     * Write calendar ID to all events in the collection as a convenience.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _afterLoad()
    {
        if ( ($filter = $this->getFilter('calendar_id')) ) {
            foreach ($this->_items as $item) {
                $item->setCalendarId($filter['value']);
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Re-order the collection, accounting for special data structs.
     * 
     * @return Mymodules_Google_Model_Resource_Collection_Abstract
     */
    protected function _renderOrders()
    {
        if ($this->_data) {
            $parameters = array();

            foreach ($this->_orders as $field => $direction) {
                $sort = array();

                for ($i = 0; $i < count($this->_data); $i++) {
                    // Special provisions for sorting start and end times.
                    if ( in_array($field, array('start', 'end')) ) {
                        $date = new Zend_Date($this->_data[$i][$field]['date_time']);

                        $sort[$i] = $date->getTimestamp();

                        unset($date);
                    } else {
                        $sort[$i] = $this->_data[$i][$field];
                    }
                }

                $parameters[] = &$sort;
                $parameters[] = &$direction;
            }

            if (count($parameters) > 1) {
                $parameters[] = &$this->_data;

                call_user_func_array('array_multisort', $parameters);
            }
        }

        return $this;
    }

    /**
     * Filter the collection to include cancelled events.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Event_Collection
     */
    public function applyCancelledFilter()
    {
        $this->addFilter('showDeleted', true);

        return $this;
    }

}