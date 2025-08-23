# Test-Driven Development (TDD) Guide: Red → Green → Refactor

This document demonstrates a pragmatic TDD workflow for the Examination System across critical components: authentication, user management, DAO/database access, admin controller actions, and router behavior.

Each section includes:
- Red: an initial failing PHPUnit test
- Green: minimal code to pass
- Refactor: clean solution using the production implementation in `src/`
- Notes: fixtures, mocks, setup/teardown, Arrange–Act–Assert and edge cases

## Conventions
- Tests live under `tests/Unit` and `tests/Integration` using PHPUnit 9.
- Bootstrap: `tests/bootstrap.php` sets testing env and cleans globals.
- Use Arrange–Act–Assert structure with clear, focused assertions.
- Use mocks for dependencies (e.g., `UserDAO`, `PDO`).

---

## 1) AuthService: login/logout and session management

### Red (failing test excerpt)
```php
// tests/Unit/Auth/AuthServiceRedTest.php
public function test_login_sets_session_on_success(): void
{
    // Arrange
    $dao = $this->createMock(App\Auth\DAO\UserDAO::class);
    $dao->method('authenticate')->willReturn([
        'user_id' => 1,
        'school_id' => '2021-0001',
        'full_name' => 'John Doe',
        'role' => 'student',
        'password' => '$hash'
    ]);

    $service = new App\Auth\Services\AuthService();
    // Inject mock via reflection (no DI constructor yet)
    (new ReflectionProperty($service, 'userDAO'))->setValue($service, $dao);

    // Act
    $result = $service->login('2021-0001', 'secret');

    // Assert
    $this->assertTrue($result['success']);
    $this->assertSame('2021-0001', $_SESSION['school_id']); // Fails if AuthService not writing session
}
```

### Green (minimal change idea)
- Ensure `AuthService::login` starts a session and writes the expected keys when authenticate returns a user.

### Refactor (use production code)
- The existing `App\Auth\Services\AuthService` already implements session handling and returns a consistent payload.
- Complementary tests in `tests/Unit/Auth/AuthServiceTest.php` cover success, failure, empty inputs, logout, `isAuthenticated`, `getCurrentUser`, role checks.

### Edge cases to cover
- Empty `school_id` or `password` ⇒ validation error
- Invalid credentials ⇒ error message and no session
- Logout clears all session data and prevents re-use
- Role gating: `requireRole('admin')` rejects other roles

---

## 2) UserService: CRUD and role validation

### Red (failing)
```php
// tests/Unit/User/UserServiceRedTest.php
public function test_create_user_requires_role_specific_fields(): void
{
    // Arrange: student without year/section should fail
    $fakeDao = new class implements App\Auth\Services\UserDAOInterface {
        // Implement methods minimally (e.g., returning defaults)
        public function findBySchoolId($id){return null;} public function findById($id){return null;}
        public function getAllUsers(){return [];} public function getUsersByRole($r){return [];} public function getStudentsByYearSection($y,$s){return [];} public function create($d){return 1;} public function update($id,$d){return true;} public function delete($id){return true;} public function authenticate($a,$b){return false;}
    };
    $service = new App\Auth\Services\UserService($fakeDao);

    // Act
    $result = $service->createUser([
        'school_id' => 'S-1',
        'full_name' => 'Stud A',
        'role' => 'student', // missing year_level and section
    ]);

    // Assert
    $this->assertFalse($result['success']); // Should fail until validation implemented
}
```

### Green
- Implement basic validation: required fields, valid roles, and for students ensure `year_level` and `section` present.

### Refactor
- Use `App\Auth\Services\UserService` which already encapsulates these rules and uses a DAO implementing `UserDAOInterface`.
- See `tests/Unit/User/UserServiceTest.php` for complete, fast in-memory fake DAO and scenarios: duplicate school_id, missing fields, update, delete, queries by role/year-section.

### Edge cases
- Duplicate `school_id` detection
- Updating `school_id` to an existing one must fail
- Deleting non-existent user returns error

---

## 3) UserDAO: database interactions (mocked PDO)

