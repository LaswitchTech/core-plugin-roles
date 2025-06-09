<?php

/**
 * Core Framework - RolesModel
 *
 * @license    MIT (https://mit-license.org/)
 * @author     Louis Ouellet <louis@laswitchtech.com>
 */

// Import additionnal class into the global namespace
use \LaswitchTech\Core\Abstracts\Model;

class RolesModel extends Model {

    /**
     * Retrieve the list of Roles
     *
     * @return array
     */
    public function list(): array
    {
        // Retrieve the Roles
        $Query = $this->Database->query()
            ->table('roles')
            ->select('*')
            ->join('owner', 'users', 'username')
            ->where('id', 9999, '<>');

        // Fetch the Roles
        $roles = $Query->fetch();

        // Sanitize the Roles
        foreach($roles as $key => $role){
            $role['users'] = json_decode($role['users'] ?? '[]', true);
            $role['groups'] = json_decode($role['groups'] ?? '[]', true);
            $role['permissions'] = json_decode($role['permissions'] ?? '[]', true);
            $roles[$key] = $role;
        }

        // Return the Roles
        return $roles;
    }

    /**
     * Retrieve Roles's Details
     *
     * @param int $id
     * @param bool $all
     * @return array
     */
    public function get(int $id, bool $all = true): array
    {
        // Retrieve the Role
        $Query = $this->Database->query()
            ->table('roles')
            ->select('*')
            ->where('id', $id)
            ->where('id', 9999, '<>')
            ->limit(1);

        // Fetch the Roles
        $roles = $Query->fetch();

        // Loop through the Roles
        foreach($roles as $key => $role){

            // Decode JSON Fields
            $role['users'] = json_decode($role['users'] ?? '[]', true);
            $role['groups'] = json_decode($role['groups'] ?? '[]', true);
            $role['permissions'] = json_decode($role['permissions'] ?? '[]', true);

            // Check if all the details should be retrieved
            if($all){

                // Arrange the permissions as a table
                $permissions = [];
                foreach($role['permissions'] as $permission => $level){
                    $permissions[] = ["permission" => $permission, "level" => $level];
                }
                $role['permissions'] = $permissions;

                // Retrieve the Users
                $users = [];
                foreach($role['users'] as $key => $user){

                    // Retrieve the User
                    $Query = $this->Database->query()
                        ->table('users')
                        ->select('*')
                        ->join('owner', 'users', 'username')
                        ->join('vcard', 'vcards', 'id')
                        ->where('id', 9999, '<>')
                        ->where('id', $user)
                        ->limit(1);
                    $users[$user] = $Query->fetch()[0] ?? [];
                }
                $role['users'] = $users;

                // Retrieve the Groups
                $groups = [];
                foreach($role['groups'] as $key => $group){

                    // Retrieve the User
                    $Query = $this->Database->query()
                        ->table('groups')
                        ->select('*')
                        ->join('owner', 'users', 'username')
                        ->where('id', 9999, '<>')
                        ->where('id', $group)
                        ->limit(1);
                    $groups[$group] = $Query->fetch()[0] ?? [];
                }
                $role['groups'] = $groups;

                // Retrieve the Events
                $Query = $this->Database->query()
                    ->table('events')
                    ->select('*')
                    ->where('targetTable', 'roles')
                    ->where('targetId', $role['id'])
                    ->where('id', 9999, '<>')
                    ->index('id');
                $role['events'] = $Query->result();
            }

            // Save the Role
            return $role;
        }

        // Return the Role
        return [];
    }

    /**
     * Create a new role and return the id
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('roles')
            ->insert($data);

        // Execute the Query
        $affectedRows = $Query->execute();

        // Execute the Query
        return $Query->lastId();
    }

    /**
     * Update a role
     *
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        // Create the Query
        $Query = $this->Database->query()
            ->table('roles')
            ->update($data)
            ->where('id', $id);

        // Execute the Query
        return $Query->execute();
    }

    /**
     * Retrieve the list of Groups
     *
     * @return array
     */
    public function groups(): array
    {
        // Retrieve the Roles
        $Query = $this->Database->query()
            ->table('groups')
            ->select('*')
            ->join('owner', 'users', 'username')
            ->where('id', 9999, '<>')
            ->index('id');

        // Fetch the Roles
        $groups = $Query->fetch();

        // Sanitize the Roles
        foreach($groups as $key => $group){
            $group['users'] = json_decode($group['users'] ?? '[]', true);
            $groups[$key] = $group;
        }

        // Return the Groups
        return $groups;
    }

    /**
     * Retrieve the list of Users
     *
     * @return array
     */
    public function users(): array
    {
        // Retrieve the Roles
        $Query = $this->Database->query()
            ->table('users')
            ->select('*')
            ->join('owner', 'users', 'username')
            ->join('vcard', 'vcards', 'id')
            ->where('id', 9999, '<>')
            ->index('id');

        // Fetch the Roles
        $users = $Query->fetch();

        // Sanitize the Roles
        foreach($users as $key => $user){
            $user['vcard']['tags'] = json_decode($user['vcard']['tags'] ?? '[]', true);
            $user['vcard']['industries'] = json_decode($user['vcard']['industries'] ?? '[]', true);
            $users[$key] = $user;
        }

        // Return the Users
        return $users;
    }
}
