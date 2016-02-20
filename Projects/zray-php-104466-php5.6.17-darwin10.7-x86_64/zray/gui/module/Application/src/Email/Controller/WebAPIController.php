<?php
namespace Email\Controller;

use Zend\Mail\AddressList;

use Zend\Mail\Transport;

use Zend\Mail\Message;

use Zend\View\Model\ViewModel;

use Zend\Mime\Part as MimePart,
	Zend\Mime\Message as MimeMessage;	

use ZendServer\Mvc\Controller\WebAPIActionController,
ZendServer\Log\Log,
Application\Module,
WebAPI;
use Notifications\NotificationContainer;
use Zend\Uri\UriFactory;
use WebAPI\Exception;
use Zend\Uri\Http;

class WebAPIController extends WebAPIActionController {
    
    public function emailSendAction() {
        $this->isMethodPost();
        
        $params = $this->getParameters(array(
                'templateName' => '',
                'to' => '',
                'toName' => '',
                'from' => '',
                'fromName' => '',
                'subject' => _t('Welcome Email'),
                'replyTo' => '',
                'headers' => array(),
                'templateParams' => array(),
        		'html' => 'TRUE'
                ));
        
        $this->validateMandatoryParameters($params, array('templateName', 'to', 'from', 'subject'));
        $this->validateEmailString($params['to'], 'to');
        $this->validateEmailAddress($params['from'], 'from');
        $this->validateString($params['subject'], 'subject');
        $useHtml = $this->validateBoolean($params['html'], 'html');
        
        $resolver = $this->getLocator('\Zend\View\Resolver\TemplatePathStack'); /* @var $resolver \Zend\View\Resolver\TemplatePathStack */
        
        // Store the default suffix and paths to be able to render json/xml later on
        $defaultSuffix = $resolver->getDefaultSuffix();
        $paths = $resolver->getPaths();
        
        $resolver->setDefaultSuffix('phtml');
        $resolver->setPaths(array($this->getTemplatePath(Module::config('mail', 'templatePath'))));

        $renderer = $this->getLocator( 'Zend\View\Renderer\PhpRenderer' ); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
        $renderer->setResolver($resolver);
        
        // get the email plugin name and call the controlled plugin with the request parameters
        // the result is merged into the params array
        $emailPluginName = $params['templateName'] . 'Email';
        if ($this->getPluginManager()->has($emailPluginName)) {
        	$emailPlugin = $this->getPluginManager()->get($emailPluginName);
        	try {
        		$params['templateParams'] = array_merge($params['templateParams'], $emailPlugin($params['templateParams']));
        	} catch (\Exception $ex) {
        		// Get back the default suffix and paths to render WebAPI json/xml template
        		$resolver->setDefaultSuffix($defaultSuffix);
        		$resolver->setPaths($paths);
        		$renderer->setResolver($resolver);
        		throw new Exception(vsprintf('Email content rendering failed: %s', array($ex->getMessage())), Exception::INTERNAL_SERVER_ERROR, $ex);
        	}
        }

        $directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
        $guiHostName = $directivesMapper->selectSpecificDirectives(array('zend_monitor.gui_host_name'));
        $guiHostName = $guiHostName->current()->getFileValue();
        
        if (empty($guiHostName)) {
        	$guiHostName = '127.0.0.1';
        }
        
        $uri = UriFactory::factory($guiHostName, 'http');

		if (! $uri->isAbsolute()) {
			$uri->setHost($guiHostName);
		}
        
        if (is_null($uri->getScheme())) {
        	$uri->setScheme('http');
        }

        if ($uri->getScheme() == 'https') {
	        $uri->setPort(Module::config('installation', 'securedPort'));
        } else {
	        $uri->setPort(Module::config('installation', 'defaultPort'));
        }
        
        $uri->setPath(Module::config('baseUrl'));

        // create external url
        $baseUrl = $uri->toString();
        $params['templateParams'] = array_merge($params['templateParams'], array('baseUrl' => $baseUrl));
        
        // render the main content
        $vsModel = new ViewModel();
        $vsModel->setTemplate($params['templateName']);
        $vsModel->setVariables($params['templateParams']);
        $mainContent = $renderer->render($vsModel);
        
        // render the layout
        $vsLayout = new ViewModel();
        $vsLayout->setTemplate('layout.phtml');
        $vsLayout->setVariable('content', $mainContent);
        
        	
        try {
	        $body = $renderer->render($vsLayout);
        } catch (\Exception $ex) {
        	// Get back the default suffix and paths to render WebAPI json/xml template
        	$resolver->setDefaultSuffix($defaultSuffix);
        	$resolver->setPaths($paths);
        	$renderer->setResolver($resolver);
        	throw new Exception(vsprintf('Email content rendering failed: %s', array($ex->getMessage())), Exception::INTERNAL_SERVER_ERROR, $ex);
        }
        
        $subject = $renderer->emailSubject()->getStoredSubject();
        if (! $subject) {
        	$subject = $params['subject'];
        }
        $subject = '[Zend Server] ' . $subject;
        
        // Get back the default suffix and paths to render WebAPI json/xml template 
        $resolver->setDefaultSuffix($defaultSuffix);
        $resolver->setPaths($paths);
        $renderer->setResolver($resolver);
        
        // use html content
        if ($useHtml) {
        	$html = new MimePart($body);
        	$html->type = "text/html";
        	$body = new MimeMessage();
        	$body->setParts(array($html));
        }
        
        $message = new Message();
        $message->setBody($body);
        
        // support email or AddressList
        $addressList = new AddressList();
        if (strstr($params['to'], ',')) {
        	$emails = explode(',', $params['to']);
        	$message->setTo($emails, $params['toName']);
        } else {        	
        	$message->setTo($params['to'], $params['toName']);
        }
        
        $message->setFrom($params['from'], $params['fromName']);
        $message->setSubject($subject);
        if ($params['replyTo']) {
            $message->setReplyTo($params['replyTo']);
        } elseif (Module::config('mail', 'return_to_address')) {
        	$message->setReplyTo(Module::config('mail', 'return_to_address'));
        }
        if ($params['headers']) {
            $headers = $message->getHeaders();
            
            foreach ($params['headers'] as $header) {
                $headers->addHeaderLine($header);
            }
            $message->setHeaders($headers);
        }
        
        if (($mailType = Module::config('mail', 'mail_type')) == 'smtp') {
        	try {
	            if (! Module::config('mail', 'mail_host')) {
	                Log::err('The SMTP host is missing');
	                throw new WebAPI\Exception('The SMTP host is missing', WebAPI\Exception::SMTP_HOST_IS_MISSING);
	            }
	            if (! Module::config('mail', 'mail_port')) {
	                Log::err('The SMTP port is missing');
	                throw new WebAPI\Exception('The SMTP port is missing', WebAPI\Exception::SMTP_PORT_IS_MISSING);
	            }
	           	if (! Module::config('mail', 'authentication_method')) {
					Log::err('The SMTP authentication method is missing');
					throw new WebAPI\Exception('The SMTP authentication method is missing', WebAPI\Exception::SMTP_AUTHENTICATION_METHOD_IS_MISSING);
				}
        	} catch (WebAPI\Exception $ex) {
        		$this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_MAIL_SETTINGS_NOT_SET);
        		throw $ex;
        	}
			$transport = new Transport\Smtp();
			
			$options = array(
					'host' => Module::config('mail', 'mail_host'),
					'port' => Module::config('mail', 'mail_port'),
			);
			
			$conConf = array();
			if (Module::config('mail', 'authentication')) {
				$options['connection_class'] = Module::config('mail', 'authentication_method');
				
				$conConf = array(
						'username' => Module::config('mail', 'mail_username'),
						'password' => Module::config('mail', 'mail_password'),
				);
	            
	            if (in_array(Module::config('mail', 'mail_ssl'), array('ssl', 'tls'))) {
	            	$conConf['ssl'] = Module::config('mail', 'mail_ssl');
	            }
			}
		
			$smtpOption = new Transport\SmtpOptions($options);
            
            $smtpOption->setConnectionConfig($conConf);
            $transport->setOptions($smtpOption);
        } else if(($mailType = Module::config('mail', 'mail_type')) == 'sendmail') {
            $transport = new Transport\Sendmail();
        } else {
            Log::err('Unknown transport type');
            throw new WebAPI\Exception('Unknown transport type', WebAPI\Exception::EMAIL_SEND_TRANSPORT_IS_MISSING);
        }
        
        try {
            $transport->send($message);
            Log::info("Sent email message with type {$mailType}");
        } catch (\Exception $e) {
        	Log::info("Email sending failed");
        	Log::debug($e);
            throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
        }
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate('email/web-api/1x3/email-send');
        $viewModel->setVariable('status', 'OK');
        return $viewModel;
    }
    
    protected function getTemplatePath($path) {
    	if (realpath($path)) {
    		return $path;
    	}
    	
    	return ZEND_SERVER_GUI_PATH . DIRECTORY_SEPARATOR . $path; // relative path
    }
    
    protected function validateEmailString($value, $key) {
    	if (strstr($value, ',')) {
    		$emails = explode(',', $value);
    		foreach ($emails as $email) {
    			if ($email) {
    				$this->validateEmailAddress($email, $key);
    			}
    		}
    	} else {
    		$this->validateEmailAddress($value, $key);
    	}
    }
}