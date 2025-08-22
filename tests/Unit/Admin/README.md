# AdminController Unit Tests

This directory contains comprehensive unit tests for the `AdminController` class, which handles administrative functions in the MVC application.

## Test Coverage

The `AdminControllerTest` covers **27 test methods** including:

### Core Admin Functionality
- `dashboard()` - Admin dashboard display with user data
- `logout()` - Logout handling and confirmation
- `showLogoutConfirmation()` - Logout confirmation page display

### Student Management (8 tests)
- `addStudent()` - Adding new students
- `editStudent()` - Editing existing students  
- `deleteStudent()` - Deleting students
- Validation handling for missing fields
- Error handling for invalid requests

### Faculty Management (8 tests) ✨ **NEW**
- `addFaculty()` - Adding new faculty members
- `editFaculty()` - Editing existing faculty members
- `deleteFaculty()` - Deleting faculty members
- Validation handling for missing fields
- Error handling for invalid requests

### General User Management (4 tests)
- `addUser()` - Adding general users
- `editUser()` - Editing general users
- `deleteUser()` - Deleting general users
- Parameter validation

### Utility Methods (3 tests)
- `getYearSections()` - Year-section grouping logic
- `showSuccess()` - Success message display
- `showError()` - Error message display

### Helper Methods (2 tests)
- `redirectToDashboard()` - Dashboard redirection
- `requireAuth()` - Authentication requirement

## Test Scenarios

### Dashboard Tests
- ✅ Display admin dashboard with user data
- ✅ Show students organized by year and section
- ✅ Show faculty members list
- ✅ Handle empty user lists gracefully

### Student Management Tests
- ✅ Add student with valid data
- ✅ Add student with missing required fields
- ✅ Edit student successfully
- ✅ Edit student without user ID
- ✅ Delete student successfully
- ✅ Delete student without user ID

### Faculty Management Tests ✨ **NEW**
- ✅ Add faculty with valid data
- ✅ Add faculty with missing required fields
- ✅ Edit faculty successfully
- ✅ Edit faculty without user ID
- ✅ Edit faculty with missing fields
- ✅ Delete faculty successfully
- ✅ Delete faculty without user ID

### General User Management Tests
- ✅ Add user with valid data
- ✅ Edit user with valid data
- ✅ Delete user with valid data
- ✅ Handle missing user ID parameter

### Authentication & Security Tests
- ✅ Require authentication for all operations
- ✅ Require admin role for access
- ✅ Handle logout with confirmation
- ✅ Show logout confirmation page

### Error Handling Tests
- ✅ Handle invalid request methods
- ✅ Handle missing required fields
- ✅ Handle service layer errors
- ✅ Display appropriate error messages
- ✅ Display success confirmations

## How to Run

### Option 1: Using the Test Runner Script
```bash
# From the tests/Unit/Admin directory
./run_tests.sh

# With verbose output
./run_tests.sh -v

# With coverage report
./run_tests.sh -c

# Both verbose and coverage
./run_tests.sh -v -c
```

### Option 2: Using PHPUnit Directly
```bash
# From the project root directory
vendor/bin/phpunit tests/Unit/Admin/AdminControllerTest.php

# With testdox format and verbose output
vendor/bin/phpunit tests/Unit/Admin/AdminControllerTest.php --testdox --verbose

# Using the local PHPUnit config
vendor/bin/phpunit --configuration tests/Unit/Admin/phpunit.xml
```

### Option 3: Using the Local PHPUnit Config
```bash
# From the tests/Unit/Admin directory
vendor/bin/phpunit --configuration phpunit.xml
```

## Dependencies

- **PHPUnit**: Testing framework
- **PHP**: 7.4+ recommended
- **Composer**: For dependency management

## Mock Strategy

The tests use PHPUnit's mocking capabilities to isolate the `AdminController` from its dependencies:

- **AuthService**: Mocked for authentication and user session management
- **UserService**: Mocked for user CRUD operations
- **View**: Mocked for template rendering

## Test Environment Setup

### Output Buffer Management
- Tests properly manage output buffering to capture controller output
- Each test starts with a clean output buffer
- Proper cleanup in tearDown() prevents buffer conflicts

### Superglobal Management
- `$_SESSION`, `$_GET`, `$_POST`, `$_SERVER` are reset for each test
- Tests simulate different HTTP request methods and input data

### Error Handling
- Header warnings are suppressed for unit tests (better tested in integration tests)
- Error reporting is managed per test to prevent interference

## Common Issues and Solutions

### "Headers already sent" Errors
- **Cause**: Output buffering conflicts or premature output
- **Solution**: Tests now properly manage output buffers and suppress header warnings

### Mock Expectation Failures
- **Cause**: Incorrect mock setup or parameter expectations
- **Solution**: Fixed mock expectations to match actual method call order and parameters

### Output Buffer Warnings
- **Cause**: Tests not properly cleaning up output buffers
- **Solution**: Improved buffer management with proper level tracking

## Integration vs Unit Testing

These tests are **unit tests** that focus on:
- ✅ Method behavior in isolation
- ✅ Mock dependency interactions
- ✅ Input validation and error handling
- ✅ Business logic verification

For testing HTTP headers, redirects, and full request/response cycles, use **integration tests** instead.

## Coverage Reports

When running with coverage (`-c` flag), reports are generated in:
- `coverage/` directory (HTML format)
- `coverage.txt` (text format)
- `junit.xml` (JUnit format for CI/CD)
- `testdox.txt` (human-readable test results)

## Best Practices Used

1. **Arrange-Act-Assert**: Clear test structure
2. **Mock Isolation**: Dependencies are properly mocked
3. **Clean State**: Each test starts with fresh superglobals
4. **Reflection**: Private methods are tested using reflection
5. **Output Capture**: Controller output is captured and asserted
6. **Error Suppression**: Appropriate warnings are suppressed for unit tests

## Future Improvements

- Add more edge case testing
- Implement integration tests for header/redirect functionality
- Add performance benchmarks for data processing methods
- Expand coverage to include more complex user scenarios