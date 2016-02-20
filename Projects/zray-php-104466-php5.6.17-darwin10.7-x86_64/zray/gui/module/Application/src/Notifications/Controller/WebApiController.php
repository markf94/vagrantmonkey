<?php
namespace Notifications\Controller;

use Notifications\Db\NotificationsMapper,
	Notifications\NotificationContainer;

use Application\Module;

use Zend\Stdlib\Parameters;

use ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Set,
	Zend\Mvc\Controller\ActionController,
	ZendServer\Log\Log,
	ZendServer\FS\FS,
	WebAPI;

use Servers\View\Helper\ServerStatus;
use Notifications\NotificationActionContainer;
use Application\Db\Connector;
use WebAPI\Exception;

class WebApiController extends WebAPIActionController {
	
	public function getNotificationsAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array(
			'hash' => '',
		));
		
		$this->checkRssFeed();
		
		$this->validateString($params['hash'], 'hash');
		
	    $serversMapper = $this->getServersMapper();
		$notificationsMapper = $this->getNotificationsMapper();
		
	    $restartingServers = $serversMapper->findRestartingServers();
	    // add restart required notification in case some servers in restart required state
	    $restartRequiredServers = $serversMapper->findRestartRequiredServers();
		$healthCheckesult =  $this->checkZsdHealth();
		$missingServers = $notificationsMapper->findMissingServers();
		$daemonMessages = $this->getMessagesMapper()->findAllDaemonsMessages();

		
		$db = $this->getServiceLocator()->get(Connector::DB_CONTEXT_ZSD); /* @var $db \Zend\Db\Adapter\Adapter */
		$db = $db->getDriver()->getConnection();
		try {
			$db->beginTransaction();
		    if ($healthCheckesult === false) {
		    	$this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_ZSD_OFFLINE);
		    } elseif ($healthCheckesult === true) {
		    	$this->getNotificationsMapper()->deleteByType(NotificationContainer::TYPE_ZSD_OFFLINE);
		    }
	        
		    $notificationsMapper->cleanNotificationsForMissingServers($missingServers);
		    
		    // add restarting notification in case some servers in restarting state
	        if ((is_array($restartingServers) && count($restartingServers) > 0)
	        	|| (($restartingServers instanceof Set) && ($restartingServers->count() > 0))) {
	        	$notificationsMapper->insertNotification(NotificationContainer::TYPE_SERVER_RESTARTING);
	        } else {
	        	$notificationsMapper->deleteByType(NotificationContainer::TYPE_SERVER_RESTARTING);
	        }
	        
	        
	        if ((is_array($restartRequiredServers) && count($restartRequiredServers) > 0)
	        	|| (($restartRequiredServers instanceof Set) && ($restartRequiredServers->count() > 0))) {
	        	$notificationsMapper->insertNotification(NotificationContainer::TYPE_RESTART_REQUIRED);
	        } else {
	        	$notificationsMapper->deleteByType(NotificationContainer::TYPE_RESTART_REQUIRED);
	        }
	        $db->commit();
		} catch (\Exception $ex) {
			$db->rollback();
			throw new Exception(vsprintf('Could not manage notifications: %s', array($ex->getMessage())), Exception::INTERNAL_SERVER_ERROR, $ex);
		}
        
		$notifications = $notificationsMapper->findAllNotificationsWithNames();
		
		$notificationsSet = new Set($notifications->toArray());
		$notificationsSet->setHydrateClass('\Notifications\NotificationContainer');
		
		// remove restarting notification in case no restart required is present
		$notificationTypes = array();
		foreach ($notificationsSet as $key => $notification) { /* @var $notification NotificationContainer */
			$notificationTypes[$notification->getType()] = $key;
		}
		
		// Remove expiration notices if license is not going to expire
		/// this may happen if a trial license was replaced "under the hood" with a new unexpired license
		if (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45])
		 || isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15])
		 	|| isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE])) {
			
			$licenseExpiryDays = $this->getZemUtilsWrapper()->getLicenseExpirationDaysNum(true);
			$licenseNeverExpires = $this->getZemUtilsWrapper()->getLicenseInfo()->isNeverExpires();
			if ($licenseExpiryDays > 60 || $licenseNeverExpires) {
				if (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45])) {
					unset($notificationsSet[$notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45]]);
					$notificationsMapper->deleteByType(NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45);
				}
				if (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15])) {
					unset($notificationsSet[$notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15]]);
					$notificationsMapper->deleteByType(NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15);
				}
				if (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE])) {
					unset($notificationsSet[$notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE]]);
					$notificationsMapper->deleteByType(NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE);
				}
			}
		}
		// Remove 45 days expiry if there's either a 15/7 days notification
		if (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45]) && (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15]) || isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE]))) {
			unset($notificationsSet[$notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45]]);
			$notificationsMapper->deleteByType(NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45);
		}	
		// Remove 15 days expiry if there's 7 days notification
		if (isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15]) && isset($notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE])) {
			unset($notificationsSet[$notificationTypes[NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15]]);
			$notificationsMapper->deleteByType(NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15);
		}
		if (isset($notificationTypes[NotificationContainer::TYPE_DATABASE_CONNECTION_RESTORED])) {
			$notificationsArray = $notificationsSet->toArray();
			foreach ($notificationsArray as $key => $value) {
				if ($value['TYPE'] == NotificationContainer::TYPE_DATABASE_CONNECTION_RESTORED) {
					$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
					$serversSet = $serversMapper->findServerById($value['NODE_ID']);
					
					$notificationsArray[$key]['EXTRA_DATA'] = json_encode(array($serversSet->getNodeName()));
				}
			}
			$notificationsSet = new Set($notificationsArray);
			$notificationsSet->setHydrateClass('\Notifications\NotificationContainer'); 
		}
				
		$hash = md5(serialize($notificationsSet));
		if ($hash == $params['hash']) {
			$notificationsSet = array();
		}
		
		$viewModel = new \Zend\View\Model\ViewModel(array('notifications' => $notificationsSet, 'hash' => $hash, 'daemonMessages' => $daemonMessages));
		$viewModel->setTemplate('notifications/web-api/1x6/get-notifications');
		return $viewModel;
	}
	
	private function checkRssFeed() {
	    static $checked = false;
	    if ($checked) {
	        return;
	    }
	    
	    try {
    	    $channel = \Zend\Feed\Reader\Reader::importString($this->getZendServerRssFeed());
    	    $lastDate = Module::config('rss', 'zend_gui', 'rssDate');
    	    $newItems = array();
    	    $maxDate = null;
    	    foreach ($channel as $item) { /* @var $item \Zend\Feed\Reader\Entry\Rss */
    	        $date = $item->getDateModified(); /* @var $date \DateTime */
    	        if ($date->getTimestamp() - $lastDate  > 0) {
    	            $newItems[] = $item;
    	            $maxDate = $date;
    	        }
    	    }
    	     
    	    foreach ($newItems as $newItem) {
    	        $checked = true; // important!
    	        $this->getGuiConfigurationMapper()->setGuiDirectives(array('zend_gui.rssDate' => $maxDate->getTimestamp()));
    	        
    	        $category = current($newItem->getCategories()->getValues());
    	        $notificationType = \Notifications\NotificationContainer::TYPE_RSS_NEWS_AVAILABLE + $category;
    	        
    	        $this->getNotificationsMapper()->deleteByType($notificationType);
    	        $this->getNotificationsMapper()->insertNotification($notificationType, array($newItem->getDescription(), 'title' => $newItem->getTitle()));
    
    	        $renderer = $this->getLocator( 'Zend\View\Renderer\PhpRenderer' ); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
    	        $resolver = $renderer->resolver();
    	        
    	        $request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
    	        $request->setPost(new \Zend\Stdlib\Parameters(array('type' => 'RSSUpdateAvailable' . $category)));
    	        $request->setMethod('POST');
    	        $viewModel = $this->forward()->dispatch('NotificationsWebApi-1_3', array('action' => 'sendNotification')); /* @var $viewModel \Zend\View\Model\ViewModel */
    	        
    	        $renderer->setResolver($resolver);
    	    }
	    } catch (\Exception $e) {
	        return;
	    }
	}
	
	private function getZendServerRssFeed() {
	    try {
	        $guiTemp = FS::getGuiTempDir();
	        $welcomePath = $guiTemp . DIRECTORY_SEPARATOR . 'rss.html';
	        $welcomeTimestamp = 0;
	        if (file_exists($welcomePath)) {
	            $welcomeTimestamp = filemtime($welcomePath);
	        }
	
	        $welcomeContent = '';
	        // check if timestamp is a day older - check once a day
	        if ($welcomeTimestamp <= strtotime('-1 day')) {
	            $client = new \Zend\Http\Client();
	            $client->setUri($this->getRssUrl());
	            $response = $client->send();
	            if ($response->isOk() !== false) {
	                file_put_contents($welcomePath, $response->getBody());
	                $welcomeContent = $response->getBody();
	            } else {
	                if (file_exists($welcomePath)) {
	                   $welcomeContent = file_get_contents($welcomePath);
	                } else {
	                    file_put_contents($welcomePath, '');
	                    $welcomeContent = '';
	                }
	            }
	        } else {
	            if (file_exists($welcomePath)) {
	               $welcomeContent = file_get_contents($welcomePath);
	            }
	        }
	    } catch (\Exception $e) {
	        $welcomeContent = '';
	    }
	     
	    return $welcomeContent;
	}
	
	private function getRssUrl() {
	    $osName = FS::getOSAsString();
	    $arch = php_uname('m');
	     
	    if (strtolower($osName) == 'linux') {
	        $osName = $this->getLinuxDistro();
	        if (empty($osName)) {
	            $osName = 'Linux';
	        }
	    }
	    
	    $uniqueId = Module::config('license', 'zend_gui', 'uniqueId');
	    $zsVersion = Module::config('package', 'version');
	    $profile = Module::config('package', 'zend_gui', 'serverProfile');
	    
	    $trial = ($this->getZemUtilsWrapper()->getLicenseType() == \Configuration\License\License::EDITION_ENTERPRISE_TRIAL) ? '1' : '0';
	    
	    return Module::config('rss', 'zend_gui', 'rssUrl') . '?zsVersion=' . $zsVersion . '&php=' . phpversion() . '&arch=' . $arch . '&os=' . $osName . '&profile=' . $profile . '&trial=' . $trial . '&hash=' . $uniqueId;
	}
	
	/**
	 * null means there's an error checking current state
	 * @return boolean|null
	 */
	private function checkZsdHealth() {
		$zsdHealthChecker = $this->getLocator()->get('Zsd\ZsdHealthChecker');
		$notificationContainer = $this->getNotificationsMapper()->getNotificationByType(NotificationContainer::TYPE_ZSD_OFFLINE)->current();
		$zsdIsDown = (is_object($notificationContainer) && $notificationContainer->getId() > 0);
		
		return $zsdHealthChecker->checkZsdHealth($zsdIsDown);
	}
	
	public function deleteNotificationAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array(
			'type' => '',
		));
		
		$this->validateMandatoryParameters($params, array('type'));
		$type = $this->validateNotificationType($params['type'], 'type');
		
				
		$mapper = $this->getNotificationsMapper();
		$mapper->deleteByType($type);
		
		return array();
	}
	
	public function updateNotificationAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array(
				'type' => '',
				'repeat' => ''
		));
		
		$this->validateMandatoryParameters($params, array('type'));
		$this->validateString($params['type'], 'type');
		$this->validateInteger($params['repeat'], 'repeat');
		
		$mapper = $this->getNotificationsMapper();
		$type = $this->convertNotificationNameToType($params['type']);
		$repeat = $params['repeat'];
		
		if ($type == \Notifications\NotificationContainer::TYPE_MAX_SERVERS_IN_CLUSTER) { // maxServersInCluster will be delayed for maximum 1 week 
			$repeat = min(10080, $repeat); // 1 week = 60 * 24 * 7
		} elseif ($type == \Notifications\NotificationContainer::TYPE_NO_SUPPORT) { // noSupport will be delayed for maximum 1 month
			$repeat = min(40320, $repeat); // 1 month = 60 * 24 * 7 * 4
		}
		
		$mapper->insertFilter($type, $repeat);
		
		$notifications = $mapper->getNotificationByType($type);
		$notificationsSet = new Set($notifications->toArray());
		$notificationsSet->setHydrateClass('\Notifications\NotificationContainer');
		
		return array('notifications' => $notificationsSet);
	}
		
	public function sendNotificationAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array(
			'type' => '',
			'ip' => '127.0.0.1'
		));
		
		if (empty($params['ip'])) {
			$params['ip'] = '127.0.0.1';
		}
		
		$this->validateMandatoryParameters($params, array('type', 'ip'));
		$this->validateString($params['type'], 'type');
		$this->validateString($params['ip'], 'ip');

		
		$notificationAction = $this->getNotificationsActionMapper()->getNotification($params['type']);

		if ($notificationAction->count() == 0) {
			return array();
		}
		
		$notificationAction = $notificationAction->current();
		
		$notificationRow = $this->getNotificationsMapper()->getNotificationByType($notificationAction->getType())->current();
		
		$notification = new NotificationContainer(array('TYPE' => $notificationAction->getType(), 'EXTRA_DATA' => json_encode($notificationRow->getExtraData())));
		
		$notificationEmail = $notificationAction->getEmail();
		$notificationCustomAction = $notificationAction->getCustomAction();
		
		$renderer = $this->getLocator()->get('Zend\View\Renderer\PhpRenderer');
		$listView = array();
		
		$licenseError = false;
		// send email
		if (! empty($notificationEmail)) {
			if ($this->isAclAllowed('data:useEmailNotification')) {
				$templateParams = array('title' => $notification->getTitle(),
										'description' => $renderer->notificationDescription($notification),
				                        'url' => ''
				);
				
				$url = $notification->getUrl();
				if (! empty($url)) {
				    $templateParams['url'] = 'http://' . $params['ip'] . ':' . Module::config('installation', 'defaultPort') . Module::config()->baseUrl . $notification->getUrl();
				}
				
				$this->getRequest()->getPost()->set('to', $notificationEmail);
				$this->getRequest()->getPost()->set('from', 'noreply@zend.com');
				$this->getRequest()->getPost()->set('subject', 'Zend Server Notification Alert');
				$this->getRequest()->getPost()->set('templateName', 'notification');
				$this->getRequest()->getPost()->set('templateParams', $templateParams);
				
				$listView = $this->forward()->dispatch('EmailWebAPI-1_3', array('action' => 'emailSend')); /* @var $emailListView \Zend\View\Model\ViewModel */
				$listView->setTemplate('notifications/web-api/1x3/send-notification');
			} else {
				$licenseError = true;
			}
		}
		
		// call script
		if (! empty($notificationCustomAction)) {
			if ($this->isAclAllowed('data:useCustomAction')) {
				// need to replace with cUrl
				$ctx = stream_context_create(array(
						'http' => array(
								'timeout' => 1
						)
					)
				);
				
				$uri = new \Zend\Uri\Http($notificationCustomAction);
				$uriParams = $uri->getQueryAsArray();
				$uri->setQuery(array_merge($uriParams, array('type' => $params['type'])));
				
				file_get_contents($uri, 0, $ctx);
			} else {
				$licenseError = true;
			}
		}
		
		if ($licenseError) {
			throw new WebAPI\Exception(_t('Not supported by edition'), WebAPI\Exception::NOT_SUPPORTED_BY_EDITION);
		}
		
		return $listView;
	}	
	
	private function validateNotificationType($type) {
		$notificationsActionsMapper = $this->getNotificationsActionMapper();
		$notifications = $notificationsActionsMapper->findAll();
		$dictionary = array();
		foreach ($notifications as $notificationType) { /* @var $notificationType NotificationActionContainer */
			$dictionary[$notificationType->getName()] = $notificationType->getType();
		}
		Log::debug($dictionary);
		if (! isset($dictionary[$type])) {
			throw new WebAPI\Exception(_t('type parameter must be one of (%s)', array(implode(', ', array_keys($dictionary)))), WebAPI\Exception::INVALID_PARAMETER);
		}
		return $dictionary[$type];
	}
	
	/**
	 * @param string $name
	 * @return integer
	 */
	private function convertNotificationNameToType($name) {
		$notificationsActionsMapper = $this->getNotificationsActionMapper();
		$notificationAction = $notificationsActionsMapper->getNotification($name);
		if (count($notificationAction) == 0) {
			return -1;
		}
		$notificationAction = $notificationAction->current();
		return $notificationAction->getType();
	}
	
	/**
	 * Return the linux distro name
	 * @return mixed
	 */
	private function getLinuxDistro() {
	    exec('less /etc/issue', $output);
	    if (count($output) > 0) {
	
	        $distros = array(
	            'Ubuntu' => 'Ubuntu',
	            'Fedora' => 'Fedora',
	            'OEL'	 => 'Oracle',
	            'RHEL'	 => 'Red Hat',
	            'OpenSUSE'	=> 'openSUSE',
	            'SUSE'	 => 'SUSE',
	            'Debian' => 'Debian',
	            'CentOS' => 'CentOS',
	        );
	
	        foreach ($distros as $distro => $keyword) {
	            foreach ($output as $outputRow) {
	                if (strpos($outputRow, $keyword) !== false) {
	                    return $distro;
	                }
	            }
	        }
	    }
	
	    return '';
	}
}