<?php

/**
 * Google Calendar settings resource model.
 *
 * PHP Version 5
 *
 * @todo      Ability to get settings for user acting on behalf of ('sub' param).
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

class Mymodules_Google_Model_Resource_Calendar_Setting 
    extends Mymodules_Google_Model_Resource_Abstract
{

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
     * @param Mage_Core_Model_Abstract $model The model.
     * @param array                    $value The ID value.
     * @param string                   $field The ID field.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Setting
     */
    public function load(Mage_Core_Model_Abstract $model, $value, $field = null)
    {
        $this->_beforeLoad($model);

        if ($value) {
            $data = $this->_getReadAdapter()
                ->fetchRow('settings', $value);

            if ($data) {
                $model->setData($data);
            }
        }

        $this->_afterLoad($model);

        return $this;
    }

}