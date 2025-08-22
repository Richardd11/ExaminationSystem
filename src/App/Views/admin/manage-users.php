<!-- Top Section - Add User Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Users
            </h4>
            <p class="text-grey-600">Add students and faculty to the system</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1" onclick="showAddStudentModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Student
            </button>
            <button class="bg-transparent border-2 border-grey-500 text-grey-600 hover:bg-grey-500 hover:text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300" onclick="showAddFacultyModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Faculty
            </button>
        </div>
    </div>
</div>

<!-- Students Section - Organized by Year & Section -->
<div class="mb-8">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-graduation-cap mr-2 text-primary-600"></i>
        Students by Year & Section
    </h5>
    
    <!-- Year-Section Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <?php 
        $firstSection = true;
        foreach ($yearSections as $yearSection => $count): 
        ?>
            <button class="year-section-tab <?= $firstSection ? 'active' : '' ?> bg-grey-100 border border-grey-300 text-grey-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-white hover:border-primary-600" 
                    data-section="section-<?= str_replace(' ', '-', strtolower($yearSection)) ?>">
                <?= $yearSection ?>
                <span class="bg-green-500 text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2"><?= $count ?></span>
            </button>
        <?php 
            $firstSection = false;
        endforeach; 
        ?>
    </div>

    <!-- Student Sections Content -->
    <?php 
    $firstSection = true;
    foreach ($yearSections as $yearSection => $count): 
        $sectionId = 'section-' . str_replace(' ', '-', strtolower($yearSection));
        $sectionStudents = array_filter($students, function($student) use ($yearSection) {
            return ($student['year_level'] . ' ' . $student['section']) === $yearSection;
        });
    ?>
        <div class="student-section <?= $firstSection ? 'active' : '' ?> <?= !$firstSection ? 'hidden' : '' ?>" 
             id="<?= $sectionId ?>">
            
            <!-- Section Header -->
            <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-lg font-bold text-primary-600">
                            <i class="fas fa-users mr-2"></i>
                            <?= $yearSection ?>
                        </h6>
                    </div>
                    <div>
                        <span class="text-grey-600 text-sm"><?= $count ?> students</span>
                    </div>
                </div>
            </div>

            <!-- Student Cards -->
            <?php foreach ($sectionStudents as $student): ?>
                <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="text-lg font-bold text-primary-600 mb-2">
                                <i class="fas fa-user-graduate mr-2"></i>
                                <?= htmlspecialchars($student['full_name']) ?>
                            </div>
                            <div class="text-grey-600 text-sm mb-2">
                                <i class="fas fa-id-card mr-2"></i>
                                <?= htmlspecialchars($student['school_id']) ?>
                            </div>
                            <div class="text-grey-500 text-xs">
                                <i class="fas fa-calendar mr-2"></i>
                                Added on <?= date('M d, Y', strtotime($student['created_at'])) ?>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editStudent(<?= $student['user_id'] ?>)">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteStudent(<?= $student['user_id'] ?>)">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php 
        $firstSection = false;
    endforeach; 
    ?>
</div>

<!-- Faculty Section -->
<div class="mt-12">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-chalkboard-teacher mr-2 text-primary-600"></i>
        Faculty Members
    </h5>
    
    <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
        <div class="flex justify-between items-center">
            <div>
                <h6 class="text-lg font-bold text-primary-600">
                    <i class="fas fa-users mr-2"></i>
                    All Faculty
                </h6>
            </div>
            <div>
                <span class="text-grey-600 text-sm"><?= count($faculty) ?> faculty members</span>
            </div>
        </div>
    </div>

    <!-- Faculty Cards -->
    <?php foreach ($faculty as $facultyMember): ?>
        <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <div class="text-lg font-bold text-primary-600 mb-2">
                        <i class="fas fa-user-tie mr-2"></i>
                        <?= htmlspecialchars($facultyMember['full_name']) ?>
                    </div>
                    <div class="text-grey-600 text-sm mb-2">
                        <i class="fas fa-id-card mr-2"></i>
                        <?= htmlspecialchars($facultyMember['school_id']) ?>
                    </div>
                    <div class="text-grey-500 text-xs">
                        <i class="fas fa-calendar mr-2"></i>
                        Added on <?= date('M d, Y', strtotime($facultyMember['created_at'])) ?>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editFaculty(<?= $facultyMember['user_id'] ?>)">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteFaculty(<?= $facultyMember['user_id'] ?>)">
                        <i class="fas fa-trash mr-1"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Edit Student Function
function editStudent(studentId) {
    // TODO: Fetch student data and populate form
    console.log('Edit student:', studentId);
    // For now, show a simple form
    showEditStudentModal(studentId);
}

// Show Edit Student Modal
function showEditStudentModal(studentId) {
    document.getElementById('editStudentModal').classList.remove('hidden');
    document.getElementById('editStudentId').value = studentId;
}

