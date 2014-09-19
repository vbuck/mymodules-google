<?php

/**
 * Simple event attendee collection wrapper model.
 *
 * PHP Version 5
 *
 * @package   Mymodules_Google
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski
 */

class Mymodules_Google_Model_Resource_Calendar_Event_Attendee_Collection 
    extends Varien_Data_Collection
{

    /* @var $_apiObject array */
    protected $_apiObject = null;

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