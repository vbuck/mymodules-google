<?php

/**
 * Simple event notification collection wrapper model.
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

class Mymodules_Google_Model_Resource_Calendar_Event_Notification_Collection 
    extends Varien_Data_Collection
{

    /* @var $_apiObject array */
    protected $_apiObject = null;

    /**
     * Get event notification settings. Alias for items.
     * 
     * @return array
     */
    public function getNotificationSettings()
    {
        return $this->getItems();
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
            $this->_apiObject = array();

            foreach ($this as $item) {
                $this->_apiObject[] = $item->toApiObject();
            }
        }

        return $this->_apiObject;
    }

}