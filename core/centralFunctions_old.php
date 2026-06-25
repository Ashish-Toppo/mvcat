<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception; 


// functions for 'data fetched from EPR' defined here

$restricted_db_host = 'localhost';
$restricted_db_name = 'adbu_erp';
$restricted_db_user = 'root';
$restricted_db_password = '';
$restricted_db_login_user = 'adbu_erp_login';
$restricted_db_login_password = '';

//function verifyErpCredentials($identifier, $password, $type = 'usernameOrEmail', $token = null, $tokenName = null)
function verifyErpCredentials($identifier, $password, $type, $token = null, $tokenName = 'e_csrf_token')
{
    // CSRF Check
    if ($token !== ($_SESSION[$tokenName] ?? null)) {
        return [
            'success' => false,
            'data'    => null,
            'error'   => 'Invalid CSRF token'
        ];
    }



    $db = restrictedDb_for_login();

    /*
        Login Rules:
        ----------------------------------
        User Type = 3  => Student
        Other Types    => Employee / Admin / Staff

        Identifier can be:
        - username
        - email
    */

    $sql = "
        SELECT 
            u.id,
            u.username,
            u.password,
            u.user_type,
            u.status,
            u.last_login,

            -- Student Details
            s.id AS student_id,
            s.name AS student_name,
            s.phone AS student_phone,
            s.email AS student_email,
            s.registration_no AS student_code,

            -- Employee Details
            e.id AS employee_id,
            e.name AS employee_name,
            e.employee_code,

            ed.mobile AS employee_phone,
            ed.email AS employee_email

        FROM sol_erp_2014_user_master u
        LEFT JOIN sol_erp_2014_student_master s     ON s.user_id = u.id AND u.user_type = 3
        LEFT JOIN sol_erp_2014_employee_master e    ON e.user_id = u.id AND u.user_type = 2
        LEFT JOIN sol_erp_2014_employee_detail ed   ON ed.employee_id = e.id
        WHERE 
            (   u.username = :identifier
                OR s.email = :identifier
                OR ed.email = :identifier
            )
            AND u.status = '1'
            AND u.user_type in ('2','3')
        ORDER BY u.id DESC
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':identifier' => $identifier
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return [
            'success' => false,
            'data'    => null,
            'error'   => 'User not found'
        ];
    }



    $validPassword = (md5($password) == $user['password']); //$validPassword = ($password == $user['password']); // CHANGE if encrypted

    if (!$validPassword) {
        return [
            'success' => false,
            'data'    => null,
            'error'   => 'Invalid password'
        ];
    }

    // Determine Name / Email / Phone
    if ($user['user_type'] == 3) {
        // Student
        $name  = $user['student_name'];
        $email = $user['student_email'];
        $phone = $user['student_phone'];
        $code  = $user['student_code'];
        $role  = 'student';
        $is_manager =  false;
        $is_admin   = false;
    } else {
        // Employee
        $name  = $user['employee_name'];
        $email = $user['employee_email'];
        $phone = $user['employee_phone'];
        $code  = $user['employee_code'];
        $role  = 'employee';
        $is_manager =  true;
        $is_admin   = false;
    }

    return [
        'success' => true,
        'data' => [
            'id'         => $user['id'],
            'username'   => $user['username'],
            'user_type'  => $user['user_type'],
            'role'       => $role,
            'code'       => $code,
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'is_manager' => $is_manager, 
            'is_admin'   => $is_admin
        ]
    ];
}

function restrictedDb($db_name = null) //done:vpd:20-02-2026
{
    static $pdo = null;
    global $restricted_db_host, $restricted_db_name, $restricted_db_user, $restricted_db_password;

    $db_name = $db_name ?? $restricted_db_name;

    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=$restricted_db_host;dbname=$db_name;charset=utf8mb4",
            $restricted_db_user,
            $restricted_db_password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}
function restrictedDb_for_login($db_name = null) //done:vpd:20-02-2026
{
    static $pdo = null;
    global $restricted_db_host, $restricted_db_name, $restricted_db_login_user, $restricted_db_login_password;

    $db_name = $db_name ?? $restricted_db_name;

    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=$restricted_db_host;dbname=$db_name;charset=utf8mb4",
            $restricted_db_login_user,
            $restricted_db_login_password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo;
}



