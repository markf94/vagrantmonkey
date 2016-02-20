<?php
namespace Servers\Controller;

use Servers\Forms\JoinCluster;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	MonitorUi\Filter,
	Servers\Forms\AddServer,
	ZendServer\Configuration\Manager,
	ZendServer\Ini\IniReader,
	Statistics\Model as StatisticsModel,
	Configuration\MapperExtensions as MapperExtensions,
	Configuration\MapperDirectives as MapperDirectives,
	Servers\Configuration\Mapper as ServersConfigurationMapper;
use ZendServer\Exception;

class IndexController extends ActionController
{
	public function indexAction() {
		$serversView = $this->forward()->dispatch('ServersWebAPI-1_2', array('action' => 'clusterGetServerStatus')); /* @var $serversView \Zend\View\Model\ViewModel */
		$serversView->setTemplate('servers/index');// Restoring original route
		$serversVars = $serversView->getVariables();
		$serversSet =  $serversVars['servers'];
		
		$addServerForm = new AddServer();
		$edition = new \ZendServer\Edition();
		$nodeId = $edition->getServerId();
		$inCluster = ! $edition->isSingleServer();
		
		$license = $this->getLocator('Configuration\License\ZemUtilsWrapper')->getLicenseInfo();
		$registeredNumOfServers = $this->getLocator('Servers\Db\Mapper')->countActiveServers();

		$tasks = array();
		foreach ($this->getTasksMapper()->findAllTasksOfConnectedServers() as $taskRow) {
			$tasks[$taskRow['TASK_ID']][] = $taskRow['NODE_ID'];
		}

		$mapper = new ServersConfigurationMapper();
		$manager = new Manager();
		
		$variables =  array('pageTitle' => 'Manage Servers',
					'pageTitleDesc' => '',  /* Daniel */
					'joinCluster' => new JoinCluster(),
					'servers' => $serversSet,
					'nodeId' => $nodeId,
					'addServer' => $addServerForm,
					'license' => $license,
					'isServersCountOverLicense' => (! $license->isServersUnlimited()) && $license->getNumOfServers() < $registeredNumOfServers && $inCluster,
					'isServersCountEqualLicense' => (! $license->isServersUnlimited()) && $license->getNumOfServers() == $registeredNumOfServers && $inCluster,
					'registeredNumOfServers' => $registeredNumOfServers,
					'perPage' => Module::config('list' ,'resultsPerPage'),
					'inCluster' => $inCluster,
					'osType' => $manager->getOsType(),
					'currentTasks' => $tasks,
					'isClusterSupport' => $mapper->isClusterSupport(),
                    'isRestartAllowed' => $this->isAclAllowed('route:ServersWebAPI', 'restartPhp'),
					'isDevbar' => $this->getDirectivesMapper()->getDirectiveValue('zray.enable')
				);
		
		$serversView->setVariables($variables);
		return $serversView;
	}
}