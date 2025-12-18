<?php
// doctor/schedule.php
define('PAGE_TITLE', 'My Schedule');
require_once __DIR__ . '/../includes/header.php';

Auth::requireRole('doctor');

$user_id = Auth::id();
$db = Database::getInstance();

// Fetch Doctor ID
$stmt = $db->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$user_id]);
$doctor_id = $stmt->fetch()['id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $days = $_POST['days'] ?? [];
    $start_time = $_POST['start_time'] ?? '09:00';
    $end_time = $_POST['end_time'] ?? '17:00';
    $duration = $_POST['duration'] ?? 30;

    try {
        $db->beginTransaction();
        
        // Clear existing availability
        $stmt = $db->prepare("DELETE FROM doctor_availability WHERE doctor_id = ?");
        $stmt->execute([$doctor_id]);
        
        // Insert new availability
        $stmt = $db->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($days as $day) {
            $stmt->execute([$doctor_id, $day, $start_time, $end_time, $duration]);
        }
        
        $db->commit();
        $message = "Schedule updated successfully!";
        Session::setFlash('success', "Your working hours have been updated.");
    } catch (Exception $e) {
        $db->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch current schedule
$stmt = $db->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$current_schedule = $stmt->fetchAll();

$selected_days = array_column($current_schedule, 'day_of_week');
$first_row = $current_schedule[0] ?? ['start_time' => '09:00:00', 'end_time' => '17:00:00', 'slot_duration' => 30];

$day_names = [
    1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'
];
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="dashboard.php" class="inline-flex items-center text-slate-500 hover:text-primary-600 mb-6 transition-colors font-medium">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
        </a>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-primary-600 px-8 py-10 text-white">
                <h1 class="text-3xl font-bold">Manage Working Hours</h1>
                <p class="text-primary-100 mt-2">Set your weekly availability and consultation duration</p>
            </div>

            <form action="" method="POST" class="p-8 space-y-8">
                <!-- Working Days -->
                <div>
                    <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-primary-500"></i> Working Days
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <?php foreach ($day_names as $val => $name): ?>
                            <label class="relative flex items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-slate-50 
                                <?php echo in_array($val, $selected_days) ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-100 text-slate-500'; ?>">
                                <input type="checkbox" name="days[]" value="<?php echo $val; ?>" class="hidden" <?php echo in_array($val, $selected_days) ? 'checked' : ''; ?> onchange="this.parentElement.classList.toggle('border-primary-500'); this.parentElement.classList.toggle('bg-primary-50'); this.parentElement.classList.toggle('text-primary-700'); this.parentElement.classList.toggle('border-slate-100'); this.parentElement.classList.toggle('text-slate-500');">
                                <span class="font-bold text-sm uppercase tracking-wider"><?php echo substr($name, 0, 3); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Time & Duration -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Start Time</label>
                        <input type="time" name="start_time" value="<?php echo date('H:i', strtotime($first_row['start_time'])); ?>" class="w-full rounded-xl border-slate-200 focus:border-primary-500 focus:ring-primary-500 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">End Time</label>
                        <input type="time" name="end_time" value="<?php echo date('H:i', strtotime($first_row['end_time'])); ?>" class="w-full rounded-xl border-slate-200 focus:border-primary-500 focus:ring-primary-500 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Slot Duration (min)</label>
                        <select name="duration" class="w-full rounded-xl border-slate-200 focus:border-primary-500 focus:ring-primary-500 py-3">
                            <option value="15" <?php echo $first_row['slot_duration'] == 15 ? 'selected' : ''; ?>>15 Minutes</option>
                            <option value="30" <?php echo $first_row['slot_duration'] == 30 ? 'selected' : ''; ?>>30 Minutes</option>
                            <option value="45" <?php echo $first_row['slot_duration'] == 45 ? 'selected' : ''; ?>>45 Minutes</option>
                            <option value="60" <?php echo $first_row['slot_duration'] == 60 ? 'selected' : ''; ?>>60 Minutes</option>
                            <option value="90" <?php echo $first_row['slot_duration'] == 90 ? 'selected' : ''; ?>>90 Minutes</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="submit" class="w-full bg-slate-900 border border-slate-900 text-white font-bold py-4 rounded-2xl hover:bg-slate-800 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Save Schedule Changes
                    </button>
                    <p class="text-center text-slate-400 text-xs mt-4">Note: Changing your schedule won't affect existing booked appointments.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/header.php'; ?>
