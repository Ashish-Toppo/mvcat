<?php

namespace App\Models;

use Core\Classes\Model;
use PDOException;
use PDO;

use App\Helpers\UserIdentity;

class User extends Model
{
    public static string $table = 'users';

    public function findByEmail($email, $external_user)
    {
        if ($external_user) return $this->first("SELECT * FROM " . static::$table . " WHERE email = ?", [$email]);
        return getUser(['email' => $email]);
    }

    public function getById($id, $external_user)
    {
        if ($external_user) return $this->first("SELECT * FROM " . static::$table . " WHERE id = ?", [$id]);
        return getUser(['id' => $id]);
    }

    public function getByIds(array $ids, bool $external_user)
    {
        // Validate input
        if (empty($ids)) return [];

        // 1. CRITICAL FIX: Remove 'intval'. Keep IDs as unique strings.
        $ids = array_values(array_unique($ids));

        $users = [];

        if ($external_user) {
            // --- LOCAL DATABASE LOGIC ---

            // A. Extract raw numeric IDs for the SQL query (e.g., 'LCL_123' -> 123)
            $rawIds = [];
            foreach ($ids as $id) {
                $extracted = UserIdentity::extractId($id);
                if ($extracted) $rawIds[] = $extracted;
            }

            if (empty($rawIds)) return [];

            // B. Run Query using Raw IDs
            $placeholders = implode(',', array_fill(0, count($rawIds), '?'));
            $sql = "SELECT * FROM " . static::$table . " WHERE id IN ($placeholders)";
            $rows = $this->all($sql, $rawIds);

            // C. Re-normalize IDs in the result 
            // (So the rest of the app sees 'LCL_123', not just '123')
            foreach ($rows as $row) {
                $row['id'] = UserIdentity::PREFIX_LOCAL . $row['id'];
                $users[] = $row;
            }
        } else {
            // --- ERP / HELPER LOGIC ---

            foreach ($ids as $id) {
                try {
                    // A. Extract raw ID for the helper (e.g., 'ERP_500' -> '500')
                    $rawId = UserIdentity::extractId($id);

                    // B. Call Helper
                    $user = getUser(['id' => $rawId]);

                    if ($user) {
                        // C. Normalize ID in result
                        $user['id'] = UserIdentity::PREFIX_ERP . $rawId;
                        $users[] = $user;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        // 2. CRITICAL FIX: Index by String, do not cast to (int)
        $indexed = [];
        foreach ($users as $u) {
            if (!isset($u['id'])) continue;
            // Keep the key as a string (e.g., 'LCL_123')
            $indexed[(string)$u['id']] = $u;
        }

        return $indexed;
    }


    public function update($userId, array $userData) : bool
    {
        try {
            $sql = "UPDATE " . static::$table . " SET `full_name`=?, `email`=?, `phone`=?, `bio`=? WHERE `id` = ? AND is_active = 1";

            $queryPlaceholders = [
                $userData['full_name'],
                $userData['email'],
                $userData['phone'],
                $userData['bio'],
                $userId
            ];

            $stmt = $this->query($sql, $queryPlaceholders);

            if ($stmt->rowCount() > 0) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            // Log the error, show friendly message, or rethrow depending on your app
            error_log("Database update error: " . $e->getMessage());
            return false;
        }
    }

    public function new_user($user_id, $full_name, $email, $password, $phone, $organization)
    {
        $timestamp = date('YmdHis');
        $randomDigits = random_int(1000, 9999);

        // Generate a random uppercase letter (A-Z)
        // ascii 65 = 'A', 90 = 'Z'
        $randomChar = chr(random_int(65, 90));

        // Final ID: LCL_202601241615005892X
        $newId = 'LCL_' . $timestamp . $randomDigits . $randomChar;

        // Store the result of the insert instead of returning it directly
        $insertResult = self::insert([
            'id'           => $newId,
            'user_id'      => 'EX-' . (string) $user_id,
            'full_name'    => (string) $full_name,
            'email'        => (string) $email,
            'password'     => password_hash($password, PASSWORD_DEFAULT),
            'phone'        => (string) $phone,
            'organization' => (string) $organization,
        ]);

        // The base model will return 0 on success for string IDs.
        // As long as it didn't return exactly 'false', the row was inserted.
        if ($insertResult !== false) {
            return $newId; // Return your generated string ID
        }

        return false; // Or throw an Exception, depending on how you handle errors
    }

    public function updatePassword($userId, $hashedPassword)
    {
        try {
            $sql = "UPDATE " . static::$table . " SET `password` = ? WHERE `id` = ? AND is_active = 1";
            $stmt = $this->query($sql, [$hashedPassword, $userId]);

            if ($stmt->rowCount() > 0) {
                return true; // success
            }
            return false; // no change (maybe same password as before)
        } catch (PDOException $e) {
            error_log("Database update error: " . $e->getMessage());
            return false; // DB error
        }
    }

    public function findByName($name, $exact = false, $external_user = false)
    {
        if ($external_user) {
            try {
                if ($exact) {
                    $sql = "SELECT * FROM " . static::$table . " WHERE full_name = ? AND is_active = 1";
                    $stmt = $this->query($sql, [$name]);
                } else {
                    $sql = "SELECT * FROM " . static::$table . " WHERE full_name LIKE ? AND is_active = 1";
                    $stmt = $this->query($sql, ["%" . $name . "%"]);
                }

                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (PDOException $e) {
                error_log("Database fetch error: " . $e->getMessage());
                return [];
            }
        }


        return getUserByName($name, false);
    }

    // ─────────────────────────────────────────────────────────
    // Admin-only methods (local users table)
    // ─────────────────────────────────────────────────────────

    /**
     * Paginated list of local users for the admin dashboard.
     * Supports filtering by name/email search and is_active status.
     */
    public function getAllUsersForAdmin(array $filters, int $limit, int $offset): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(full_name LIKE ? OR email LIKE ?)";
            $params[]     = '%' . $filters['search'] . '%';
            $params[]     = '%' . $filters['search'] . '%';
        }

        if ($filters['status'] !== '' && $filters['status'] !== null) {
            $conditions[] = "is_active = ?";
            $params[]     = (int)$filters['status'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT id, user_id, full_name, email, phone, organization, is_active, created_at
                  FROM " . static::$table . "
                  $where
                  ORDER BY created_at DESC
                  LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("getAllUsersForAdmin error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count of local users matching the given filters (for pagination).
     */
    public function countUsersForAdmin(array $filters): int
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(full_name LIKE ? OR email LIKE ?)";
            $params[]     = '%' . $filters['search'] . '%';
            $params[]     = '%' . $filters['search'] . '%';
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $conditions[] = "is_active = ?";
            $params[]     = (int)$filters['status'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql   = "SELECT COUNT(*) FROM " . static::$table . " $where";

        try {
            $stmt = $this->query($sql, $params);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("countUsersForAdmin error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Set a local user's active status (0 = inactive, 1 = active).
     */
    public function setActive(string $id, int $status): bool
    {
        try {
            $sql  = "UPDATE " . static::$table . " SET is_active = ? WHERE id = ?";
            $stmt = $this->query($sql, [$status, $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("setActive error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin-initiated password reset for a local user.
     */
    public function adminResetPassword(string $id, string $hashedPassword): bool
    {
        try {
            $sql  = "UPDATE " . static::$table . " SET password = ? WHERE id = ?";
            $stmt = $this->query($sql, [$hashedPassword, $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log("adminResetPassword error: " . $e->getMessage());
            return false;
        }
    }
}
