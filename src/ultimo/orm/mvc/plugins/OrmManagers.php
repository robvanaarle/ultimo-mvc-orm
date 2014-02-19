<?php

namespace ultimo\orm\mvc\plugins;

class OrmManagers implements \ultimo\mvc\plugins\ApplicationPlugin {
  
  /**
   * The name, dsn, username, password and driver options of PDO connections.
   * @var array
   */
  protected $connections = array();
  
  /**
   * Model info of models availbale throughout the entire application.
   * @var array
   */
  protected $globalModels = array();
  
  /**
   * Adds connection info.
   * @param string $name The name of the connection.
   * @param string $dsn The dsn of the connection.
   * @param string $username The username of the connection.
   * @param string $password The password of the connection
   * @param array $driverOptions The driver options for the connection.
   * return OrmManagers This instance for fluid design.
   */
  public function addConnection($name, $dsn, $username='', $password='', $driverOptions = array()) {
    $this->connections[$name] = array($dsn, $username, $password, $driverOptions);
    return $this;
  }

  /**
   * Returns a PDO connection with the specified name. If this is the first
   * request for a connection, then the connection is established.
   * @param string $name The name of the PDO connection to get.
   * @return \PDO The PDO connection with the specified name, or null if no
   * connection exists with that name.
   */
  public function getConnection($name) {
    if (!isset($this->connections[$name])) {
      return null;
    }

    if (is_array($this->connections[$name])) {
      $args = $this->connections[$name];
      $this->connections[$name] = new \PDO($args[0], $args[1], $args[2], $args[3]);
      $this->connections[$name]->query('SET NAMES \'utf8\'');
    }

    return $this->connections[$name];
  }
  
  /**
   * Adds info about a global module, usable throughout the entire application.
   * @param string $tableIdentifier The table identifier.
   * @param string $modelName The name of the model.
   * @param string $modelClass The qualified classname of the model.
   * @return OrmManagers This instance for fluid design..
   */
  public function addGlobalModel($tableIdentifier, $modelName, $modelClass) {
    $this->globalModels[$modelName] = array('tableIdentifier' => $tableIdentifier, 'modelClass' => $modelClass);
    return $this;
  }
  
  /**
   * Returns the info about global models.
   * @return array The info about global models.
   */
  public function getGlobalModels() {
    return $this->globalModels;
  }
  
  public function onPluginAdded(\ultimo\mvc\Application $application) { }
  
  /**
   * Attach am OrmModuleManager to the created module.
   */
  public function onModuleCreated(\ultimo\mvc\Module $module) {
    $module->addPlugin(new OrmModuleManagers($module, $this), 'uorm');
  }
  
  public function onRoute(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request) { }
  
  public function onRouted(\ultimo\mvc\Application $application, \ultimo\mvc\Request $request=null) { }
  
  public function onDispatch(\ultimo\mvc\Application $application) { }
  
  public function onDispatched(\ultimo\mvc\Application $application) { }
  
}