<?php

namespace ultimo\orm\mvc;

class Manager extends \ultimo\orm\Manager {
  /**
   * Module the Model is part of.
   * @var \ultimo\mvc\Module
   */
  protected $_module;
  
  /**
   * Sets the module this Model is part of.
   * @param \ultimo\mvc\Module $module The module this Model is part of.
   */
  public function setModule(\ultimo\mvc\Module $module) {
    $this->_module = $module;
  }
  
  /**
   * Returns the module this Model is part of.
   * @return \ultimo\mvc\Module $module The module this Model is part of.
   */
  public function getModule() {
    return $this->_module;
  }
}