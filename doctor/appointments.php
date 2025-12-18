<?php
// doctor/appointments.php
define('PAGE_TITLE', 'My Appointments');
require_once __DIR__ . '/../includes/header.php';

Auth::requireRole('doctor');

$user_id = Auth::id();
$db = Database::getInstance();

// Fetch Doctor ID
$stmt = $db->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$user_id]);
$doctor_id = $stmt->fetch()['id'];

// Fetch all appointments
$stmt = $db->prepare("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone, p.gender, p.dob
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 border-b border-slate-200 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">All Appointments</h1>
                <p class="text-slate-500">View and manage your entire patient history.</p>
            </div>
            
            <div class="flex gap-2">
                <!-- Filters could go here -->
            </div>
        </div>

        <?php if (empty($appointments)): ?>
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-100">
                <p class="text-slate-500">No appointments found.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-4">
                <?php foreach ($appointments as $apt): 
                    $statusColors = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'confirmed' => 'bg-green-100 text-green-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $statusClass = $statusColors[$apt['status']] ?? 'bg-slate-100 text-slate-800';
                ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary-50 rounded-full flex items-center justify-center text-primary-600 font-bold">
                            <?php echo substr($apt['patient_name'], 0, 1); ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900"><?php echo htmlspecialchars($apt['patient_name']); ?></h3>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-slate-500">
                                <span class="flex items-center gap-1">
                                    <i class="fa-regular fa-calendar"></i> <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i> <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fa-solid fa-phone text-xs"></i> <?php echo htmlspecialchars($apt['patient_phone']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 self-end md:self-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php echo $statusClass; ?>">
                            <?php echo ucfirst($apt['status']); ?>
                        </span>
                        
                        <div class="flex gap-1">
                            <?php if ($apt['status'] === 'pending'): ?>
                                <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'confirmed')" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-sm font-semibold hover:bg-green-100">Confirm</button>
                            <?php endif; ?>
                            <?php if ($apt['status'] === 'confirmed'): ?>
                                <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'completed')" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-semibold hover:bg-blue-100">Complete</button>
                            <?php endif; ?>
                            <a href="view-appointment.php?id=<?php echo $apt['id']; ?>" class="px-3 py-1.5 bg-slate-50 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-100 border border-slate-100">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateStatus(id, status) {
    if(confirm(`Are you sure you want to set this appointment to ${status}?`)) {
        window.location.href = `update-appointment-status.php?id=${id}&status=${status}`;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
