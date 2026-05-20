<?php
$pageTitle = 'Kelola Admin';

if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Token keamanan tidak valid');
        redirect('/admin/users');
    }
    
    $action = $_POST['action'] ?? '';
    
    // Create user
    if ($action === 'create') {
        $validator = Validator::make($_POST, [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,super_admin'
        ]);
        
        if ($validator->fails()) {
            setFlash('danger', implode(', ', $validator->errors()));
            redirect('/admin/users');
        }
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            setFlash('danger', 'Email sudah terdaftar');
            redirect('/admin/users');
        }
        
        $userId = generateUuid();
        $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $db->prepare("
            INSERT INTO users (id, name, email, password, role, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        if ($stmt->execute([$userId, $_POST['name'], $_POST['email'], $hashedPassword, $_POST['role']])) {
            ActivityLog::log(ActivityLog::ACTION_CREATE, 'Admin membuat user: ' . $_POST['name'], null, ['user_id' => $userId]);
            setFlash('success', 'User berhasil dibuat');
        } else {
            setFlash('danger', 'Gagal membuat user');
        }
        redirect('/admin/users');
    }
    
    // Delete user
    if ($action === 'delete' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        
        // Prevent deleting self
        if ($id === $user['id']) {
            setFlash('danger', 'Tidak bisa menghapus akun sendiri');
            redirect('/admin/users');
        }
        
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role IN ('admin', 'super_admin')");
        if ($stmt->execute([$id])) {
            ActivityLog::log(ActivityLog::ACTION_DELETE, 'Admin menghapus user', null, ['user_id' => $id]);
            setFlash('success', 'User berhasil dihapus');
        } else {
            setFlash('danger', 'Gagal menghapus user');
        }
        redirect('/admin/users');
    }
}

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'role' => $_GET['role'] ?? '',
];

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Build WHERE clause
$where = ["role IN ('admin', 'super_admin')"];
$params = [];

if ($filters['search']) {
    $where[] = "(name LIKE ? OR email LIKE ?)";
    $search = '%' . $filters['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}
if ($filters['role']) {
    $where[] = "role = ?";
    $params[] = $filters['role'];
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Count total
$countSql = "SELECT COUNT(*) FROM users $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Create paginator
$paginator = new Paginator($total, 20, $page);

// Get data
$query = "
    SELECT *
    FROM users
    $whereClause
    ORDER BY created_at DESC
    {$paginator->getLimit()}
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="container py-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2>Kelola Admin</h2>
        <button onclick="showCreateForm()" class="nb-btn nb-btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Admin
        </button>
    </div>

    <!-- Create Form -->
    <div id="userForm" class="nb-card mb-4" style="display: none;">
        <h4 class="mb-3">Tambah Admin Baru</h4>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="action" value="create">
            
            <div class="nb-form-group">
                <label class="nb-label">Nama Lengkap</label>
                <input type="text" name="name" class="nb-input" required>
            </div>
            
            <div class="nb-form-group">
                <label class="nb-label">Email</label>
                <input type="email" name="email" class="nb-input" required>
            </div>
            
            <div class="nb-form-group">
                <label class="nb-label">Password</label>
                <input type="password" name="password" class="nb-input" required minlength="8">
                <small style="color: var(--gray-600);">Minimal 8 karakter</small>
            </div>
            
            <div class="nb-form-group">
                <label class="nb-label">Role</label>
                <select name="role" class="nb-input" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="nb-btn nb-btn-primary">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <button type="button" onclick="hideForm()" class="nb-btn nb-btn-outline">
                    <i class="bi bi-x"></i> Batal
                </button>
            </div>
        </form>
    </div>

    <!-- Filter Bar -->
    <div class="nb-card mb-4">
        <form method="GET">
            <div class="nb-filter-bar">
                <div class="nb-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="nb-input" placeholder="Cari nama atau email..." value="<?= e($filters['search']) ?>">
                </div>
                
                <select name="role" class="nb-input nb-filter-select">
                    <option value="">Semua Role</option>
                    <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $filters['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                
                <button type="submit" class="nb-btn nb-btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                
                <?php if (!empty(array_filter($filters))): ?>
                    <a href="<?= APP_URL ?>/admin/users" class="nb-btn nb-btn-outline">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="nb-pagination-info"><?= $paginator->getInfo() ?></div>

    <div class="nb-card">
        <?php if (count($users) > 0): ?>
            <div class="nb-table-responsive">
                <table class="nb-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php if ($u['profile_photo']): ?>
                                            <img src="<?= upload($u['profile_photo']) ?>" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #000; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #000; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                                <?= getInitials($u['name']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 600;"><?= e($u['name']) ?></div>
                                            <?php if ($u['id'] === $user['id']): ?>
                                                <span class="nb-badge" style="background: var(--info); font-size: 10px;">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= e($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'super_admin'): ?>
                                        <span class="nb-badge nb-badge-danger">Super Admin</span>
                                    <?php else: ?>
                                        <span class="nb-badge nb-badge-primary">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatDateIndo($u['created_at']) ?></td>
                                <td>
                                    <?php if ($u['id'] !== $user['id']): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus user ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="nb-btn nb-btn-sm nb-btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400);">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 48px; color: var(--gray-500);">
                <i class="bi bi-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p>Belum ada admin</p>
            </div>
        <?php endif; ?>
    </div>

    <?= $paginator->render(APP_URL . '/admin/users', $filters) ?>
</div>

<script>
function showCreateForm() {
    document.getElementById('userForm').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hideForm() {
    document.getElementById('userForm').style.display = 'none';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
