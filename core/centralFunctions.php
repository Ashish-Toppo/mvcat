<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// functions for 'data fetched from EPR' defined here

$restricted_db_host = 'localhost';
$restricted_db_name = 'adbu_erp_events';
$restricted_db_user = 'root';
$restricted_db_password = '';

function restrictedDb($db_name = null)
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



function getCampus(array $filter = [])
{
    $table_name = 'core_campuses';
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

    // Name filter
    if (isset($filter['name'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE name = ?");
        $stmt->execute([$filter['name']]);
        return $stmt->fetch();
    }

    // Future: additional filters (e.g. by city, campus_type, etc.)
    throw new InvalidArgumentException('Invalid filter for getCampus()');
}


function getSchool(array $filter = [])
{
    $table_name = 'core_schools';
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

function getDepartment(array $filter = [])
{
    $table_name = 'core_departments';
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

function getUser(array $filter = [])
{
    $table_name = 'users';
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

    // Email filter
    if (isset($filter['email'])) {
        $stmt = $db->prepare("SELECT * FROM $table_name WHERE email = ?");
        $stmt->execute([$filter['email']]);
        return $stmt->fetch();
    }

    throw new InvalidArgumentException('Invalid filter for getUser()');
}

function getUserByName($name, $exact = false)
{

    $table_name = 'users';
    $db = restrictedDb();

    try {
        if ($exact) {
            $stmt = $db->prepare("SELECT * FROM users WHERE full_name = ? AND is_active = 1");
            $stmt->execute([$name]);
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE full_name LIKE ? AND is_active = 1");
            $stmt->execute(["%" . $name . "%"]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Database fetch error: " . $e->getMessage());
        return [];
    }
}

function sendMail($to, $subject, $htmlBody, $altBody = '', $attachments = [])
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = (string) $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = (string) $_ENV['SMTP_USER'];
        $mail->Password   = (string) $_ENV['SMTP_PASS']; // real password here
        $mail->Port       = (int) $_ENV['SMTP_PORT'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // recommended

        // Optional debugging
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = function ($str, $level) { error_log("SMTP: $str"); };

        $mail->setFrom('ashishtoppo8958@gmail.com', 'ADBU Event System');
        $mail->addReplyTo('noreply@adbu.edu.in', 'Do Not Reply');

        if (is_array($to)) {
            foreach ($to as $email) $mail->addAddress($email);
        } else {
            $mail->addAddress($to);
        }

        foreach ($attachments as $filePath) {
            if (file_exists($filePath)) $mail->addAttachment($filePath);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);

        $result = $mail->send();

        if (!$result) {
            error_log("Mail send() failed: " . $mail->ErrorInfo);
            return false;
        }

        return true;

    } catch (Exception $e) {
        error_log("Mail exception: " . $e->getMessage());
        return false;
    }
}




function send_sms() {}
