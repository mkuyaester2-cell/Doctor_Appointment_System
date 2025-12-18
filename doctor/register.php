<?php
// doctor/register.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Session.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    $role = Session::get('user_type');
    header("Location: " . APP_URL . "/$role/dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $qualification = $_POST['qualification'] ?? '';
    $experience = $_POST['experience'] ?? 0;
    $fee = $_POST['fee'] ?? 0;
    $bio = $_POST['bio'] ?? '';
    
    // Address data
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zip'] ?? '';

    // Basic Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($specialization)) {
        $error = 'Please fill in all required fields.';
    } else {
        $db = Database::getInstance();
        $auth = new Auth();
        
        try {
            $db->beginTransaction();
            
            // 1. Create User Account
            $user_id = $auth->register($email, $password, 'doctor');
            
            if ($user_id === false) {
                throw new Exception("Email already registered. Please login.");
            }
            
            // 2. Create Address
            $stmt = $db->prepare("INSERT INTO addresses (street_address, city, state, postal_code) VALUES (?, ?, ?, ?)");
            $stmt->execute([$street, $city, $state, $zip]);
            $address_id = $db->lastInsertId();
            
            // 3. Get first clinic or create a default one
            $stmt = $db->query("SELECT id FROM clinics LIMIT 1");
            $clinic = $stmt->fetch();
            $clinic_id = $clinic ? $clinic['id'] : null;
            
            if (!$clinic_id) {
                $stmt = $db->prepare("INSERT INTO clinics (name, address_id, phone, email, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['General Clinic', $address_id, $phone, $email, $user_id]);
                $clinic_id = $db->lastInsertId();
            }

            // 4. Create Doctor Profile
            $stmt = $db->prepare("INSERT INTO doctors (user_id, full_name, specialization, qualification, experience_years, phone, bio, clinic_id, address_id, consultation_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $fullname, $specialization, $qualification, $experience, $phone, $bio, $clinic_id, $address_id, $fee]);
            $doctor_id = $db->lastInsertId();

            // 5. Create Default Availability (Mon-Fri, 9AM-5PM, 30 min slots)
            $stmt = $db->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration) VALUES (?, ?, '09:00:00', '17:00:00', 30)");
            for ($day = 1; $day <= 5; $day++) {
                $stmt->execute([$doctor_id, $day]);
            }
            
            $db->commit();
            
            // Auto Login
            $auth->login($email, $password);
            Session::setFlash('success', 'Registration successful! Welcome to the medical team.');
            header("Location: " . APP_URL . "/doctor/dashboard.php");
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = $e->getMessage();
        }
    }
}

define('PAGE_TITLE', 'Doctor Registration');
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
            <!-- Form Header -->
            <div class="bg-primary-600 px-8 py-10 text-center text-white relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/medical-icons.png')]"></div>
                <h2 class="text-3xl font-bold relative z-10">Join as a Doctor</h2>
                <p class="mt-2 text-primary-100 relative z-10">Create your professional profile and start managing your practice</p>
            </div>
            
            <div class="px-8 py-10">
                <?php if ($error): ?>
                    <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                        <p class="text-red-700"><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-8">
                    <!-- Personal Details -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800 border-b pb-2 mb-6 flex items-center gap-2">
                            <i class="fa-regular fa-user text-primary-500"></i> Professional Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name (including Dr. prefix)</label>
                                <input type="text" name="fullname" required placeholder="Dr. John Doe" class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Specialization</label>
                                <input type="text" name="specialization" required placeholder="e.g. Cardiologist" class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Qualification</label>
                                <input type="text" name="qualification" required placeholder="e.g. MBBS, MD" class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Years of Experience</label>
                                <input type="number" name="experience" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Consultation Fee ($)</label>
                                <input type="number" name="fee" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Professional Bio</label>
                                <textarea name="bio" rows="3" class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3" placeholder="Briefly describe your expertise..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Clinic Address -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800 border-b pb-2 mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-map-pin text-primary-500"></i> Clinic Address
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Street Address</label>
                                <input type="text" name="street" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                                <input type="text" name="city" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">State</label>
                                <input type="text" name="state" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Postal Code</label>
                                <input type="text" name="zip" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                        </div>
                    </div>

                    <!-- Account Details -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800 border-b pb-2 mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-lock text-primary-500"></i> Account Security
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                                <input type="email" name="email" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3">
                            </div>
                            
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                <input type="password" name="password" required class="w-full rounded-xl border-slate-300 focus:border-primary-500 focus:ring-primary-500 py-3" placeholder="Min. 8 characters">
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-lg font-bold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Register as Doctor
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-slate-600">
                            Already have an account? 
                            <a href="<?php echo APP_URL; ?>/login.php" class="font-bold text-primary-600 hover:text-primary-500 px-1">Login here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
