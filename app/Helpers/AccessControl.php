<?php

namespace App\Helpers;

use App\Helpers\UserIdentity;
use App\Models\InchargeCampus;
use App\Models\InchargeSchool;
use App\Models\EventComanager;
use App\Helpers\EventPerm;

class AccessControl
{
    /**
     * Check if the current user is a Super Admin
     */
    public static function isAdmin()
    {
        return !empty($_SESSION['e_is_admin']) && $_SESSION['e_is_admin'] == 1;
    }

    /**
     * Check if the current user is a Manager (has event approval rights)
     */
    public static function isManager()
    {
        return !empty($_SESSION['e_is_manager']) && $_SESSION['e_is_manager'] == 1;
    }

    /**
     * Check if the current user is an incharge of any campus or school
     */
    public static function isIncharge()
    {
        if (empty($_SESSION['e_user_uid'])) {
            return false;
        }

        $userId = $_SESSION['e_user_uid'];

        // Check Campus Incharge
        $campusInchargeModel = new InchargeCampus();
        $managedCampuses = $campusInchargeModel->getManagedCampuses($userId);
        if (!empty($managedCampuses)) {
            return true;
        }

        // Check School Incharge
        $schoolInchargeModel = new InchargeSchool();
        $managedSchools = $schoolInchargeModel->getManagedSchools($userId);
        if (!empty($managedSchools)) {
            return true;
        }



        return false;
    }

    /**
     * Check if the current user OWNS a specific resource.
     * * @param string|int $resourceOwnerId The 'created_by' or 'user_id' from your DB record
     * @return bool
     */
    public static function owns($resourceOwnerId)
    {
        if (empty($_SESSION['e_user_id']) || empty($resourceOwnerId)) {
            return false;
        }

        // Normalize both IDs to ensure "ERP_500" matches "500" if mixed types exist
        $currentId = UserIdentity::normalize($_SESSION['e_user_id']);
        $ownerId   = UserIdentity::normalize($resourceOwnerId);

        return $currentId === $ownerId;
    }

    /**
     * The "God Mode" Check.
     * Returns TRUE if user is Admin OR if they own the resource.
     * Use this for 'Edit' and 'Delete' actions.
     */
    public static function canManage($resourceOwnerId)
    {
        // Admins can manage anything
        if (self::isAdmin()) {
            return true;
        }

        // Users can manage their own items
        return self::owns($resourceOwnerId);
    }

    /**
     * Check if a user can manage a specific event (owner, admin, manager, or comanager with permissions)
     *
     * @param int $eventId
     * @param mixed $resourceOwnerId Optional owner id to short-circuit ownership check
     * @param int $requiredPermission Optional permission bit to require for co-managers
     * @return bool
     */
    // public static function canManageEvent($eventId, $resourceOwnerId = null, $requiredPermission = EventPerm::EDIT_EVENT_FILE_CONTENT)
    // {
    //     // Admins can manage anything
    //     if (self::isAdmin()) {
    //         return true;
    //     }

    //     // echo "inside canManageEvent <br>"; 

    //     // If owner ID provided and current user owns it
    //     if ($resourceOwnerId !== null && self::owns($resourceOwnerId)) {
    //         return true;
    //     }

    //     // Session flagged managers can also manage
    //     if (self::isManager()) {
    //         return true;
    //     }

    //     // Check co-manager table for explicit permission
    //     if (empty($_SESSION['e_user_id'])) {
    //         return false;
    //     }

    //     $erpId = $_SESSION['e_user_id'];
    //     // echo "$erpId is checking co-manager permissions for event $eventId with required permission $requiredPermission";exit;
    //     $ec = new EventComanager();
    //     $record = $ec->findOne(['event_id' => $eventId, 'erp_id' => $erpId]);

    //     if (!$record) {
    //         return false;
    //     }

    //     $bitmask = (int)($record['perms_bitmask'] ?? 0);
    //     return EventComanager::hasPermission($bitmask, $requiredPermission);
    // }

