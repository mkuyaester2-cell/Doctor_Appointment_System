<?php
// includes/footer.php
?>
</main>

<!-- Footer -->
<footer class="bg-white border-t border-slate-200 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-1">
                <a href="<?php echo APP_URL; ?>" class="flex items-center gap-2 mb-6">
                    <div class="bg-primary-600 text-white p-1.5 rounded-lg">
                        <i class="fa-solid fa-user-doctor text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-slate-800">DocBook</span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Connecting patients with the best healthcare professionals. Book appointments 24/7 with zero hassle.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                        <i class="fa-brands fa-twitter"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="font-bold text-slate-800 mb-4">For Patients</h3>
                <ul class="space-y-3 text-sm text-slate-500">
                    <li><a href="<?php echo APP_URL; ?>/patient/browse-doctors.php" class="hover:text-primary-600 transition-colors">Find a Doctor</a></li>
                    <li><a href="#" class="hover:text-primary-600 transition-colors">Clinics</a></li>
                    <li><a href="#" class="hover:text-primary-600 transition-colors">Health Blog</a></li>
                    <li><a href="#" class="hover:text-primary-600 transition-colors">Mobile App</a></li>
                </ul>
            </div>

            <!-- For Doctors -->
            <div>
                <h3 class="font-bold text-slate-800 mb-4">For Doctors</h3>
                <ul class="space-y-3 text-sm text-slate-500">
                    <li><a href="<?php echo APP_URL; ?>/login.php" class="hover:text-primary-600 transition-colors">Doctor Login</a></li>
                    <li><a href="#" class="hover:text-primary-600 transition-colors">Join as a Doctor</a></li>
                    <li><a href="#" class="hover:text-primary-600 transition-colors">Practice Management</a></li>
                    <li><a href="#" class="hover:text-primary-600 transition-colors">Success Stories</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-200 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex space-x-6 text-sm text-slate-400">
                <a href="#" class="hover:text-slate-600">Privacy Policy</a>
                <a href="#" class="hover:text-slate-600">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Simple Mobile Menu Toggle
    const btn = document.querySelector('button.md\\:hidden');
    const menu = document.querySelector('.md\\:flex');
    // Implement toggle logic if needed, for MVP using simple layout
</script>
</body>
</html>
