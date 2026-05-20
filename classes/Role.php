<?php
/**
 * Role Class
 * 
 * Defines role constants and role-related utilities
 * to avoid hard-coded strings and typos
 */

class Role {
    // Role constants
    const SUPER_ADMIN = 'super_admin';
    const ADMIN = 'admin';
    const PESERTA = 'peserta';
    
    // Role hierarchy (higher number = more permissions)
    private static $hierarchy = [
        self::SUPER_ADMIN => 3,
        self::ADMIN => 2,
        self::PESERTA => 1,
    ];
    
    // Role display names
    private static $displayNames = [
        self::SUPER_ADMIN => 'Super Administrator',
        self::ADMIN => 'Administrator',
        self::PESERTA => 'Peserta Magang',
    ];
    
    /**
     * Get all available roles
     */
    public static function all() {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::PESERTA,
        ];
    }
    
    /**
     * Check if role is valid
     */
    public static function isValid($role) {
        return in_array($role, self::all());
    }
    
    /**
     * Get role display name
     */
    public static function getDisplayName($role) {
        return self::$displayNames[$role] ?? $role;
    }
    
    /**
     * Get role hierarchy level
     */
    public static function getLevel($role) {
        return self::$hierarchy[$role] ?? 0;
    }
    
    /**
     * Check if user role has permission based on hierarchy
     * 
     * @param string $userRole Current user's role
     * @param string $requiredRole Required role for action
     * @return bool
     */
    public static function hasPermission($userRole, $requiredRole) {
        $userLevel = self::getLevel($userRole);
        $requiredLevel = self::getLevel($requiredRole);
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Get roles that can be assigned by current user
     * 
     * @param string $userRole Current user's role
     * @return array
     */
    public static function getAssignableRoles($userRole) {
        $userLevel = self::getLevel($userRole);
        $assignable = [];
        
        foreach (self::all() as $role) {
            $roleLevel = self::getLevel($role);
            
            // Can only assign roles lower than or equal to own level
            // Super admin can assign all roles
            if ($userRole === self::SUPER_ADMIN || $roleLevel < $userLevel) {
                $assignable[] = $role;
            }
        }
        
        return $assignable;
    }
    
    /**
     * Check if user can manage another user based on roles
     * 
     * @param string $managerRole Manager's role
     * @param string $targetRole Target user's role
     * @return bool
     */
    public static function canManage($managerRole, $targetRole) {
        // Super admin can manage everyone
        if ($managerRole === self::SUPER_ADMIN) {
            return true;
        }
        
        // Admin can manage peserta only
        if ($managerRole === self::ADMIN && $targetRole === self::PESERTA) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get default redirect path for role
     */
    public static function getDefaultPath($role) {
        switch ($role) {
            case self::SUPER_ADMIN:
            case self::ADMIN:
                return '/admin';
            case self::PESERTA:
                return '/dashboard';
            default:
                return '/';
        }
    }
}
