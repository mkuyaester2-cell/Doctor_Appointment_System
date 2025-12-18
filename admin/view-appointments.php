<?php
// admin/view-appointments.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';

Auth::requireRole('admin');

$db = Database::getInstance();

define('PAGE_TITLE', 'All Appointments');
require_once __DIR__ . '/../includes/header.php';

// Fetch all appointments
$stmt = $db->query("
    SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name, d.specialization
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN doctors d ON a.doctor_id = d.id 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments = $stmt->fetchAll();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">All Appointments</h1>
            <a href="dashboard.php" class="text-slate-500 hover:text-primary-600 font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Patient</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Doctor</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Schedule</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
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
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($apt['patient_name']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($apt['doctor_name']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($apt['specialization']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-900"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $statusColors[$apt['status']]; ?>">
                                        <?php echo $apt['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($appointments)): ?>
                            <tr><td colspan="4" class="px-6 py-20 text-center text-slate-400">No appointments recorded yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