function getCampus(array $filter = [])//done:vpd:20-02-2026
{
    $table_name = 'campus_master';
    $db = restrictedDb();

    // No filter → all records
    if (empty($filter)) {
        $stmt = $db->query("SELECT *, campus_name as name FROM $table_name");
        return $stmt->fetchAll();
    }

    // ID filter
    if (isset($filter['id'])) {
        $stmt = $db->prepare("SELECT *, campus_name as name FROM $table_name WHERE id = ?");
        $stmt->execute([$filter['id']]);
        return $stmt->fetch();
    }

    // Name filter
    if (isset($filter['name'])) {
        $stmt = $db->prepare("SELECT *, campus_name as name FROM $table_name WHERE campus_name = ?");
        $stmt->execute([$filter['name']]);
        return $stmt->fetch();
    }

    // Future: additional filters (e.g. by city, campus_type, etc.)
    throw new InvalidArgumentException('Invalid filter for getCampus()');
}


function getSchool(array $filter = [])//done:vpd:20-02-2026
{
    $table_name = 'sol_erp_2014_school_master';
    $db = restrictedDb();

    // No filter → all records
    if (empty($filter)) {
        $stmt = $db->query("SELECT * FROM $table_name");
        return $stmt->fetchAll();
    }

    // ID filter
    if (isset($filter['id'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE id = ?");
        $stmt->execute([$filter['id']]);
        return $stmt->fetch();
    }

    // Campus ID filter
    if (isset($filter['campus_id'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE campus_id = ?");
        $stmt->execute([$filter['campus_id']]);
        return $stmt->fetchAll();
    }

    // Name filter
    if (isset($filter['name'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE name = ?");
        $stmt->execute([$filter['name']]);
        return $stmt->fetch();
    }

    // Future: other filters
    throw new InvalidArgumentException('Invalid filter for getSchool()');
}

function getDepartment(array $filter = [])//done:vpd:20-02-2026
{
    $table_name = 'sol_erp_2014_department_master';
    $db = restrictedDb();

    // No filter → all departments
    if (empty($filter)) {
        $stmt = $db->query("SELECT * FROM $table_name");
        return $stmt->fetchAll();
    }

    // ID filter
    if (isset($filter['id'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE id = ?");
        $stmt->execute([$filter['id']]);
        return $stmt->fetch();
    }

    // School ID filter
    if (isset($filter['school_id'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE school_id = ?");
        $stmt->execute([$filter['school_id']]);
        return $stmt->fetchAll();
    }

    // Name filter
    if (isset($filter['name'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE name = ?");
        $stmt->execute([$filter['name']]);
        return $stmt->fetch();
    }

    throw new InvalidArgumentException('Invalid filter for getDepartment()');
}

function getUser(array $filter = []) //partiall done
{
    $table_name = 'sol_erp_2014_user_master';
    $table_employee_master = 'sol_erp_2014_employee_master';
    $table_employee_details = 'sol_erp_2014_employee_detail';
    
    $db = restrictedDb();



    // No filter → all records
    if (empty($filter)) {
        $stmt = $db->query("
                            SELECT um.id, um.username as user_id, 
                            em.name as full_name,
                            ed.email as email  
                            FROM $table_name um
                            LEFT JOIN $table_employee_master em on em.employee_code = um.username
                            LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                            where  um.user_type=2 limit 10");
        return $stmt->fetchAll();
    }

    // ID filter
    if (isset($filter['id'])) {
        $stmt = $db->prepare("
                            SELECT um.id, um.username as user_id, 
                            em.name as full_name,
                            ed.email as email  
                            FROM $table_name um
                            LEFT JOIN $table_employee_master em on em.employee_code = um.username
                            LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                            where  um.user_type=2 and um.id = ? limit 10
                            ");
                        $stmt->execute([$filter['id']]);
        return $stmt->fetch();
    }
    if (isset($filter['user_code'])) {
        $stmt = $db->prepare("
                            SELECT um.id, um.username as user_id, 
                            em.name as full_name,
                            ed.email as email  
                            FROM $table_name um
                            LEFT JOIN $table_employee_master em on em.employee_code = um.username
                            LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                            where  um.user_type=2 and um.username = ? limit 10
                            ");
                        $stmt->execute([$filter['user_code']]);
        return $stmt->fetch();
    }

    // Email filter
    if (isset($filter['email'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE email = ?");
        $stmt->execute([$filter['email']]);
        return $stmt->fetch();
    }

    throw new InvalidArgumentException('Invalid filter for getUser()');
}

function getUserByName($name, $exact = false) //partiall done
{
	
    $table_name = 'sol_erp_2014_user_master';
    $table_employee_master = 'sol_erp_2014_employee_master';
    $table_employee_details = 'sol_erp_2014_employee_detail';

    $db = restrictedDb();

    try {
        if ($exact) {
            $stmt = $db->prepare("
                                SELECT um.id, um.username as user_id, 
                                em.name as full_name,
                                ed.email as email  
                                FROM $table_name um
                                LEFT JOIN $table_employee_master em on em.employee_code = um.username
                                LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                                where  um.user_type=2 and (um.username  = ? OR em.name  = ? OR ed.email  = ?) AND em.status  = '1'
                                limit 10");
                            
                            // SELECT id, username as user_id, username as full_name, username as email 
                            //     FROM sol_erp_2014_user_master
                            //     WHERE user_type='2' and username  = ? AND status  = '1'
                            //     ");
            $stmt->execute([$name,$name,$name]);
        } else {
            $stmt = $db->prepare("
                                SELECT um.id, um.username as user_id, 
                                em.name as full_name,
                                ed.email as email  
                                FROM $table_name um
                                LEFT JOIN $table_employee_master em on em.employee_code = um.username
                                LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                                where  um.user_type=2 and (um.username  LIKE ? OR em.name  LIKE ? OR ed.email  LIKE ?) AND em.status  = '1'
                                limit 10");

                                // SELECT id, username as user_id, username as full_name, username as email 
                                // FROM sol_erp_2014_user_master 
                                // WHERE user_type='2' and username  LIKE ? AND status  = '1'
                                // ");
            $stmt->execute(['%'.$name.'%', '%'.$name.'%', '%'.$name.'%']);
			
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Database fetch error: " . $e->getMessage());
        return [];
    }
}

function searchUsers($search_name_empID_email, $exact = false)
{   

    $table_name = 'sol_erp_2014_user_master';
    $table_employee_master = 'sol_erp_2014_employee_master';
    $table_employee_details = 'sol_erp_2014_employee_detail';

    $db = restrictedDb();

    try {
        if ($exact) {
            $stmt = $db->prepare("
                                SELECT um.id, um.username as user_id, 
                                em.name as full_name,
                                ed.email as email  
                                FROM $table_name um
                                LEFT JOIN $table_employee_master em on em.employee_code = um.username
                                LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                                where  um.user_type=2 and (um.username  = ? OR em.name  = ? OR ed.email  = ?) AND em.status  = '1'
                                limit 10");
                            
                            // SELECT id, username as user_id, username as full_name, username as email 
                            //     FROM sol_erp_2014_user_master
                            //     WHERE user_type='2' and username  = ? AND status  = '1'
                            //     ");
            $stmt->execute([$search_name_empID_email,$search_name_empID_email,$search_name_empID_email]);
        } else {
            $stmt = $db->prepare("
                                SELECT um.id, um.username as user_id, 
                                em.name as full_name,
                                ed.email as email  
                                FROM $table_name um
                                LEFT JOIN $table_employee_master em on em.employee_code = um.username
                                LEFT JOIN $table_employee_details ed on ed.employee_id = em.id
                                where  um.user_type=2 and (um.username  LIKE ? OR em.name  LIKE ? OR ed.email  LIKE ?) AND em.status  = '1'
                                limit 10");

                                // SELECT id, username as user_id, username as full_name, username as email 
                                // FROM sol_erp_2014_user_master 
                                // WHERE user_type='2' and username  LIKE ? AND status  = '1'
                                // ");
            $stmt->execute(['%'.$search_name_empID_email.'%', '%'.$search_name_empID_email.'%', '%'.$search_name_empID_email.'%']);
			
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Database fetch error: " . $e->getMessage());
        return [];
    }
}
// require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
// require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
// require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

function sendMail($to, $subject, $htmlBody, $altBody = '', $attachments = [])
{  

   
 
	return true;
}

function send_sms() {
    
}
