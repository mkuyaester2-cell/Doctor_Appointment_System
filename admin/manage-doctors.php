<?php
// admin/manage-doctors.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';

Auth::requireRole('admin');

$db = Database::getInstance();

// Handle Approval/Rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $db->prepare("UPDATE doctors SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    Session::setFlash('success', "Doctor account has been " . $status);
    header("Location: manage-doctors.php");
    exit;
}

define('PAGE_TITLE', 'Manage Doctors');
require_once __DIR__ . '/../includes/header.php';

// Fetch Doctors
$stmt = $db->query("
    SELECT d.*, u.email 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    ORDER BY CASE WHEN d.status = 'pending' THEN 0 ELSE 1 END, d.created_at DESC
");
$doctors = $stmt->fetchAll();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Manage Doctors</h1>
            <a href="dashboard.php" class="text-slate-500 hover:text-primary-600 font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Doctor</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Contact</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($doctors as $doc): 
                            $statusClasses = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700'
                            ];
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900"><?php echo htmlspecialchars($doc['full_name']); ?></div>
                                    <div class="text-xs text-slate-500"><?php echo htmlspecialchars($doc['specialization']); ?> • <?php echo $doc['experience_years']; ?> yrs exp</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600"><?php echo htmlspecialchars($doc['email']); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($doc['phone']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $statusClasses[$doc['status']]; ?>">
                                        <?php echo $doc['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($doc['status'] === 'pending'): ?>
                                        <div class="flex justify-end gap-2">
                                            <a href="?action=approve&id=<?php echo $doc['id']; ?>" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-bold hover:bg-green-700 shadow-sm" onclick="return confirm('Approve this doctor?')">Approve</a>
                                            <a href="?action=reject&id=<?php echo $doc['id']; ?>" class="px-3 py-1.5 bg-white border border-red-200 text-red-600 rounded-lg text-xs font-bold hover:bg-red-50" onclick="return confirm('Reject this doctor?')">Reject</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs font-medium italic">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($doctors)): ?>
                            <tr><td colspan="4" class="px-6 py-20 text-center text-slate-400">No doctors registered yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
