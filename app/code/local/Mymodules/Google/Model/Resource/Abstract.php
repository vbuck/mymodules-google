<?php

/**
 * Google resource model abstract class.
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

abstract class Mymodules_Google_Model_Resource_Abstract 
    extends Mage_Core_Model_Resource_Abstract
{

    protected $_idFieldName = 'id';

    /**
     * Constructor.
     * 
     * @return void
     */
    public function _construct() {}

    /**
     * Resource after delete implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * Resource after load implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * Resource after save implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * Resource before delete implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * Resource before load implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _beforeLoad(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * Resource before save implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $model)
    {
        return $this;
    }

    /**
     * Read adapter implementation.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _getReadAdapter() {}

    /**
     * Write adapter implementation.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    protected function _getWriteAdapter() {}

    /**
     * Post-load operations.
     * 
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    public function afterLoad(Mage_Core_Model_Abstract $model)
    {
        return $this->_afterLoad($model);
    }

    /**
     * Resource delete implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    public function delete(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeDelete($model);
        $this->_afterDelete($model);

        return $this;
    }

    /**
     * Return the ID field name.
     * 
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Resource load implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * @param mixed                    $value  The ID value.
     * @param string                   $field  The ID field.
     * 
     * @return Mage_Core_Model_Resource_Abstract
     */
    public function load(Mage_Core_Model_Abstract $model, $value, $field = true)
    {
        $this->_beforeLoad($model);
        $this->_afterLoad($model);

        return $this;
    }

    /**
     * Resource save implementation.
     *
     * @param Mage_Core_Model_Abstract $model The model.
     * 
     * @return Mymodules_Google_Model_Resource_Abstract
     */
    public function save(Mage_Core_Model_Abstract $model)
    {
        $this->_beforeSave($model);
        $this->_afterSave($model);

        return $this;
    }

}