// Delete Student Function
function deleteStudent(studentId) {
    if (confirm('Are you sure you want to delete this student?')) {
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/delete-student';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = studentId;
        
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Edit Faculty Function
function editFaculty(facultyId) {
    // TODO: Fetch faculty data and populate form
    console.log('Edit faculty:', facultyId);
    // For now, show a simple form
    showEditFacultyModal(facultyId);
}

// Show Edit Faculty Modal
function showEditFacultyModal(facultyId) {
    document.getElementById('editFacultyModal').classList.remove('hidden');
    document.getElementById('editFacultyId').value = facultyId;
}

// Delete Faculty Function
function deleteFaculty(facultyId) {
    if (confirm('Are you sure you want to delete this faculty member?')) {
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/delete-faculty';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = facultyId;
        
        form.appendChild(userIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Add Student Modal
function showAddStudentModal() {
    document.getElementById('addStudentModal').classList.remove('hidden');
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Reset Form Fields
function resetForm(formId) {
    document.getElementById(formId).reset();
}

// Add Faculty Modal
function showAddFacultyModal() {
    document.getElementById('addFacultyModal').classList.remove('hidden');
}

// Year-Section Tab Switching
document.addEventListener('DOMContentLoaded', function() {
    const yearSectionTabs = document.querySelectorAll('.year-section-tab');
    const studentSections = document.querySelectorAll('.student-section');

    yearSectionTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            
            // Update active tab
            yearSectionTabs.forEach(t => {
                t.classList.remove('active', 'bg-primary-600', 'text-white', 'border-primary-600');
                t.classList.add('bg-grey-100', 'text-grey-600', 'border-grey-300');
            });
            this.classList.add('active', 'bg-primary-600', 'text-white', 'border-primary-600');
            this.classList.remove('bg-grey-100', 'text-grey-600', 'border-grey-300');
            
            // Show/hide sections
            studentSections.forEach(section => {
                if (section.id === targetSection) {
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
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

<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Student
            </h3>
            <button onclick="closeModal('editStudentModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editStudentForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/edit-student" method="POST">
                <input type="hidden" id="editStudentId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="edit_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="edit_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="edit_year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_section" class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select id="edit_section" name="section" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editStudentModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Update Student
            </button>
        </div>
    </div>
</div>

<script>
// Submit edit form function
function submitEditForm() {
    const form = document.getElementById('editStudentForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Close modal after submission
        setTimeout(() => {
            closeModal('editStudentModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close edit modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editStudentModal');
    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeModal('editStudentModal');
        }
    });
});
</script>

<!-- Add Student Modal -->
<div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Student
            </h3>
            <button onclick="closeModal('addStudentModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addStudentForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add-student" method="POST" onsubmit="handleFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="section" class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select id="section" name="section" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-grey-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-grey-500 mt-1">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('addStudentModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Add Student
            </button>
        </div>
    </div>
</div>

<script>
// Handle form submission
function handleFormSubmit(event) {
    // Form will submit normally to controller
    // Controller will handle validation and redirect
}

// Submit form function
function submitForm() {
    const form = document.getElementById('addStudentForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Reset form fields after submission
        setTimeout(() => {
            resetForm('addStudentForm');
            closeModal('addStudentModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addStudentModal');
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal('addStudentModal');
        }
    });
});
</script>

<!-- Edit Faculty Modal -->
<div id="editFacultyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Faculty
            </h3>
            <button onclick="closeModal('editFacultyModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editFacultyForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/edit-faculty" method="POST">
                <input type="hidden" id="editFacultyId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_faculty_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="edit_faculty_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_faculty_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="edit_faculty_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>

                <input type="hidden" name="role" value="faculty">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editFacultyModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditFacultyForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Update Faculty
            </button>
        </div>
    </div>
</div>

<script>
// Submit edit faculty form function
function submitEditFacultyForm() {
    const form = document.getElementById('editFacultyForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Close modal after submission
        setTimeout(() => {
            closeModal('editFacultyModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close edit faculty modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const editFacultyModal = document.getElementById('editFacultyModal');
    editFacultyModal.addEventListener('click', function(e) {
        if (e.target === editFacultyModal) {
            closeModal('editFacultyModal');
        }
    });
});
</script>

<!-- Add Faculty Modal -->
<div id="addFacultyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Faculty
            </h3>
            <button onclick="closeModal('addFacultyModal')" class="text-grey-400 hover:text-grey-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addFacultyForm" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add-faculty" method="POST" onsubmit="handleFacultyFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="faculty_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="faculty_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="faculty_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="faculty_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="faculty_password" class="block text-sm font-medium text-grey-700 mb-2">Password</label>
                    <input type="password" id="faculty_password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-grey-500 mt-1">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="faculty">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('addFacultyModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitFacultyForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Add Faculty
            </button>
        </div>
    </div>
</div>

<script>
// Handle faculty form submission
function handleFacultyFormSubmit(event) {
    // Form will submit normally to controller
    // Controller will handle validation and redirect
}

// Submit faculty form function
function submitFacultyForm() {
    const form = document.getElementById('addFacultyForm');
    if (form.checkValidity()) {
        // Submit form to controller
        form.submit();
        // Reset form fields after submission
        setTimeout(() => {
            resetForm('addFacultyForm');
            closeModal('addFacultyModal');
        }, 100);
    } else {
        // Show validation errors
        form.reportValidity();
    }
}

// Close faculty modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addFacultyModal');
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal('addFacultyModal');
        }
    });
});
</script>