<?php

/**
 * Google Calendar ACL collection model.
 *
 * PHP Version 5
 *
 * @see       https://developers.google.com/google-apps/calendar/v3/reference/acl/list
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

class Mymodules_Google_Model_Resource_Calendar_Acl_Collection 
    extends Mymodules_Google_Model_Resource_Collection_Abstract
{

    /**
     * Initialize collection.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar_acl', 'acl');
    }

    /**
     * Write calendar ID to all ACL entries in the collection as a convenience.
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

}