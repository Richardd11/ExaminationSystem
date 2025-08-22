<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
                        grey: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-grey-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-6 mb-8">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-1">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Admin Dashboard
                    </h1>
                    <p class="text-lg opacity-90">
                        Welcome back, <?= htmlspecialchars($admin['full_name'] ?? 'Admin') ?>
                    </p>
                </div>
                <div>
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/logout" class="bg-transparent border-2 border-white text-white px-6 py-2 rounded-full hover:bg-white hover:text-primary-600 transition-all duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4">
        <!-- Session Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex justify-between items-center" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex justify-between items-center" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                </div>
                <button type="button" class="text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="border-b-2 border-grey-200 mb-0">
            <div class="flex space-x-1">
                <button class="bg-white text-primary-600 font-semibold px-6 py-4 rounded-t-lg border-b-2 border-primary-600 hover:bg-grey-50 transition-all duration-300" id="users-tab" onclick="showTab('users')">
                    <i class="fas fa-users mr-2"></i>
                    Manage Users
                </button>
                <button class="text-grey-600 font-semibold px-6 py-4 rounded-t-lg hover:bg-white hover:text-primary-600 transition-all duration-300" id="subjects-tab" onclick="showTab('subjects')">
                    <i class="fas fa-book mr-2"></i>
                    Manage Subjects
                </button>
                <button class="text-grey-600 font-semibold px-6 py-4 rounded-t-lg hover:bg-white hover:text-primary-600 transition-all duration-300" id="assignments-tab" onclick="showTab('assignments')">
                    <i class="fas fa-link mr-2"></i>
                    Subject Assignments
                </button>
                <button class="text-grey-600 font-semibold px-6 py-4 rounded-t-lg hover:bg-white hover:text-primary-600 transition-all duration-300" id="reports-tab" onclick="showTab('reports')">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Reports
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-b-lg p-8 shadow-lg">
            <!-- Tab 1: Manage Users -->
            <div id="users" class="tab-content active">
                <?php include 'manage-users.php'; ?>
            </div>

            <!-- Tab 2: Manage Subjects -->
            <div id="subjects" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-book text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Manage Subjects</h4>
                    <p class="text-grey-500">Subject management functionality coming soon...</p>
                </div>
            </div>

            <!-- Tab 3: Subject Assignments -->
            <div id="assignments" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-link text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Subject Assignments</h4>
                    <p class="text-grey-500">Assignment functionality coming soon...</p>
                </div>
            </div>

            <!-- Tab 4: Reports -->
            <div id="reports" class="tab-content hidden">
                <div class="text-center py-12">
                    <i class="fas fa-chart-bar text-6xl text-grey-400 mb-4"></i>
                    <h4 class="text-xl font-semibold text-grey-700 mb-2">Reports & Analytics</h4>
                    <p class="text-grey-500">Reporting functionality coming soon...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab Switching
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('bg-white', 'text-primary-600', 'border-primary-600');
                tab.classList.add('text-grey-600');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.remove('hidden');
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('text-grey-600');
            activeTab.classList.add('bg-white', 'text-primary-600', 'border-primary-600');
        }

        // Year-Section Tab Switching
        document.addEventListener('DOMContentLoaded', function() {
            const yearSectionTabs = document.querySelectorAll('.year-section-tab');
            const studentSections = document.querySelectorAll('.student-section');

            yearSectionTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetSection = this.getAttribute('data-section');
                    
                    // Update active tab
                    yearSectionTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show/hide sections
                    studentSections.forEach(section => {
                        if (section.id === targetSection) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                });
            });

            // Show first section by default
            if (yearSectionTabs.length > 0) {
                yearSectionTabs[0].click();
            }
        });
    </script>
</body>
</html>