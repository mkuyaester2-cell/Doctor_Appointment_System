<?php
// doctor/dashboard.php
define('PAGE_TITLE', 'Doctor Dashboard');
require_once __DIR__ . '/../includes/header.php';

// Ensure user is logged in as doctor
Auth::requireRole('doctor');

$user_id = Auth::id();
$db = Database::getInstance();

// Fetch Doctor ID
$stmt = $db->prepare("SELECT id, full_name, specialization, status FROM doctors WHERE user_id = ?");
$stmt->execute([$user_id]);
$doctor = $stmt->fetch();
$doctor_id = $doctor['id'];
$doctor_status = $doctor['status'];

// Check if availability is set
$stmt = $db->prepare("SELECT COUNT(*) FROM doctor_availability WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$has_schedule = $stmt->fetchColumn() > 0;

// Setup default schedule if missing (Safety net for existing doctors)
if (!$has_schedule) {
    $stmt = $db->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, '09:00:00', '17:00:00', 30)");
    for ($day = 1; $day <= 5; $day++) {
        $stmt->execute([$doctor_id, $day]);
    }
    $has_schedule = true;
}

// Stats
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_appointments,
        SUM(CASE WHEN appointment_date = CURDATE() AND status != 'cancelled' THEN 1 ELSE 0 END) as today_patients,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests
    FROM appointments 
    WHERE doctor_id = ?
");
$stmt->execute([$doctor_id]);
$stats = $stmt->fetch();

// Recent Appointments
$stmt = $db->prepare("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 10
");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Welcome, <?php echo htmlspecialchars($doctor['full_name']); ?>!</h1>
                <p class="text-slate-500"><?php echo htmlspecialchars($doctor['specialization']); ?> • Manage your patients and schedules.</p>
            </div>
            <div class="flex gap-3">
                <a href="appointments.php" class="inline-flex items-center justify-center px-6 py-3 border border-slate-200 rounded-xl shadow-sm text-base font-medium text-slate-700 bg-white hover:bg-slate-50 transition-all">
                    View All Appointments
                </a>
            </div>
        </div>

        <?php if ($doctor_status === 'pending'): ?>
            <div class="mb-10 bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-2xl shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-xl">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-amber-900">Account Pending Approval</h3>
                        <p class="text-amber-700">Your profile is currently being reviewed by our administrators. You will be visible to patients once approved.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Today's Patients</p>
                    <p class="text-2xl font-bold text-slate-900"><?php echo $stats['today_patients'] ?? 0; ?></p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Pending Requests</p>
                    <p class="text-2xl font-bold text-slate-900"><?php echo $stats['pending_requests'] ?? 0; ?></p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-check-to-slot"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Total Appointments</p>
                    <p class="text-2xl font-bold text-slate-900"><?php echo $stats['total_appointments'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-900">Recent Appointments</h2>
            <a href="appointments.php" class="text-primary-600 font-medium hover:text-primary-700 text-sm">See all history &rarr;</a>
        </div>
        
        <?php if (empty($appointments)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-3xl">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-900 mb-2">No appointments scheduled</h3>
                <p class="text-slate-500 mb-6 max-w-sm mx-auto">New appointment requests will appear here once patients book with you.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Patient</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($appointments as $apt): 
                                $statusColors = [
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $statusClass = $statusColors[$apt['status']] ?? 'bg-slate-100 text-slate-800';
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($apt['patient_name']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($apt['patient_phone']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-900"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($apt['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <?php if ($apt['status'] === 'pending'): ?>
                                            <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'confirmed')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Confirm">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="view-appointment.php?id=<?php echo $apt['id']; ?>" class="p-2 text-slate-400 hover:text-primary-600 rounded-lg transition-colors" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($apt['status'] !== 'completed' && $apt['status'] !== 'cancelled'): ?>
                                            <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'cancelled')" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition-colors" title="Cancel">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateStatus(id, status) {
    if(confirm(`Are you sure you want to set this appointment to ${status}?`)) {
        // Typically would use AJAX, for now redirecting to a handler
        window.location.href = `update-appointment-status.php?id=${id}&status=${status}`;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
