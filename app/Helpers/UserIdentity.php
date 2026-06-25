<?php

namespace App\Helpers;

class UserIdentity
{
    const PREFIX_ERP = 'ERP_';
    const PREFIX_LOCAL = 'LCL_';

    /**
     * The Main Fix: Standardize any ID (int or string) to the correct format.
     * Usage: $cleanId = UserIdentity::normalize($rawId);
     */
    public static function normalize($id)
    {
        if (empty($id)) return null;

        // If it's already a clean string, leave it alone
        if (str_starts_with((string)$id, self::PREFIX_ERP)) return $id;
        if (str_starts_with((string)$id, self::PREFIX_LOCAL)) return $id;

        // If it's numeric (e.g., 5002), assume it's an ERP user
        if (is_numeric($id)) {
            return self::PREFIX_ERP . $id;
        }

        // Fallback (or return null if invalid)
        return $id;
    }

    /**
     * Check if the current user is an ERP (SSO) User
     */
    public static function isErp($id = null)
    {
        // 1. Handle the external user session logic first
        if (isset($_SESSION['e_is_external_user']) && $_SESSION['e_is_external_user'] != 0) {
            return false;
        }

        // 2. Resolve the ID (passed param -> session -> empty string)
        $id = $id ?? $_SESSION['e_user_id'] ?? '';

        // 3. Check the prefix
        return str_starts_with((string)$id, self::PREFIX_ERP);
    }

    /**
     * Check if the current user is a Local User
     */
    public static function isLocal($id = null)
    {
        $id = $id ?? $_SESSION['e_user_id'] ?? '';
        return str_starts_with((string)$id, self::PREFIX_LOCAL);
    }

    /**
     * SAFE EXTRACTION: If you absolutely need the integer for a legacy check.
     * Example: extracting '5002' from 'ERP_5002'
     */
    public static function extractId($id = null)
    {
        $id = $id ?? $_SESSION['e_user_id'] ?? '';

        // Remove prefixes
        $raw = str_replace([self::PREFIX_ERP, self::PREFIX_LOCAL], '', (string)$id);

        // Return numeric if possible, otherwise return the raw string
        return is_numeric($raw) ? (int)$raw : $raw;
    }

    /**
     * REPLACEMENT FOR: if ((int)$id > 0)
     */
    public static function isValid($id)
    {
        return !empty($id) && $id !== 'n/a';
    }
}
