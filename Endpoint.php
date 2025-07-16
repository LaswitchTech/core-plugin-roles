<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Base\BaseEndpoint;

class RolesEndpoint extends BaseEndpoint {

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Initialize the Endpoint
        $this->init('roles');

        // Set Properties
        $this->required = ['name','permissions'];
    }

    /**
     * Retrieve a record
     */
    public function fetchAction(): array
    {
        // Call the parent constructor
        $message = parent::fetchAction();

        // Check if the records is accessible
        if($message['status'] == 200){

            // Check if the Users is accessible
            if($this->Helper->Core->isInstalled('users')){

                // Initialize the dependencies
                $message['data']['dependencies']['users'] = [];

                // Loop through the users to fetch them.
                foreach($message['data']['record']['users'] as $id){
                    $message['data']['dependencies']['users'][$id] = $this->Model->Users->fetch($id);
                }

                // Set the users in the record
                $message['data']['record']['users'] = $message['data']['dependencies']['users'];
            }

            // Check if the Groups is accessible
            if($this->Helper->Core->isInstalled('groups')){

                // Initialize the dependencies
                $message['data']['dependencies']['groups'] = [];

                // Loop through the groups to fetch them.
                foreach($message['data']['record']['groups'] as $id){
                    $message['data']['dependencies']['groups'][$id] = $this->Model->Groups->fetch($id);
                }

                // Set the groups in the record
                $message['data']['record']['groups'] = $message['data']['dependencies']['groups'];
            }

            // Check if the Notes is accessible
            if($this->Helper->Core->isInstalled('notes')){
                $message['data']['dependencies']['notes'] = $this->Model->Notes->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
            }

            // Check if the Relationship Plugin is accessible
            if($this->Helper->Core->isInstalled('relationship')){
                $message['data']['dependencies']['relationship'] = $this->Model->Relationship->get($this->basename, $message['data']['record']['id']);
                if($this->Helper->Core->isInstalled('vcards') && array_key_exists('vcard', $message['data']['record'])){
                    $message['data']['dependencies']['relationship'] = array_merge(
                        $message['data']['dependencies']['relationship'],
                        $this->Model->Relationship->get('vcards', $message['data']['record']['vcard']['id'])
                    );
                }
            }

            // Check if the Events is accessible
            if($this->Helper->Core->isInstalled('event')){
                $message['data']['dependencies']['event'] = $this->Model->Event->fetchAll([
                    ["key" => "targetTable", "operator" => "=", "value" => $this->basename],
                    ["key" => "targetId", "operator" => "=", "value" => $message['data']['record']['id']],
                    ["key" => "isArchived", "operator" => "<>", "value" => 1],
                ]);
            }

            // Initialize the Permissions
            $message['data']['dependencies']['permissions'] = [
                "Development",
                "Developer",
                "Administration",
                "Administrator",
                "AccountManager",
                "BusinessAccountManager",
            ];

            // Retrieve the main routes
            $routes = $this->Config->get('routes');

            // Set Plugins Path
            $pluginsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins';

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
                $message['data']['dependencies']['permissions'][] = 'Route>'.$route;
            }

            // Set Controller Path
            $controllersPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'Controller';

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
                                    $message['data']['dependencies']['permissions'][] = 'Controller>/'.$controller.'/'.$method;
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
                                    $message['data']['dependencies']['permissions'][] = 'Controller>/'.$plugin.'/'.$method;
                                }
                            }
                        }
                    }
                }
            }

            // Set Endpoint Path
            $endpointsPath = $this->Config->root() . DIRECTORY_SEPARATOR . 'Endpoint';

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
                                    $message['data']['dependencies']['permissions'][] = 'Endpoint>/'.$endpoint.'/'.$method;
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
                                    $message['data']['dependencies']['permissions'][] = 'Endpoint>/'.$plugin.'/'.$method;
                                }
                            }
                        }
                    }
                }
            }

            // Check if the Categories is accessible
            if($this->Helper->Core->isInstalled('categories')){

                // Retrieve the Categories
                foreach($this->Model->Categories->fetchAll() as $category){
                    $message['data']['dependencies']['permissions'][] = 'Category>'.$category['targetTable'].'/'.$category['name'];
                }
            }

            // Check if the Doctypes is accessible
            if($this->Helper->Core->isInstalled('doctypes')){

                // Retrieve the DocTypes
                foreach($this->Model->Doctypes->fetchAll() as $doctype){
                    if(!in_array('DocType>'.$doctype['name'], $message['data']['dependencies']['permissions'])){
                        $message['data']['dependencies']['permissions'][] = 'DocType>'.$doctype['name'];
                    }
                }
            }

            // Sort all permissions
            sort($message['data']['dependencies']['permissions']);
        }

        // Return the message
        return $message;
    }

    /**
     * Create a record
     */
    public function createAction(): array
    {
        // Call the parent constructor
        $message = parent::createAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Retrieve the parameters
            $parameters = $message['data']['parameters'];

            // Initialize the fields array
            $fields = [];

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Role',
                    'message' => 'New Role Created by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/roles/details?id='.$message['data']['record']['id'],
                    'targetTable' => 'roles',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);

                // Setup a new event for the target
                $event['link'] = '/plugin/'.$message['data']['record']['targetTable'].'/details?id='.$message['data']['record']['targetId'];
                $event['targetTable'] = $message['data']['record']['targetTable'];
                $event['targetId'] = $message['data']['record']['targetId'];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }

            // Check if $fields is empty
            if(!empty($fields)){
                $affectedRows = $this->Model->Roles->update($message['data']['record']['id'], $fields);
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Update a record
     */
    public function updateAction(): array
    {
        // Call the parent constructor
        $message = parent::updateAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Role',
                    'message' => 'Role Updated by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/roles/details?id='.$message['data']['record']['id'],
                    'targetTable' => 'roles',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Delete a record
     */
    public function deleteAction(): array
    {
        // Call the parent constructor
        $message = parent::deleteAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Role',
                    'message' => 'Role Deleted by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/roles/details?id='.$message['data']['record']['id'],
                    'targetTable' => 'roles',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);

                // Setup a new event for the target
                $event['link'] = '/plugin/'.$message['data']['record']['targetTable'].'/details?id='.$message['data']['record']['targetId'];
                $event['targetTable'] = $message['data']['record']['targetTable'];
                $event['targetId'] = $message['data']['record']['targetId'];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Archive a record
     */
    public function archiveAction(): array
    {
        // Call the parent constructor
        $message = parent::archiveAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Role',
                    'message' => 'Role Archived by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/roles/details?id='.$message['data']['record']['id'],
                    'targetTable' => 'roles',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);

                // Setup a new event for the target
                $event['link'] = '/plugin/'.$message['data']['record']['targetTable'].'/details?id='.$message['data']['record']['targetId'];
                $event['targetTable'] = $message['data']['record']['targetTable'];
                $event['targetId'] = $message['data']['record']['targetId'];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }
        }

        // Return the message
        return $message;
    }

    /**
     * Recover a record
     */
    public function recoverAction(): array
    {
        // Call the parent constructor
        $message = parent::recoverAction();

        // Check if the record is accessible
        if($message['status'] == 200){

            // Check if the Event Plugin is accessible
            if($this->Helper->Core->isInstalled('event')){

                // Initialize the Events
                $message['data']['event'] = [];

                // Setup a new event
                $event = [
                    'category' => 'Role',
                    'message' => 'Role Recovered by <vcard>'.$this->Auth->user()->vcard['id'].':'.$this->Auth->user()->username.'</vcard>',
                    'icon' => 'circle',
                    'color' => 'secondary',
                    'link' => '/plugin/roles/details?id='.$message['data']['record']['id'],
                    'targetTable' => 'roles',
                    'targetId' => $message['data']['record']['id'],
                ];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);

                // Setup a new event for the target
                $event['link'] = '/plugin/'.$message['data']['record']['targetTable'].'/details?id='.$message['data']['record']['targetId'];
                $event['targetTable'] = $message['data']['record']['targetTable'];
                $event['targetId'] = $message['data']['record']['targetId'];

                // Create the event
                $message['data']['event'][] = $this->Model->Event->create($event);
            }
        }

        // Return the message
        return $message;
    }
}
