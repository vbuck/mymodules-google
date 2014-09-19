<?php

/**
 * Simple wrapper model for event extended properties.
 *
 * PHP Version 5
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

class Mymodules_Google_Model_Calendar_Event_Extendedproperty 
    extends Mage_Core_Model_Abstract
{

    /* @var $_apiObject Google_Service_Calendar_EventExtendedProperties */
    protected $_apiObject = null;

    /**
     * Local constructor.
     * 
     * @return void
     */
    public function _construct()
    {
        // Initialize property pools
        if (!$this->hasData('private')) {
            $this->setData('private', new Varien_Object());
        }

        if (!$this->hasData('shared')) {
            $this->setData('shared', new Varien_Object());
        }

        $this->_init('google/calendar_event_extendedproperty');
    }

    /**
     * Property type setter.
     * 
     * @param string $type  The property type (private|shared).
     * @param string $key   The property key.
     * @param mixed  $value The property value.
     *
     * @return Mymodules_Google_Model_Calendar_Event_Extendedproperty
     */
    public function setProperty($type = 'shared', $key, $value)
    {
        $collection = $this->getData($type);

        if ($collection instanceof Varien_Object) {
            $collection->setData($key, $value);
        }

        return $this;
    }

    /**
     * Convert the data to an API-compatible object.
     * 
     * @return Google_Service_Calendar_EventExtendedProperties
     */
    public function toApiObject()
    {
        // Re-build existing instance
        if ($this->_apiObject) {
            unset($this->_apiObject);
            $this->_apiObject = null;
        }

        if (is_null($this->_apiObject)) {
            $privateData    = Mage::getSingleton('google/resource_helper_calendar')->translateFields($this->getPrivate()->getData());
            $sharedData     = Mage::getSingleton('google/resource_helper_calendar')->translateFields($this->getShared()->getData());

            $this->_apiObject = new Google_Service_Calendar_EventExtendedProperties(
                array(
                    'private'   => $privateData,
                    'shared'    => $sharedData,
                )
            );

            unset($privateData);
            unset($sharedData);
        }

        return $this->_apiObject;
    }

}