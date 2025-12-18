<?php
// admin/view-patients.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';

Auth::requireRole('admin');

$db = Database::getInstance();

define('PAGE_TITLE', 'All Patients');
require_once __DIR__ . '/../includes/header.php';

// Fetch all patients
$stmt = $db->query("
    SELECT p.*, u.email, a.city 
    FROM patients p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN addresses a ON p.address_id = a.id
    ORDER BY p.created_at DESC
");
$patients = $stmt->fetchAll();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">All Patients</h1>
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
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Contact</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Gender/Age</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($patients as $p): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($p['full_name']); ?></div>
                                    <div class="text-xs text-slate-400 font-medium">Joined <?php echo date('M Y', strtotime($p['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600"><?php echo htmlspecialchars($p['email']); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($p['phone']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600"><?php echo $p['gender']; ?></div>
                                    <div class="text-xs text-slate-400"><?php echo $p['dob'] ? date_diff(date_create($p['dob']), date_create('today'))->y . ' yrs' : 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600"><?php echo htmlspecialchars($p['city'] ?: 'N/A'); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($patients)): ?>
                            <tr><td colspan="4" class="px-6 py-20 text-center text-slate-400">No patients registered yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