### Red
```php
// tests/Unit/DAO/UserDAORedTest.php
public function test_find_by_school_id_executes_expected_sql(): void
{
    // Arrange (mock PDO + statement)
    $pdo = $this->createMock(PDO::class);
    $stmt = $this->createMock(PDOStatement::class);

    // Expect prepare/execute/fetch calls (will fail until DAO uses PDO)
    $pdo->expects($this->once())->method('prepare')->with("SELECT * FROM users WHERE school_id = ?")->willReturn($stmt);
    $stmt->expects($this->once())->method('execute')->with(['SID-1']);
    $stmt->expects($this->once())->method('fetch')->willReturn(['user_id'=>1,'school_id'=>'SID-1']);

    // Minimal seam: inject PDO into DAO via reflection
    $dao = new App\Auth\DAO\UserDAO();
    (new ReflectionProperty($dao, 'db'))->setValue($dao, $pdo);

    // Act
    $found = $dao->findBySchoolId('SID-1');

    // Assert
    $this->assertSame('SID-1', $found['school_id']);
}
```

### Green
- Ensure DAO methods prepare, bind, execute, and fetch rows properly for all queries.

### Refactor
- The production `App\Auth\DAO\UserDAO` uses `App\Config\Database` and `PDO` with proper error handling. Unit tests in `tests/Unit/DAO/UserDAOTest.php` mock `PDO`/`PDOStatement` and inject via reflection to validate SQL and branching.

### Edge cases
- Missing row returns `false`
- Exceptions caught and converted to safe defaults
- Insert returns `lastInsertId()`

---

## 4) AdminController: user management actions and redirects

### Red
```php
// tests/Unit/Admin/AdminControllerRedTest.php
public function test_dashboard_renders_view_with_user_lists(): void
{
    // Arrange
    $ctrl = new App\Admin\Controllers\AdminController();
    $auth = $this->createMock(App\Auth\Services\AuthService::class);
    $users = $this->createMock(App\Auth\Services\UserService::class);
    $view = $this->createMock(App\Core\View::class);

    // Inject mocks via reflection
    (new ReflectionProperty($ctrl, 'authService'))->setValue($ctrl, $auth);
    (new ReflectionProperty($ctrl, 'userService'))->setValue($ctrl, $users);
    (new ReflectionProperty($ctrl, 'view'))->setValue($ctrl, $view);

    $auth->method('getCurrentUser')->willReturn(['user_id'=>99,'role'=>'admin']);
    $users->method('getUsersByRole')->willReturn([]);

    $view->expects($this->once())->method('display')->with('admin.dashboard', $this->arrayHasKey('students'));

    // Act
    $ctrl->dashboard();
}
```

### Green
- Minimal controller method should gather data and call `View->display('admin.dashboard', $data)`.

### Refactor
- Use the production `AdminController` which already orchestrates dependencies and renders views, with comprehensive unit coverage in `tests/Unit/Admin/AdminControllerTest.php` (includes redirects, logout confirmation, POST handlers, error/success flashes).

### Edge cases
- Unauthorized access, role mismatch
- POST input validation
- Redirect URLs built from `SCRIPT_NAME` base path

---

## 5) Router: request handling and parameterized routes

### Red
```php
// tests/Unit/Core/RouterRedTest.php
public function test_dispatch_invokes_route_and_echoes_result(): void
{
    // Arrange
    $router = new App\Core\Router();
    $router->get('/hello', fn() => 'world');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/hello';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    // Capture output
    ob_start();

    // Act
    $router->dispatch();

    // Assert
    $this->assertSame('world', trim(ob_get_clean())); // fails until dispatch implemented
}
```

### Green
- Implement `dispatch` to normalize the path, find a matching route, invoke callback, echo result, and handle 404.

### Refactor
- Use production `App\Core\Router` (`dispatch`, parameterized routes, `any`, and legacy `handleRequest`) with unit coverage in `tests/Unit/Core/RouterTest.php`.

### Edge cases
- Trailing slashes, subdirectory deployment stripping
- Parameterized routes `/users/{id}`
- 404 handling writes correct status and body

---

## Setup/Teardown, Fixtures, Mocks

- `tests/bootstrap.php` sets `APP_ENV=testing`, disables session cookies, and resets superglobals for isolation.
- Use `createMock()` for `UserDAO`, `AuthService`, `UserService`, `PDO`, `PDOStatement`.
- Use in-memory fakes (see `UserServiceTest.php`) for fast domain validation.
- Start/stop sessions explicitly in Auth tests to avoid cross-test contamination.

## How to Run
1) Ensure Composer autoload is up-to-date:
```bash
composer dump-autoload -o
```
2) Run all test suites:
```bash
vendor/bin/phpunit
```
3) Run only unit tests:
```bash
vendor/bin/phpunit --testsuite "Unit Tests"
```

## Closing Notes
- The Red–Green–Refactor cycle is demonstrated using minimal failing tests first, then the smallest change to pass, and finally the clean production code already present in `src/`.
- The provided unit tests under `tests/Unit` are aligned with these principles and can serve as both examples and regression safety nets.