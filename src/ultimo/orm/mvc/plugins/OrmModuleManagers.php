<?php

namespace ultimo\orm\mvc\plugins;

class OrmModuleManagers implements \ultimo\mvc\plugins\ModulePlugin {
  
  /**
   * The module to manage the manageers for.
   * @var \ultimo\mvc\Module
   */
  protected $module;
  
  /**
   * The creator of this plugin.
   * @var OrmManagers
   */
  protected $appPlugin;
  
  /**
   * Cached managers.
   * @var array
   */
  protected $managers = array();
  
  /**
   * Constructor
   * @param \ultimo\mvc\Module $module The module to manager the managers for.
   * @param OrmManagers $appPlugin The creator of this plugin.
   */
  public function __construct(\ultimo\mvc\Module $module, OrmManagers $appPlugin) {
    $this->module = $module;
    $this->appPlugin = $appPlugin;
  }
  
  /**
   * Constructs the table identifier belonging to the model with the specified
   * name.
   * @param string $modelName The model name.
   * @return string The table identifier belonging to the model with the
   * specified name.
   */
  public function getTableIdentifier($modelName) {
    // convert camelcase to underscore case
    $modelTableName = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $modelName);
    return '`' . strtolower($this->module->getName() . '_' . $modelTableName) . '`';
  }
  
  /**
   * Returns the fully qualified classname within the module for the specified
   * model name.
   * @param string $modelName The model name.
   * @return The fully qualified classname within the module for the specified
   * model name, or null if no model class exists with that name.
   */
  public function getModelClass($modelName) {
    return $this->module->getFQName('models\\' . $modelName);
  }
  
  /**
   * Returns a manager with the specified qualified name.
   * @param string $managerName The qualified manager name.
   * @return \ultimo\orm\Manager The manager with the specified qualified name,
   * or null if no such manager exists in the module.
   */
  public function getManager($managerName='') {
    
    $managerId = strtolower($managerName) . 'manager';
    
    // check if it is in cache
    if (!isset($this->managers[$managerId])) {
      
      if ($managerName != '') {
        // fetch the fully qualified name
        $qName = 'managers\\' . ucfirst(strtolower($managerName)) . 'Manager';
        $fqName = $this->module->getFQName($qName);
        if ($fqName === false) {
          return null;
        }
      } else {
        $fqName = '\ultimo\orm\mvc\Manager';
      }
      
      // retrieve the connection id
      $connectionId = 'default';
      $uormConfig = $this->module->getPlugin('config')->getConfig('uorm');
      if (isset($uormConfig[$managerId]) && isset($uormConfig[$managerId]['connectionId'])) {
        $connectionId = $uormConfig[$managerId]['connectionId'];
      }

      // fetch the connection and create the manager
      $connection = $this->appPlugin->getConnection($connectionId);
      $manager = new $fqName($connection);
      
      // add module
      if (method_exists($manager, 'setModule')) {
        $manager->setModule($this->module);
      }
      
      // register each model name in the module, if no specific manager was
      // requested
      if ($managerName == '') {
        $manager->registerModelNames($this->getModelNames());
      }
      
      // associate the models by finding them in the module, or in the global
      // models
      $modelNames = $manager->getRegisteredModelNames();
      $globalModels = $this->appPlugin->getGlobalModels();
      
      $moduleName = $this->module->getName();
      foreach ($modelNames as $modelName) {
        $tableIdentifier = $this->getTableIdentifier($modelName);
        $modelClass = $this->getModelClass($modelName);
        
        if ($modelClass === null && isset($globalModels[$modelName])) {
          $tableIdentifier = $globalModels[$modelName]['tableIdentifier'];
          $modelClass = $globalModels[$modelName]['modelClass'];
        }
        
        if ($modelClass !== null) {
          $manager->associateModel($tableIdentifier, $modelName, $modelClass);
        }
      }
      
      $this->managers[$managerId] = $manager;
    }
    
    return $this->managers[$managerId];
  }
  
  /**
   * Returns all model names in the module.
   * @return array All model names in the module
   */
  public function getModelNames() {
    $modelNames = array();
    
    // loop through this module and each parent
    $module = $this->module;
    while ($module !== null) {
      // loop through all files in the 'models' directory
      $modelsDir = $module->getBasePath() . DIRECTORY_SEPARATOR . 'models';
      if (is_dir($modelsDir)) {
        foreach (scandir($modelsDir) as $file) {
          // add file name as model name if extension is php
          if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
            $modelNames[] = substr($file, 0, -4);
          }
        }
      }
      $module = $module->getParent();
    }
    
    return $modelNames;
  }
  
  public function onControllerCreated(\ultimo\mvc\Controller $controller) { }
}