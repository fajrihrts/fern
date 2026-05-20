<?php
/**
 * Permission Class
 * 
 * Defines permissions and role-permission mappings
 * for granular access control
 */

class Permission {
    // User/Peserta Permissions
    const VIEW_OWN_PROFILE = 'view_own_profile';
    const EDIT_OWN_PROFILE = 'edit_own_profile';
    const CREATE_REGISTRATION = 'create_registration';
    const EDIT_OWN_REGISTRATION = 'edit_own_registration';
    const VIEW_OWN_ATTENDANCE = 'view_own_attendance';
    const CREATE_ATTENDANCE = 'create_attendance';
    const CREATE_TESTIMONIAL = 'create_testimonial';
    
    // Admin Permissions
    const VIEW_DASHBOARD = 'view_dashboard';
    const VIEW_USERS = 'view_users';
    const EDIT_USERS = 'edit_users';
    const VIEW_REGISTRATIONS = 'view_registrations';
    const APPROVE_REGISTRATIONS = 'approve_registrations';
    const REJECT_REGISTRATIONS = 'reject_registrations';
    const DELETE_REGISTRATIONS = 'delete_registrations';
    const VIEW_ATTENDANCE = 'view_attendance';
    const DELETE_ATTENDANCE = 'delete_attendance';
    const EXPORT_DATA = 'export_data';
    const MANAGE_POSTS = 'manage_posts';
    const MANAGE_TESTIMONIALS = 'manage_testimonials';
    const VIEW_ACTIVITY_LOGS = 'view_activity_logs';
    
    // Super Admin Permissions
    const MANAGE_ADMINS = 'manage_admins';
    const CREATE_ADMIN = 'create_admin';
    const DELETE_ADMIN = 'delete_admin';
    const DELETE_USERS = 'delete_users';
    const SYSTEM_SETTINGS = 'system_settings';
    const VIEW_ALL_LOGS = 'view_all_logs';
    
    /**
     * Role-Permission mapping
     */
    private static $rolePermissions = [
        Role::SUPER_ADMIN => [
            // All permissions
            self::VIEW_OWN_PROFILE,
            self::EDIT_OWN_PROFILE,
            self::CREATE_REGISTRATION,
            self::EDIT_OWN_REGISTRATION,
            self::VIEW_OWN_ATTENDANCE,
            self::CREATE_ATTENDANCE,
            self::CREATE_TESTIMONIAL,
            self::VIEW_DASHBOARD,
            self::VIEW_USERS,
            self::EDIT_USERS,
            self::VIEW_REGISTRATIONS,
            self::APPROVE_REGISTRATIONS,
            self::REJECT_REGISTRATIONS,
            self::DELETE_REGISTRATIONS,
            self::VIEW_ATTENDANCE,
            self::DELETE_ATTENDANCE,
            self::EXPORT_DATA,
            self::MANAGE_POSTS,
            self::MANAGE_TESTIMONIALS,
            self::VIEW_ACTIVITY_LOGS,
            self::MANAGE_ADMINS,
            self::CREATE_ADMIN,
            self::DELETE_ADMIN,
            self::DELETE_USERS,
            self::SYSTEM_SETTINGS,
            self::VIEW_ALL_LOGS,
        ],
        
        Role::ADMIN => [
            self::VIEW_OWN_PROFILE,
            self::EDIT_OWN_PROFILE,
            self::VIEW_DASHBOARD,
            self::VIEW_USERS,
            self::EDIT_USERS,
            self::VIEW_REGISTRATIONS,
            self::APPROVE_REGISTRATIONS,
            self::REJECT_REGISTRATIONS,
            self::DELETE_REGISTRATIONS,
            self::VIEW_ATTENDANCE,
            self::DELETE_ATTENDANCE,
            self::EXPORT_DATA,
            self::MANAGE_POSTS,
            self::MANAGE_TESTIMONIALS,
            self::VIEW_ACTIVITY_LOGS,
        ],
        
        Role::PESERTA => [
            self::VIEW_OWN_PROFILE,
            self::EDIT_OWN_PROFILE,
            self::CREATE_REGISTRATION,
            self::EDIT_OWN_REGISTRATION,
            self::VIEW_OWN_ATTENDANCE,
            self::CREATE_ATTENDANCE,
            self::CREATE_TESTIMONIAL,
        ],
    ];
    
    /**
     * Check if current user has permission
     * 
     * @param string $permission Permission to check
     * @return bool
     */
    public static function can($permission) {
        $user = auth();
        if (!$user) {
            return false;
        }
        
        return self::userCan($user['role'], $permission);
    }
    
    /**
     * Check if user role has permission
     * 
     * @param string $role User role
     * @param string $permission Permission to check
     * @return bool
     */
    public static function userCan($role, $permission) {
        $permissions = self::$rolePermissions[$role] ?? [];
        return in_array($permission, $permissions);
    }
    
    /**
     * Check if current user has any of the permissions
     * 
     * @param array $permissions Array of permissions
     * @return bool
     */
    public static function canAny(array $permissions) {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if current user has all permissions
     * 
     * @param array $permissions Array of permissions
     * @return bool
     */
    public static function canAll(array $permissions) {
        foreach ($permissions as $permission) {
            if (!self::can($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get all permissions for a role
     * 
     * @param string $role Role name
     * @return array
     */
    public static function getPermissions($role) {
        return self::$rolePermissions[$role] ?? [];
    }
    
    /**
     * Get all available permissions
     * 
     * @return array
     */
    public static function all() {
        $reflection = new ReflectionClass(__CLASS__);
        return array_values($reflection->getConstants());
    }
}
