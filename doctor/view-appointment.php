<?php
// doctor/view-appointment.php
define('PAGE_TITLE', 'Appointment Details');
require_once __DIR__ . '/../includes/header.php';

Auth::requireRole('doctor');

$apt_id = $_GET['id'] ?? null;
if (!$apt_id) {
    header("Location: dashboard.php");
    exit;
}

$db = Database::getInstance();
$user_id = Auth::id();

// Fetch appointment details and ensure ownership
$stmt = $db->prepare("
    SELECT a.*, p.full_name as patient_name, p.phone as patient_phone, p.gender, p.dob,
           addr.street_address as patient_address, addr.city as patient_city
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN users u ON a.doctor_id = (SELECT id FROM doctors WHERE user_id = ?)
    LEFT JOIN addresses addr ON p.address_id = addr.id
    WHERE a.id = ?
");
$stmt->execute([$user_id, $apt_id]);
$apt = $stmt->fetch();

if (!$apt) {
    die("Appointment not found or unauthorized access.");
}
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="dashboard.php" class="inline-flex items-center text-slate-500 hover:text-primary-600 mb-6 transition-colors font-medium">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
        </a>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Appointment #<?php echo $apt['id']; ?></h1>
                    <p class="text-slate-500">Booked on <?php echo date('M d, Y', strtotime($apt['created_at'])); ?></p>
                </div>
                <span class="px-4 py-1.5 rounded-full text-sm font-bold uppercase tracking-wider 
                    <?php echo $apt['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'; ?>">
                    <?php echo $apt['status']; ?>
                </span>
            </div>

            <div class="p-8 grid md:grid-cols-2 gap-12">
                <!-- Patient Info -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-user-injured text-primary-500"></i> Patient Details
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold">Full Name</p>
                            <p class="text-slate-900 font-medium"><?php echo htmlspecialchars($apt['patient_name']); ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold">Gender</p>
                                <p class="text-slate-900 font-medium"><?php echo $apt['gender']; ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold">Age</p>
                                <p class="text-slate-900 font-medium"><?php echo date_diff(date_create($apt['dob']), date_create('today'))->y; ?> Years</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold">Phone Number</p>
                            <p class="text-slate-900 font-medium"><?php echo htmlspecialchars($apt['patient_phone']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold">Address</p>
                            <p class="text-slate-900 font-medium"><?php echo htmlspecialchars($apt['patient_address'] . ', ' . $apt['patient_city']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Schedule Info -->
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-primary-500"></i> Schedule
                    </h2>
                    <div class="bg-blue-50 p-6 rounded-2xl space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-primary-600 shadow-sm">
                                <i class="fa-regular fa-calendar-days"></i>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 font-bold uppercase">Date</p>
                                <p class="text-slate-900 font-bold"><?php echo date('l, M d, Y', strtotime($apt['appointment_date'])); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-primary-600 shadow-sm">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 font-bold uppercase">Time</p>
                                <p class="text-slate-900 font-bold"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
                        <?php if ($apt['status'] === 'pending'): ?>
                            <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'confirmed')" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-green-200">
                                Confirm Appointment
                            </button>
                        <?php endif; ?>
                        <?php if ($apt['status'] === 'confirmed'): ?>
                            <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'completed')" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-primary-200">
                                Mark as Completed
                            </button>
                        <?php endif; ?>
                        <?php if ($apt['status'] !== 'cancelled' && $apt['status'] !== 'completed'): ?>
                            <button onclick="updateStatus(<?php echo $apt['id']; ?>, 'cancelled')" class="w-full bg-white border border-red-200 text-red-600 font-bold py-3 rounded-xl hover:bg-red-50 transition-all">
                                Cancel Appointment
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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
