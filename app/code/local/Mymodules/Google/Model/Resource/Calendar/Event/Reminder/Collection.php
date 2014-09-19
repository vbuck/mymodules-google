<?php

/**
 * Simple event reminder collection wrapper model.
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

class Mymodules_Google_Model_Resource_Calendar_Event_Reminder_Collection 
    extends Varien_Data_Collection
{

    /* @var $_apiObject Varien_Object */
    protected $_apiObject = null;
    /* @var $_useDefault boolean */
    protected $_useDefault;

    /**
     * Add item to the collection.
     * 
     * @param Varien_Object $item The item to add.
     */
    public function addItem(Varien_Object $item)
    {
        parent::addItem($item);

        // Adding items forces non-default reminder behavior
        $this->setUseDefault(false);

        return $this;
    }

    /**
     * Get event reminder overrides. Alias for items.
     * 
     * @return array
     */
    public function getOverrides()
    {
        return $this->getItems();
    }

    /**
     * Get the default reminders flag.
     * 
     * @return boolean
     */
    public function getUseDefault()
    {
        return $this->_useDefault;
    }

    /**
     * Convert the collection to an API-compatible object.
     * 
     * @return Varien_Object
     */
    public function toApiObject()
    {
        // Re-build existing instance
        if ($this->_apiObject) {
            unset($this->_apiObject);
            $this->_apiObject = null;
        }

        if (is_null($this->_apiObject)) {
            $reminders = new Google_Service_Calendar_EventReminders();

            $reminders->setUseDefault($this->getUseDefault());

            $overrides = array();
            foreach ($this as $item) {
                $overrides[] = $item->toApiObject();
            }

            $reminders->setOverrides($overrides);

            $this->_apiObject = $reminders;
        }

        return $this->_apiObject;
    }

    /**
     * Set the default reminders flag.
     * 
     * @param boolean $state The reminder state.
     */
    public function setUseDefault($state)
    {
        if ($state == true) {
            $this->_useDefault = true;
            // Must clear items if using default settings
            $this->clear();
        } else {
            $this->_useDefault = false;
        }

        return $this;
    }

}