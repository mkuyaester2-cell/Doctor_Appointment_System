<?php
// doctor/update-appointment-status.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Auth.php';

Auth::requireRole('doctor');

$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if ($id && $status) {
    $db = Database::getInstance();
    
    // Security check: ensure this doctor owns the appointment
    $user_id = Auth::id();
    $stmt = $db->prepare("SELECT d.id FROM doctors d WHERE d.user_id = ?");
    $stmt->execute([$user_id]);
    $doctor = $stmt->fetch();
    $doctor_id = $doctor['id'];

    $stmt = $db->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$id, $doctor_id]);
    if ($stmt->fetch()) {
        $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        Session::setFlash('success', "Appointment status updated to $status.");
    } else {
        Session::setFlash('error', "You are not authorized to update this appointment.");
    }
}

$referrer = $_SERVER['HTTP_REFERER'] ?? (APP_URL . '/doctor/dashboard.php');
header("Location: $referrer");
exit;
