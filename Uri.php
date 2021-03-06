<?php

namespace Panada\Http;

/**
 * Parse information from URI
 *
 * So if we have an URI like this: http://localhost:8081/project/panada/labs/v2POC/public/index.php/foo/bar/john/doe
 * where:
 *  - frontController: index.php
 *  - basePath: /project/panada/labs/v2POC/public/; a folder where front controller located. use for asstet
 *  - pathInfo: foo/bar/john/doe; every path right after frontController
 *  - location: /project/panada/labs/v2POC/public/index.php/foo/bar/john/doe; REQUEST URI excluding query
 *  - relLocation: /project/panada/labs/v2POC/public/index.php/; use for redirect or url href
 *
 * @author kandar <iskandarsoesman@gmail.com>
 */
class Uri extends \Panada\Utility\Factory
{
    protected $config = [
        'defaultController' => 'Home',
        'defaultAction' => 'index',
        'assetPath' => null,
        
        // including protocol URI ex: http://cdn.mysite.com or //cdn.mysite.com
        'assetBaseDomain' => null
    ];
    
    protected $frontController;
    protected $basePath;
    protected $pathInfo;
    protected $location;
    protected $pathSegment = [];
    protected $requestMethod = 'GET';
    protected $host;
    protected $queryString;
    protected $port = 80;
    protected $scheme = 'http';
    
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }
    
    public static function getInstance($config = [])
    {
        $child = __CLASS__;
        
        if (! isset(parent::$instance[$child])) {
            parent::$instance[$child] = (new static($config))->fromServer();
        }
        
        return self::$instance[$child];
    }
    
    public static function setInstance($config = [])
    {
        $child = __CLASS__;
        
        return parent::$instance[$child] = new Uri($config);
    }
    
    /**
     * The default value is equal to $this->basePath
     *
     * @param string $path Additional path or file for assets
     * @return string
     */
    public function getAssetURI($path = null)
    {
        return $this->config['assetBaseDomain'].$this->config['assetPath'].$path;
    }
    
    /**
     * override default configuration
     *
     * @param array $config
     * @return void
     */
    public function setConfig($config = [])
    {
        $this->config = array_merge($this->config, $config);
        
        return $this;
    }
    
    /**
     * Get path and URI information from PHP global $_SERVER vars.
     *
     * @return object An instance from this class
     */
    public function fromServer()
    {
        $this->basePath = str_replace(
            $_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']
        );
        
        $scriptPath             = explode('/', $_SERVER['SCRIPT_FILENAME']);
        $this->frontController  = end($scriptPath);
        
        $this->basePath = str_replace(
            $this->frontController, '', $this->basePath
        );
        
        $this->config['assetPath']= $this->basePath;
        
        $scriptName             = str_replace($this->frontController, '', $_SERVER['SCRIPT_NAME']);
        $requestURI             = str_replace($this->basePath.$this->frontController, '', $_SERVER['REQUEST_URI']);
        $this->pathInfo         = trim(strtok($requestURI, '?'), '/');
        $this->location         = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
        $this->pathSegment      = explode('/', $this->pathInfo);
        $this->relLocation      = str_replace($this->pathInfo, '', $this->location);
        $this->requestMethod    = $_SERVER['REQUEST_METHOD'];
        $this->host             = $_SERVER['SERVER_NAME'];
        $this->port             = $_SERVER['SERVER_PORT'];
        $this->scheme           = preg_replace('/[^a-z]/i', '', $_SERVER['SERVER_PROTOCOL']);
        
        if(isset($_SERVER['QUERY_STRING'])) {
            $this->queryString  = $_SERVER['QUERY_STRING'];
        }
        
        return $this;
    }
    
    /**
     * Just for development propose
     */
    public function debug()
    {
        echo 'SCRIPT_NAME: '.$_SERVER['SCRIPT_NAME'].'<br>';
        echo 'REQUEST_URI: '.$_SERVER['REQUEST_URI'].'<br><br>';
        echo 'frontController: '.$this->frontController.'<br>';
        echo 'basePath: '.$this->basePath.'<br>';
        echo 'pathInfo: '.$this->pathInfo.'<br>';
        echo 'location: '.$this->location.'<br>';
        print_r($this->pathSegment);
        exit;
    }
    
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        
        return $this;
    }
    
    public function getScheme()
    {
        return $this->scheme;
    }
    
    public function setPort($port)
    {
        $this->port = $port;
        
        return $this;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
        
        return $this;
    }
    
    public function setHost($host)
    {
        $this->host = $host;
        
        return $this;
    }
    
    public function getHost()
    {
        return $this->host;
    }
    
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        
        return $this;
    }
    
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }
    
    public function setFrontController($frontController)
    {
        $this->frontController = $frontController;
        
        return $this;
    }
    
    public function getFrontController()
    {
        return ltrim($this->frontController, '/');
    }
    
    public function setBasePath($basePath)
    {
        $this->basePath = $this->config['assetPath'] = $basePath;
        
        return $this;
    }
    
    public function getBasePath()
    {
        return $this->basePath;
    }
    
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;
        $this->pathSegment = explode('/', $this->pathInfo);
        
        return $this;
    }
    
    public function getPathInfo()
    {
        return $this->pathInfo;
    }
    
    public function setLocation($location)
    {
        $this->location = $location;
        $this->relLocation = $location;
        
        return $this;
    }
    
    public function getLocation()
    {
        return $this->location;
    }
    
    public function location($path = null)
    {
        return $this->relLocation.$path;
    }
    
    /*
     * @return array
     */
    public function getPathSegment()
    {
        return $this->pathSegment;
    }
    
    public function getSegment($segment = false)
    {
        if ($segment !== false) {
            return ( isset($this->pathSegment[$segment]) ) ? $this->pathSegment[$segment] : false;
        }

        return $this->pathSegment;
    }
    
    public function getController()
    {
        if ($uriString = $this->getSegment(0)) {
            if ($this->stripUriString($uriString)) {
                return $uriString;
            }

            throw new \Exception('Invalid controller name: ' . htmlentities($uriString));
        }

        return $this->config['defaultController'];
    }
    
    /**
     * Get action name from the url.
     *
     * @return  string
     */
    public function getAction()
    {
        $uriString = $this->getSegment(1);

        if (isset($uriString) && !empty($uriString)) {
            if ($this->stripUriString($uriString)) {
                return $uriString;
            }

            throw new \Exception('Invalid action name: ' . htmlentities($uriString));
        }

        return $this->config['defaultAction'];
    }

    /**
     * Get "GET" request from the url.
     *
     * @param    int
     * @return  array
     */
    public function getRequests($segment = 2)
    {
        $uriString = $this->getSegment($segment);

        if (isset($uriString)) {
            return array_slice($this->pathSegment, $segment);
        }

        return [];

    }
    
    /**
     * Cleaner for class and method name
     *
     * @param string
     * @return boolean
     */
    private function stripUriString($uri)
    {
        return (!preg_match('/[^a-zA-Z0-9_.-]/', $uri)) ? true : false;
    }
}
