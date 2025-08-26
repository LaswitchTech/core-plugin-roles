<?php

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Base\BaseModel;

class RolesModel extends BaseModel {

    /**
     * Constructor
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Initialize the Model
        $this->init('roles');
    }

    /**
     * Process a record
     *
     * @param array $record
     * @return array
     */
    protected function process(array $record): array
    {
        // Call the parent constructor
        $record = parent::process($record);

        // Process the JSON fields
        if(!is_array($record['users'])){
            $record['users'] = json_decode($record['users'] ?? "[]", true);
        }
        if(!is_array($record['groups'])){
            $record['groups'] = json_decode($record['groups'] ?? "[]", true);
        }
        if(!is_array($record['permissions'])){
            $record['permissions'] = json_decode($record['permissions'] ?? "[]", true);
        }

        // Return the processed record
        return $record;
    }

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        // Sanitize the Data
        foreach($data as $key => $value){

            // Add exceptions for specific fields
            if(in_array($key, ['users','groups']) && is_array($value)){

                // Loop through each value in the array
                foreach($value as $subkey => $subvalue){

                    // Convert the value to an integer
                    $value[$subkey] = (int)$subvalue;
                }

                // Filter unique user IDs
                $value = array_unique($value);

                // Sort the array
                sort($value);
            }

            // Add exceptions for specific fields
            if(in_array($key, ['permissions']) && is_array($value)){

                // Loop through each value in the array
                foreach($value as $subkey => $subvalue){

                    // Convert the value to an integer
                    $value[$subkey] = (int)$subvalue;
                }

                // Sort the array by key
                ksort($value);
            }

            // Check if permissions is not an array
            if(in_array($key, ['permissions']) && !is_array($value)){

                // Create an empty array
                $value = [];
            }

            // Add exceptions for specific fields
            if(in_array($key, ['isDefault'])){

                // Filter boolean
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            // Set the value back to the data array
            $data[$key] = $value;
        }

        // Call the parent update method
        return parent::update($id, $data);
    }
}
