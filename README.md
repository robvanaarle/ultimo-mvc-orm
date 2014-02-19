# Ultimo ORM MVC
Object relational mapper for Ultimo MVC

## Requirements
* PHP 5.3
* Ultimo ORM
* Ultimo MVC
* Ultimo Config MVC

## Usage
### Register plugin
	$uormPlugin = new \ultimo\orm\mvc\plugins\OrmManagers();

	// add one ore more global models (visible in all modules)
	$uormPlugin->addGlobalModel('`user_user`', 'User', '\user\models');

	// add one or multiple connections
    $uormPlugin->addConnection('master', 'mysql:dbname=database_name;host=localhost', 'username', 'password');

    $application->addPlugin($uormPlugin, 'uorm');

### &lt;module&gt;/configs/uorm.ini
	[production]
	manager.connectionId = "master"

### Controller
	$manager = $this->module->getPlugin('uorm')->getManager();
	$infoitem = $manager->Infoitem->withImages()->getById(42);