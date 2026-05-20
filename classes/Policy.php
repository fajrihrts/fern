<?php
/**
 * Policy Base Class
 * 
 * Base class for resource-level authorization policies
 */

abstract class Policy {
    /**
     * Check if user can view resource
     */
    abstract public static function view($user, $resource);
    
    /**
     * Check if user can create resource
     */
    abstract public static function create($user);
    
    /**
     * Check if user can update resource
     */
    abstract public static function update($user, $resource);
    
    /**
     * Check if user can delete resource
     */
    abstract public static function delete($user, $resource);
}

/**
 * Registration Policy
 * 
 * Authorization rules for registration resources
 */
class RegistrationPolicy extends Policy {
    /**
     * Check if user can view registration
     */
    public static function view($user, $registration) {
        // Owner can view
        if ($user['id'] === $registration['user_id']) {
            return true;
        }
        
        // Admin can view all
        if (Permission::can(Permission::VIEW_REGISTRATIONS)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can create registration
     */
    public static function create($user) {
        // Peserta can create
        if (Permission::can(Permission::CREATE_REGISTRATION)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can update registration
     */
    public static function update($user, $registration) {
        // Can only update own registration
        if ($user['id'] !== $registration['user_id']) {
            return false;
        }
        
        // Can only update if pending
        if ($registration['status'] !== 'pending') {
            return false;
        }
        
        // Must have permission
        if (!Permission::can(Permission::EDIT_OWN_REGISTRATION)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user can delete registration
     */
    public static function delete($user, $registration) {
        // Only admin can delete
        return Permission::can(Permission::DELETE_REGISTRATIONS);
    }
    
    /**
     * Check if user can approve registration
     */
    public static function approve($user, $registration) {
        return Permission::can(Permission::APPROVE_REGISTRATIONS);
    }
    
    /**
     * Check if user can reject registration
     */
    public static function reject($user, $registration) {
        return Permission::can(Permission::REJECT_REGISTRATIONS);
    }
}

/**
 * Attendance Policy
 * 
 * Authorization rules for attendance resources
 */
class AttendancePolicy extends Policy {
    /**
     * Check if user can view attendance
     */
    public static function view($user, $attendance) {
        // Owner can view
        if ($user['id'] === $attendance['user_id']) {
            return true;
        }
        
        // Admin can view all
        if (Permission::can(Permission::VIEW_ATTENDANCE)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can create attendance
     */
    public static function create($user) {
        // Must have permission
        if (!Permission::can(Permission::CREATE_ATTENDANCE)) {
            return false;
        }
        
        // Must have approved registration
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT * FROM registrations 
            WHERE user_id = ? AND status = 'approved'
            LIMIT 1
        ");
        $stmt->execute([$user['id']]);
        $registration = $stmt->fetch();
        
        if (!$registration) {
            return false;
        }
        
        // Check if within internship period
        $today = date('Y-m-d');
        if ($today < $registration['start_date'] || $today > $registration['end_date']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user can update attendance
     */
    public static function update($user, $attendance) {
        // Cannot update attendance (only create/delete)
        return false;
    }
    
    /**
     * Check if user can delete attendance
     */
    public static function delete($user, $attendance) {
        // Owner can delete own attendance (same day only)
        if ($user['id'] === $attendance['user_id']) {
            $attendanceDate = date('Y-m-d', strtotime($attendance['created_at']));
            $today = date('Y-m-d');
            
            if ($attendanceDate === $today) {
                return true;
            }
        }
        
        // Admin can delete any attendance
        if (Permission::can(Permission::DELETE_ATTENDANCE)) {
            return true;
        }
        
        return false;
    }
}

/**
 * User Policy
 * 
 * Authorization rules for user resources
 */
class UserPolicy extends Policy {
    /**
     * Check if user can view another user
     */
    public static function view($user, $targetUser) {
        // Can view own profile
        if ($user['id'] === $targetUser['id']) {
            return true;
        }
        
        // Admin can view all users
        if (Permission::can(Permission::VIEW_USERS)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can create user
     */
    public static function create($user) {
        // Only super admin can create admin users
        return Permission::can(Permission::CREATE_ADMIN);
    }
    
    /**
     * Check if user can update another user
     */
    public static function update($user, $targetUser) {
        // Can update own profile
        if ($user['id'] === $targetUser['id']) {
            return Permission::can(Permission::EDIT_OWN_PROFILE);
        }
        
        // Check if can manage target user based on role
        if (!Role::canManage($user['role'], $targetUser['role'])) {
            return false;
        }
        
        // Must have permission
        return Permission::can(Permission::EDIT_USERS);
    }
    
    /**
     * Check if user can delete another user
     */
    public static function delete($user, $targetUser) {
        // Cannot delete self
        if ($user['id'] === $targetUser['id']) {
            return false;
        }
        
        // Check if can manage target user based on role
        if (!Role::canManage($user['role'], $targetUser['role'])) {
            return false;
        }
        
        // Must have permission
        if ($targetUser['role'] === Role::PESERTA) {
            return Permission::can(Permission::DELETE_USERS);
        } else {
            return Permission::can(Permission::DELETE_ADMIN);
        }
    }
}

/**
 * Testimonial Policy
 * 
 * Authorization rules for testimonial resources
 */
class TestimonialPolicy extends Policy {
    /**
     * Check if user can view testimonial
     */
    public static function view($user, $testimonial) {
        // Everyone can view approved testimonials
        if ($testimonial['status'] === 'approved') {
            return true;
        }
        
        // Owner can view own testimonial
        if ($user['id'] === $testimonial['user_id']) {
            return true;
        }
        
        // Admin can view all
        if (Permission::can(Permission::MANAGE_TESTIMONIALS)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can create testimonial
     */
    public static function create($user) {
        // Must have permission
        if (!Permission::can(Permission::CREATE_TESTIMONIAL)) {
            return false;
        }
        
        // Must have completed internship
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT * FROM registrations 
            WHERE user_id = ? 
            AND status = 'approved'
            AND end_date < CURDATE()
            LIMIT 1
        ");
        $stmt->execute([$user['id']]);
        $registration = $stmt->fetch();
        
        return $registration !== false;
    }
    
    /**
     * Check if user can update testimonial
     */
    public static function update($user, $testimonial) {
        // Cannot update testimonial (only create/delete)
        return false;
    }
    
    /**
     * Check if user can delete testimonial
     */
    public static function delete($user, $testimonial) {
        // Owner can delete own pending testimonial
        if ($user['id'] === $testimonial['user_id'] && 
            $testimonial['status'] === 'pending') {
            return true;
        }
        
        // Admin can delete any testimonial
        if (Permission::can(Permission::MANAGE_TESTIMONIALS)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can approve testimonial
     */
    public static function approve($user, $testimonial) {
        return Permission::can(Permission::MANAGE_TESTIMONIALS);
    }
}
