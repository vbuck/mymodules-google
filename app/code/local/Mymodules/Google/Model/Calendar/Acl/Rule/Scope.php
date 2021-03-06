<?php

/**
 * Simple wrapper model for ACL rule scope.
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

class Mymodules_Google_Model_Calendar_Acl_Rule_Scope 
    extends Mage_Core_Model_Abstract
{

    /* @var $_apiObject Google_Service_Calendar_AclRuleScope */
    protected $_apiObject = null;

    /**
     * Local constructor.
     * 
     * @return void
     */
    public function _construct()
    {
        $this->_init('google/calendar_acl_rule_scope');
    }

    /**
     * Convert the data to an API-compatible object.
     * 
     * @return Google_Service_Calendar_AclRuleScope
     */
    public function toApiObject()
    {
        // Re-build existing instance
        if ($this->_apiObject) {
            unset($this->_apiObject);
            $this->_apiObject = null;
        }

        if (is_null($this->_apiObject)) {
            $data = Mage::getSingleton('google/resource_helper_calendar')->translateFields($this->getData());

            $this->_apiObject = new Google_Service_Calendar_AclRuleScope($data);

            unset($data);
        }

        return $this->_apiObject;
    }

}