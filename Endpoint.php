<?php

/**
 * Core Framework - RolesEndpoint
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Objects;
use \LaswitchTech\Core\Abstracts\Endpoint;

class RolesEndpoint extends Endpoint {

    /**
     * Constructor
     */
    public function __construct()
    {

        // Call Parent Constructor
        parent::__construct();

        // Retrieve the namespace
        $namespace = $this->Request->getNamespace();

        // Set Global access
        $this->Public = false;

        // Set Level
        switch($namespace){
            case "/roles/index":
            case "/roles/groups":
            case "/roles/users":
            case "/roles/fetch":
                $this->Level = 1;
                break;
            case "/roles/update":
                $this->Level = 3;
                break;
        }
    }

    /**
     * Fetch all roles
     */
    public function indexAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => $this->Model->Roles->list()];

        // Return the message
        return $message;
    }

    /**
     * Fetch a Role's Information
     */
    public function fetchAction(): array
    {
        // Import Global Variables
        global $CONFIG;

        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => [
            "record" => $this->Model->Roles->get(intval($this->Request->getParams('GET', 'id')))
        ]];

        // Initialize the Permissions
        $message['data']['permissions'] = [
            "Development",
            "Developer",
            "Administration",
            "Administrator",
            "AccountManager",
            "BusinessAccountManager",
        ];

        // Retrieve the main routes
        $routes = $CONFIG->get('routes');

        // Set Plugins Path
        $pluginsPath = $CONFIG->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';

        // Load Plugins Routes
        if(is_dir($pluginsPath)){
            foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
                $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin;
                if(is_file($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg')){
                    foreach(json_decode(file_get_contents($pluginPath . DIRECTORY_SEPARATOR . 'routes.cfg'),true) as $route => $param){
                        $routes[$route] = $param;
                    }
                }
            }
        }

        // Loop through the routes
        foreach($routes as $route => $param){
            $message['data']['permissions'][] = 'Route>'.$route;
        }

        // Set Controller Path
        $controllersPath = $CONFIG->root() . DIRECTORY_SEPARATOR . 'Controller';

        // Retrieve the main Controllers
        if(is_dir($controllersPath)){
            foreach(array_diff(scandir($controllersPath), array('..', '.')) as $controller){
                $controllerPath = $controllersPath . DIRECTORY_SEPARATOR . $controller;
                $controller = strtolower(str_replace('Controller', '', str_replace('.php', '', $controller)));
                if(is_file($controllerPath)){
                    $content = file_get_contents($controllerPath);
                    if(preg_match('/class\s+(\w+)\s+extends\s+Controller/', $content, $matches)){
                        $class = $matches[1];
                        if($class == ucfirst($controller).'Controller'){
                            preg_match_all('/public\s+function\s+(\w+)Action\(/', $content, $matches);
                            foreach($matches[1] as $method){
                                $message['data']['permissions'][] = 'Controller>/'.$controller.'/'.$method;
                            }
                        }
                    }
                }
            }
        }

        // Load Plugins Controllers
        if(is_dir($pluginsPath)){
            foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
                $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'Controller.php';
                if(is_file($pluginPath)){
                    $content = file_get_contents($pluginPath);
                    if(preg_match('/class\s+(\w+)\s+extends\s+Controller/', $content, $matches)){
                        $class = $matches[1];
                        if($class == ucfirst($plugin).'Controller'){
                            preg_match_all('/public\s+function\s+(\w+)Action\(/', $content, $matches);
                            foreach($matches[1] as $method){
                                $message['data']['permissions'][] = 'Controller>/'.$plugin.'/'.$method;
                            }
                        }
                    }
                }
            }
        }

        // Set Endpoint Path
        $endpointsPath = $CONFIG->root() . DIRECTORY_SEPARATOR . 'Endpoint';

        // Retrieve the main Endpoints
        if(is_dir($endpointsPath)){
            foreach(array_diff(scandir($endpointsPath), array('..', '.')) as $endpoint){
                $endpointPath = $endpointsPath . DIRECTORY_SEPARATOR . $endpoint;
                $endpoint = strtolower(str_replace('Endpoint', '', str_replace('.php', '', $endpoint)));
                if(is_file($endpointPath)){
                    $content = file_get_contents($endpointPath);
                    if(preg_match('/class\s+(\w+)\s+extends\s+Endpoint/', $content, $matches)){
                        $class = $matches[1];
                        if($class == ucfirst($endpoint).'Endpoint'){
                            preg_match_all('/public\s+function\s+(\w+)Action\(/', $content, $matches);
                            foreach($matches[1] as $method){
                                $message['data']['permissions'][] = 'Endpoint>/'.$endpoint.'/'.$method;
                            }
                        }
                    }
                }
            }
        }

        // Load Plugins Endpoints
        if(is_dir($pluginsPath)){
            foreach(array_diff(scandir($pluginsPath), array('..', '.')) as $plugin){
                $pluginPath = $pluginsPath . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . 'Endpoint.php';
                if(is_file($pluginPath)){
                    $content = file_get_contents($pluginPath);
                    if(preg_match('/class\s+(\w+)\s+extends\s+Endpoint/', $content, $matches)){
                        $class = $matches[1];
                        if($class == ucfirst($plugin).'Endpoint'){
                            preg_match_all('/public\s+function\s+(\w+)Action\(/', $content, $matches);
                            foreach($matches[1] as $method){
                                $message['data']['permissions'][] = 'Endpoint>/'.$plugin.'/'.$method;
                            }
                        }
                    }
                }
            }
        }

        // Retrieve the Categories
        foreach($this->Model->Category->all() as $category){
            $message['data']['permissions'][] = 'Category>'.$category['targetTable'].'/'.$category['name'];
        }

        // Retrieve the DocTypes
        foreach($this->Model->Documents->types(true) as $doctype){
            if(!in_array('DocType>'.$doctype['name'], $message['data']['permissions'])){
                $message['data']['permissions'][] = 'DocType>'.$doctype['name'];
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Update a vCard
     */
    public function updateAction(): array
    {
        // Import Global Variables
        global $CSRF;

        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => []];

        // Check the request method
        if($this->Request->getMethod() == "POST"){
            $message["data"]["CSRF"] = [
                "token" => $CSRF->token(),
                "key" => $CSRF->key()
            ];
        }

        // Retrieve the role id
        $id = intval($this->Request->getParams('REQUEST','id'));

        // Retrieve the role
        $role = $this->Model->Roles->get($id, false);

        // Check if the role exists
        if(empty($role)){
            $message = ["status" => 404, "message" => "Not Found", "data" => "Could not find the requested role."];
        }

        // Check if the role is accessible
        if($message['status'] == 200){

            // Check the request method
            if($this->Request->getMethod() == "POST"){

                // Retrieve the parameters
                $parameters = $this->Request->getParams('REQUEST');

                // Initialize the Events
                $message['data']['events'] = [];

                // Update the role
                foreach($parameters as $key => $value){
                    if(isset($role[$key])){
                        switch($key){
                            case 'users':
                            case 'groups':
                                if(is_array($value)){
                                    $role[$key] = [];
                                    foreach($value as $objId){
                                        $role[$key][] = intval($objId);
                                    }
                                    $role[$key] = array_unique($role[$key]);
                                } else {
                                    $role[$key] = $value;
                                }
                                break;
                            case 'permissions':
                                $role[$key] = [];
                                foreach($value as $permission => $level){
                                    $role[$key][$permission] = intval($level);
                                }
                                break;
                            case 'isDefault':
                                $role[$key] = intval(filter_var($value, FILTER_VALIDATE_BOOLEAN));
                                break;
                            default:
                                $role[$key] = $value;
                                break;
                        }
                    }
                }

                // Update the role
                $affectedRows = $this->Model->Roles->update($id, $role);

                // Retrieve the final role
                $message['data']['record'] = $this->Model->Roles->get($id);
            } else {
                $message = ["status" => 405, "message" => "Method Not Allowed", "data" => "The method is not allowed for the requested URL."];
            }
        }

        return $message;
    }

    /**
     * Fetch all groups
     */
    public function groupsAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => $this->Model->Roles->groups()];

        // Return the message
        return $message;
    }

    /**
     * Fetch all users
     */
    public function usersAction(): array
    {
        // Set the default message
        $message = ["status" => 200, "message" => "OK", "data" => $this->Model->Roles->users()];

        // Return the message
        return $message;
    }
}