    public static function canManageEvent($eventId, $resourceOwnerId = null, $requiredPermission = EventPerm::EDIT_EVENT_FILE_CONTENT)
    {
        // 1. Admins can manage anything
        if (self::isAdmin()) {
            return true;
        }

        // 2. If owner ID provided and current user owns it
        if ($resourceOwnerId !== null && self::owns($resourceOwnerId)) {
            return true;
        }

        // 3. Check if user is an Incharge for THIS specific event
        if (!empty($_SESSION['e_user_uid'])) {
            $userId = $_SESSION['e_user_uid'];

            // We need the event details to know which campus/school it belongs to
            // Note: Adjust the model instantiation and fetch method to match your framework
            $eventModel = new \App\Models\Event();
            $event = $eventModel->getEventByIdWithDetailsForManager($eventId);

            // echo "Event id: $eventId <pre>"; print_r($event); echo "</pre>"; die; // Debug: Check event details

            if ($event) {
                // Check Campus Incharge
                $campusInchargeModel = new InchargeCampus();
                $managedCampuses = $campusInchargeModel->getManagedCampuses($userId);

                foreach ($managedCampuses as $row) {
                    // Wildcard: Incharge of all campuses
                    if ($row['campus_id'] == 0) return true;

                    // Match specific campus
                    if (!empty($event['campus_id']) && $row['campus_id'] == $event['campus_id']) return true;

                    // Campus Incharge implies School Incharge
                    if (!empty($event['school_id'])) {
                        $schoolModel = new \App\Models\School();
                        $school = $schoolModel->getById($event['school_id']);
                        if ($school && $school['campus_id'] == $row['campus_id']) {
                            return true;
                        }
                    }
                }
               
                // Check School Incharge
                $schoolInchargeModel = new InchargeSchool();
                $managedSchools = $schoolInchargeModel->getManagedSchools($userId);

                foreach ($managedSchools as $row) {
                    // Wildcard: Incharge of all schools
                    if ($row['school_id'] == 0) return true;

                    // Match specific school
                    if (!empty($event['school_id']) && $row['school_id'] == $event['school_id']) return true;
                }
            }
        }

        // 4. Check co-manager table for explicit permission
        // NOTE: Keeping e_user_id here since your EventComanager table uses it
        if (empty($_SESSION['e_user_id'])) {
            return false;
        }

        $erpId = UserIdentity::normalize($_SESSION['e_user_id'] ?? '');
        $ec = new EventComanager();
        $record = $ec->findOne(['event_id' => $eventId, 'erp_id' => $erpId]);

        if (!$record) {
            return false;
        }

        $bitmask = (int)($record['perms_bitmask'] ?? 0);
        return EventComanager::hasPermission($bitmask, $requiredPermission);
    }

    /**
     * Check if the event can be edited (returns false if published and allow_editing_published is 0, except for admins)
     *
     * @param int $eventId
     * @return bool
     */
    public static function isEventEditable($eventId)
    {
        // Admins can always edit
        if (self::isAdmin()) {
            return true;
        }

        // Fetch the event to check its status
        $eventsModel = new \App\Models\Event();
        $event = $eventsModel->getById($eventId);

        if ($event && $event['status'] === 'published') {
            // Check system setting
            $settingsModel = new \App\Models\SystemSetting();
            $settings = $settingsModel->getAllSettingsFormated();

            // If the setting is '0' or false/unchecked, editing is disabled for managers
            if (isset($settings['allow_editing_published']) && $settings['allow_editing_published'] == '0') {
                return false;
            }
        }

        return true;
    }

    /**
     * Strict Check: Is the user an Internal ERP Student/Staff?
     */
    public static function isInternalUser()
    {
        // Type 3 = Internal Student/Staff
        return isset($_SESSION['e_user_type']) && $_SESSION['e_user_type'] == 3;
    }

    /**
     * Check if the current user can approve the given event.
     * Logic:
     * 1. Admins can approve anything.
     * 2. Campus Incharges can approve events in their campus.
     * 3. School Incharges can approve events in their school.
     * 4. '0' in incharge table means "All" (Global Incharge).
     *
     * @param array $event The event row from DB
     * @return bool
     */
    public static function canApproveEvent($event)
    {
        // 1. Admins can approve anything
        if (self::isAdmin()) {
            return true;
        }

        // Managers/Incharges must have a user ID
        if (empty($_SESSION['e_user_id'])) {
            return false;
        }

        $userId = $_SESSION['e_user_uid']; //$_SESSION['e_user_id'];

        // Normalize ID if needed (though Incharge tables usually use the ERP ID directly)
        // Assuming Incharge tables use the same ID format as stored in session for ERP users.
        // If session is 'ERP_500' and DB has '500', we might need normalization.
        // Based on previous code, let's try to be safe.
        // However, Incharge models query by `erp_user_id`.

        // 2. Check Campus Incharge
        $campusInchargeModel = new InchargeCampus();
        $managedCampuses = $campusInchargeModel->getManagedCampuses($userId);

        foreach ($managedCampuses as $row) {
            // Rule 1: Wildcard - Incharge of ALL campuses
            if ($row['campus_id'] == 0) {
                return true;
            }
            // Match specific campus
            if (!empty($event['campus_id']) && $row['campus_id'] == $event['campus_id']) {
                return true;
            }
            // Rule 3: Campus Incharge is also Incharge of all schools in that campus
            // Check if the event's school belongs to this managed campus
            if (!empty($event['school_id'])) {
                $schoolModel = new \App\Models\School();
                $school = $schoolModel->getById($event['school_id']);
                if ($school && $school['campus_id'] == $row['campus_id']) {
                    return true;
                }
            }
        }

        // 3. Check School Incharge
        $schoolInchargeModel = new InchargeSchool();
        $managedSchools = $schoolInchargeModel->getManagedSchools($userId);

        foreach ($managedSchools as $row) {
            // Rule 2: Wildcard - Incharge of ALL schools
            if ($row['school_id'] == 0) {
                return true;
            }
            // Match specific school
            if (!empty($event['school_id']) && $row['school_id'] == $event['school_id']) {
                return true;
            }
        }

        return false;
    }
}
