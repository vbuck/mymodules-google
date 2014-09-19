<?php

/**
 * Google Calendar ACL resource model.
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

class Mymodules_Google_Model_Resource_Calendar_Acl 
    extends Mymodules_Google_Model_Resource_Abstract
{

    /**
     * Expand extended model data into collections and objects.
     *
     * @param Mage_Core_Model_Abstract $model The event model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Acl
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $model)
    {
        $this->setScope($model);

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
     * Delete a model. Deleted entries receive the role 'none.'
     * 
     * @param  Mage_Core_Model_Abstract $model The calendar ACL model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Acl
     */
    public function delete(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeDelete($model);

        $this->_getWriteAdapter()
            ->delete(
                'acl', 
                array(
                    // Optimization on calendar ID
                    // @see Mymodules_Google_Model_Resource_Calendar_Acl::load
                    'calendar_id'   => $model->getCalendarId(),
                    'rule_id'       => $model->getId(),
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
     * @param Mage_Core_Model_Abstract  $model The calendar ACL model.
     * @param array                     $value An indexed array [calendar ID, event ID].
     * @param string                    $field The ID field by which to load.
     * 
     * @return  Mymodules_Google_Model_Resource_Calendar_Acl
     */
    public function load(Mage_Core_Model_Abstract $model, $value, $field = null)
    {
        $this->_beforeLoad($model);

        if ($value) {
            $data = $this->_getReadAdapter()
                ->fetchRow('acl', array(
                    'calendar_id'   => $value[0],
                    'rule_id'       => $value[1]
                ));

            if ($data) {
                $model->setData($data);

                // ACL rules contain no reference to their parent calendar,
                // so we can store it post-load for easy reference later.
                $model->setCalendarId($value[0]);
            }
        }

        $this->_afterLoad($model);

        return $this;
    }

    /**
     * Save the model data.
     * 
     * @param Mage_Core_Model_Abstract $model The calendar ACL model.
     * 
     * @return Mymodules_Google_Model_Resource_Calendar_Acl
     */
    public function save(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeSave($model);

        if (!is_null($model->getId())) {
            $this->_getWriteAdapter()
                ->update('acl', $model);
        } else {
            $results = $this->_getWriteAdapter()
                ->insert('acl', $model);

            if (is_array($results)) {
                $model->setId($results['id'])
                    ->setEtag($results['etag'])
                    ->setKind($results['kind']);
            }
        }

        $this->_afterSave($model);

        return $this;
    }

    /**
     * Expand the scope data as an object.
     * 
     * @param Mage_Core_Model_Abstract $model The ACL model.
     *
     * @return Mymodules_Google_Model_Resource_Calendar_Acl
     */
    public function setScope(Mage_Core_Model_Abstract $model)
    {
        $scope = $model->getData('scope');

        if (is_array($scope)) {
            $model->setData('scope', new Varien_Object($scope));
            unset($scope);
        }

        return $this;
    }

}