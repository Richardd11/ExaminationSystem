<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-6 mb-8">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-1">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        Faculty Dashboard
                    </h1>
                    <p class="text-lg opacity-90">
                        Welcome, <?= htmlspecialchars($faculty['full_name'] ?? 'Faculty') ?>
                    </p>
                </div>
                <div>
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/logout" class="bg-transparent border-2 border-white text-white px-6 py-2 rounded-full hover:bg-white hover:text-blue-700 transition-all duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-book text-blue-600 text-2xl mr-3"></i>
                    <h2 class="text-xl font-semibold">My Subjects</h2>
                </div>
                <p class="text-gray-600">Subject management coming soon...</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-users text-blue-600 text-2xl mr-3"></i>
                    <h2 class="text-xl font-semibold">My Classes</h2>
                </div>
                <p class="text-gray-600">Class roster coming soon...</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <i class="fas fa-clipboard-check text-blue-600 text-2xl mr-3"></i>
                    <h2 class="text-xl font-semibold">Upcoming Exams</h2>
                </div>
                <p class="text-gray-600">Exam schedule coming soon...</p>
            </div>
        </div>
    </div>
</body>
</html>