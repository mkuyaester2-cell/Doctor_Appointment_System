<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';

Auth::requireRole('admin');

define('PAGE_TITLE', 'Admin Dashboard');
require_once __DIR__ . '/../includes/header.php';

$db = Database::getInstance();

// Stats
$stats = [
    'total_doctors' => $db->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
    'pending_doctors' => $db->query("SELECT COUNT(*) FROM doctors WHERE status = 'pending'")->fetchColumn(),
    'total_patients' => $db->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
    'total_appointments' => $db->query("SELECT COUNT(*) FROM appointments")->fetchColumn()
];

// Recent Pending Doctors
$stmt = $db->query("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.status = 'pending' ORDER BY d.created_at DESC LIMIT 5");
$pending_doctors = $stmt->fetchAll();

// Recent Appointments
$stmt = $db->query("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN doctors d ON a.doctor_id = d.id 
    ORDER BY a.created_at DESC LIMIT 5
");
$recent_appointments = $stmt->fetchAll();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">System Overview</h1>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <a href="manage-doctors.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-primary-200 transition-colors block">
                <p class="text-slate-500 text-sm font-medium">Total Doctors</p>
                <p class="text-2xl font-bold text-slate-900"><?php echo $stats['total_doctors']; ?></p>
            </a>
            <a href="manage-doctors.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-amber-200 transition-colors block">
                <p class="text-amber-600 text-sm font-medium">Pending Approvals</p>
                <p class="text-2xl font-bold text-amber-600"><?php echo $stats['pending_doctors']; ?></p>
            </a>
            <a href="view-patients.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-primary-200 transition-colors block">
                <p class="text-slate-500 text-sm font-medium">Total Patients</p>
                <p class="text-2xl font-bold text-slate-900"><?php echo $stats['total_patients']; ?></p>
            </a>
            <a href="view-appointments.php" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:border-primary-200 transition-colors block">
                <p class="text-slate-500 text-sm font-medium">Total Appointments</p>
                <p class="text-2xl font-bold text-slate-900"><?php echo $stats['total_appointments']; ?></p>
            </a>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Pending Doctors -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900">Pending Doctor Registrations</h2>
                    <a href="manage-doctors.php" class="text-primary-600 text-sm font-medium">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($pending_doctors as $doc): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900"><?php echo htmlspecialchars($doc['full_name']); ?></div>
                                        <div class="text-xs text-slate-500"><?php echo htmlspecialchars($doc['specialization']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="manage-doctors.php?id=<?php echo $doc['id']; ?>" class="inline-flex items-center px-3 py-1 bg-primary-50 text-primary-700 rounded-lg text-xs font-bold hover:bg-primary-100 transition-colors">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pending_doctors)): ?>
                                <tr><td colspan="2" class="px-6 py-10 text-center text-slate-400">No pending requests</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900">Recent Appointments</h2>
                    <a href="view-appointments.php" class="text-primary-600 text-sm font-medium">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recent_appointments as $apt): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900"><?php echo htmlspecialchars($apt['patient_name']); ?></div>
                                        <div class="text-xs text-slate-500">with <?php echo htmlspecialchars($apt['doctor_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500">
                                        <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_appointments)): ?>
                                <tr><td colspan="2" class="px-6 py-10 text-center text-slate-400">No recent appointments</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
