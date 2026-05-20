<?php
$pageTitle = 'Kelola Berita';

if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// Handle AJAX request to get post data
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_GET['get_post'])) {
    $postId = $_GET['get_post'];
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    header('Content-Type: application/json');
    if ($post) {
        echo json_encode([
            'success' => true,
            'post' => $post
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Post not found'
        ]);
    }
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Token keamanan tidak valid');
        redirect('/admin/posts');
    }
    
    $action = $_POST['action'] ?? '';
    
    // Create post
    if ($action === 'create') {
        // Debug: log POST data
        error_log('POST title: ' . ($_POST['title'] ?? 'empty'));
        error_log('POST content length: ' . strlen($_POST['content'] ?? ''));
        error_log('POST content preview: ' . substr($_POST['content'] ?? '', 0, 100));
        
        $validator = Validator::make($_POST, [
            'title' => 'required|max:255',
            'content' => 'required'
        ]);
        
        if ($validator->fails()) {
            setFlash('danger', implode(', ', $validator->errors()));
            redirect('/admin/posts');
        }
        
        $postId = generateUuid();
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['image'], 'posts', ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
            if ($upload['success']) {
                $imagePath = $upload['path'];
            }
        }
        
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        $publishedAt = $isPublished ? date('Y-m-d H:i:s') : null;
        
        $stmt = $db->prepare("
            INSERT INTO posts (id, title, content, image, is_published, published_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        if ($stmt->execute([$postId, $_POST['title'], $_POST['content'], $imagePath, $isPublished, $publishedAt])) {
            ActivityLog::log(ActivityLog::ACTION_CREATE, 'Admin membuat post: ' . $_POST['title'], null, ['post_id' => $postId]);
            Cache::forget('latest_posts');
            setFlash('success', 'Post berhasil dibuat');
            // Redirect to same page without edit parameter to reset form
            redirect('/admin/posts');
        } else {
            setFlash('danger', 'Gagal membuat post');
        }
        redirect('/admin/posts');
    }
    
    // Update post
    if ($action === 'update' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        
        $validator = Validator::make($_POST, [
            'title' => 'required|max:255',
            'content' => 'required'
        ]);
        
        if ($validator->fails()) {
            setFlash('danger', implode(', ', $validator->errors()));
            redirect('/admin/posts');
        }
        
        // Handle image upload
        $imagePath = $_POST['existing_image'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image
            if ($imagePath) {
                deleteFile($imagePath);
            }
            $upload = uploadFile($_FILES['image'], 'posts', ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
            if ($upload['success']) {
                $imagePath = $upload['path'];
            }
        }
        
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        $publishedAt = $isPublished ? date('Y-m-d H:i:s') : null;
        
        $stmt = $db->prepare("
            UPDATE posts 
            SET title = ?, content = ?, image = ?, is_published = ?, published_at = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$_POST['title'], $_POST['content'], $imagePath, $isPublished, $publishedAt, $id])) {
            ActivityLog::log(ActivityLog::ACTION_UPDATE, 'Admin mengupdate post: ' . $_POST['title'], null, ['post_id' => $id]);
            Cache::forget('latest_posts');
            setFlash('success', 'Post berhasil diupdate');
        } else {
            setFlash('danger', 'Gagal mengupdate post');
        }
        redirect('/admin/posts');
    }
    
    // Delete post
    if ($action === 'delete' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        
        $stmt = $db->prepare("SELECT image FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        
        if ($post && $post['image']) {
            deleteFile($post['image']);
        }
        
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
        if ($stmt->execute([$id])) {
            ActivityLog::log(ActivityLog::ACTION_DELETE, 'Admin menghapus post', null, ['post_id' => $id]);
            Cache::forget('latest_posts');
            setFlash('success', 'Post berhasil dihapus');
        } else {
            setFlash('danger', 'Gagal menghapus post');
        }
        redirect('/admin/posts');
    }
    
    // Toggle publish status
    if ($action === 'toggle_publish' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '1' ? 1 : 0;
        $publishedAt = $isPublished ? date('Y-m-d H:i:s') : null;
        
        $stmt = $db->prepare("UPDATE posts SET is_published = ?, published_at = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$isPublished, $publishedAt, $id])) {
            ActivityLog::log(ActivityLog::ACTION_UPDATE, 'Admin mengubah status publish post', null, ['post_id' => $id, 'is_published' => $isPublished]);
            Cache::forget('latest_posts');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
    
    // Bulk delete
    if ($action === 'bulk_delete' && !empty($_POST['ids'])) {
        $result = BulkAction::delete('posts', $_POST['ids'], 'Bulk delete posts');
        Cache::forget('latest_posts');
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect('/admin/posts');
    }
}

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Build WHERE clause
$where = [];
$params = [];

if ($filters['search']) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $search = '%' . $filters['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}
if ($filters['status']) {
    if ($filters['status'] === 'published') {
        $where[] = "is_published = 1";
    } else {
        $where[] = "is_published = 0";
    }
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countSql = "SELECT COUNT(*) FROM posts $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Create paginator
$paginator = new Paginator($total, 20, $page);

// Get data
$query = "
    SELECT *
    FROM posts
    $whereClause
    ORDER BY created_at DESC
    {$paginator->getLimit()}
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get edit post if requested
$editPost = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editPost = $stmt->fetch();
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="container py-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2>Kelola Berita</h2>
        <a href="<?= APP_URL ?>/blog" target="_blank" class="nb-btn nb-btn-outline">
            <i class="bi bi-eye"></i> Lihat Semua Berita
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;"><?= $total ?></div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Total Berita</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--success);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM posts WHERE is_published = 1");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Published</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--warning);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM posts WHERE is_published = 0");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Draft</div>
        </div>
    </div>

    <!-- Two Column Layout: Form (Left) + List (Right) -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- LEFT COLUMN: Create/Edit Form -->
        <div class="nb-card">
        <h4 class="mb-3" style="font-size: 18px;"><?= $editPost ? 'Edit Berita' : 'Buat Berita Baru' ?></h4>
        <form method="POST" enctype="multipart/form-data" id="post-form">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="action" value="<?= $editPost ? 'update' : 'create' ?>">
            <?php if ($editPost): ?>
                <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= $editPost['image'] ?>">
            <?php endif; ?>
            
            <div class="nb-form-group">
                <label class="nb-label" style="font-size: 13px;">Judul Berita *</label>
                <input type="text" name="title" class="nb-input" required value="<?= e($editPost['title'] ?? '') ?>" placeholder="Masukkan judul berita" style="font-size: 14px; padding: 10px 14px;">
            </div>
            
            <div class="nb-form-group">
                <label class="nb-label" style="font-size: 13px;">Konten *</label>
                <input type="hidden" name="content" id="content-hidden">
                <div id="quill-editor" style="background: white; border: 3px solid #000; border-radius: 12px; min-height: 250px;">
                    <?= $editPost ? $editPost['content'] : '' ?>
                </div>
                <small style="color: var(--gray-600); display: block; margin-top: 8px;">Gunakan toolbar untuk memformat teks</small>
            </div>
            
            <div class="nb-form-group">
                <label class="nb-label" style="font-size: 13px;">Gambar Cover</label>
                <?php if ($editPost && $editPost['image']): ?>
                    <div class="mb-2 existing-image-preview">
                        <img src="<?= upload($editPost['image']) ?>" style="max-width: 200px; border: 2px solid #000; border-radius: 8px; margin-bottom: 8px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="nb-input" accept="image/*" style="font-size: 13px; padding: 8px 12px;">
                <small style="color: var(--gray-600); font-size: 11px;">Max 5MB. Format: JPG, PNG, GIF</small>
            </div>
            
            <div class="nb-form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_published" value="1" <?= ($editPost && $editPost['is_published']) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                    <span style="font-weight: 600; font-size: 14px;">Publikasikan berita ini</span>
                </label>
                <small style="color: var(--gray-600); display: block; margin-top: 6px; font-size: 12px;">Jika tidak dicentang, berita akan disimpan sebagai draft</small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="nb-btn nb-btn-primary" style="font-size: 13px; padding: 10px 20px;">
                    <i class="bi bi-save"></i> <?= $editPost ? 'Update Berita' : 'Simpan Berita' ?>
                </button>
                <?php if ($editPost): ?>
                    <a href="javascript:void(0)" onclick="cancelEdit()" class="nb-btn nb-btn-outline cancel-edit-btn" style="font-size: 13px; padding: 10px 20px;">
                        <i class="bi bi-x"></i> Batal Edit
                    </a>
                <?php else: ?>
                    <button type="reset" class="nb-btn nb-btn-outline" style="font-size: 13px; padding: 10px 20px;">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset Form
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

        <!-- RIGHT COLUMN: All Posts with Pagination -->
        <div class="nb-card" style="position: sticky; top: 80px; max-height: calc(100vh - 100px); overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4>Semua Berita</h4>
                <a href="<?= APP_URL ?>/blog" target="_blank" class="nb-btn nb-btn-outline nb-btn-sm">
                    <i class="bi bi-eye"></i> Lihat di Website
                </a>
            </div>
        
        <!-- Posts List Container -->
        <div id="posts-list-container">
            <?php
            // Pagination for all posts section
            $allPostsPage = isset($_GET['posts_page']) ? (int) $_GET['posts_page'] : 1;
            $postsPerPage = 5;
            $offset = ($allPostsPage - 1) * $postsPerPage;
            
            // Count total posts
            $totalPostsStmt = $db->query("SELECT COUNT(*) FROM posts");
            $totalPosts = $totalPostsStmt->fetchColumn();
            $totalPages = ceil($totalPosts / $postsPerPage);
            
            // Get posts for current page
            $allPostsStmt = $db->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $allPostsStmt->execute([$postsPerPage, $offset]);
            $allPosts = $allPostsStmt->fetchAll();
            
            if (count($allPosts) > 0):
            ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($allPosts as $post): ?>
                        <div style="padding: 16px; border: 2px solid var(--gray-200); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; background: var(--white); transition: all 0.2s;">
                            <!-- Clickable Content Area for Edit -->
                            <div style="flex: 1; cursor: pointer;" 
                                 onclick="handleEditClick('<?= $post['id'] ?>', event)"
                                 onmouseover="this.parentElement.style.borderColor='var(--primary)'; this.parentElement.style.transform='translateX(4px)'"
                                 onmouseout="this.parentElement.style.borderColor='var(--gray-200)'; this.parentElement.style.transform='translateX(0)'">
                                <div style="font-weight: 700; margin-bottom: 4px; font-size: 15px;"><?= e($post['title']) ?></div>
                                <div style="font-size: 12px; color: var(--gray-600);">
                                    <?= formatDateIndo($post['created_at']) ?>
                                    <?php if ($post['is_published']): ?>
                                        <span class="nb-badge nb-badge-success" style="margin-left: 8px; font-size: 11px;">Published</span>
                                    <?php else: ?>
                                        <span class="nb-badge nb-badge-warning" style="margin-left: 8px; font-size: 11px;">Draft</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Action Buttons (Not Clickable for Edit) -->
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <!-- Toggle Switch -->
                                <label class="toggle-switch" title="<?= $post['is_published'] ? 'Published' : 'Draft' ?>">
                                    <input type="checkbox" <?= $post['is_published'] ? 'checked' : '' ?> 
                                           onchange="var isPublished = this.checked; var formData = new FormData(); formData.append('csrf_token', '<?= generateCsrfToken() ?>'); formData.append('action', 'toggle_publish'); formData.append('id', '<?= $post['id'] ?>'); formData.append('is_published', isPublished ? '1' : '0'); fetch('<?= APP_URL ?>/admin/posts', { method: 'POST', body: formData }).then(() => { setTimeout(() => window.location.reload(), 300); }).catch(() => { alert('Gagal mengubah status'); this.checked = !isPublished; });">
                                    <span class="toggle-slider"></span>
                                </label>
                                <!-- Delete Button -->
                                <button type="button" class="nb-btn nb-btn-sm nb-btn-danger" title="Hapus" style="padding: 8px 12px;"
                                    onclick="deletePost('<?= $post['id'] ?>', '<?= e($post['title']) ?>', event); return false;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination Controls -->
                <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--gray-200);">
                        <button onclick="loadPostsPage(<?= $allPostsPage - 1 ?>)" class="nb-btn nb-btn-outline nb-btn-sm" <?= $allPostsPage <= 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                            <i class="bi bi-chevron-left"></i> Sebelumnya
                        </button>
                        
                        <span style="font-weight: 700; color: var(--gray-700); font-size: 13px;" id="page-indicator">
                            Halaman <?= $allPostsPage ?> dari <?= $totalPages ?>
                        </span>
                        
                        <button onclick="loadPostsPage(<?= $allPostsPage + 1 ?>)" class="nb-btn nb-btn-outline nb-btn-sm" <?= $allPostsPage >= $totalPages ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
                            Selanjutnya <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 48px; color: var(--gray-500);">
                    <i class="bi bi-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>Belum ada berita</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    </div> <!-- End Two Column Layout -->
</div>

<!-- Quill.js Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<style>
/* Toggle Switch Styling */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 26px;
  cursor: pointer;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--gray-300);
  transition: 0.3s;
  border-radius: 26px;
  border: 2px solid var(--black);
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 2px;
  background-color: var(--white);
  transition: 0.3s;
  border-radius: 50%;
  border: 2px solid var(--black);
}

.toggle-switch input:checked + .toggle-slider {
  background-color: var(--success);
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(22px);
}

.toggle-switch:hover .toggle-slider {
  box-shadow: 0 0 0 2px var(--primary);
}

/* Custom Quill Styling for Neubrutalism */
#quill-editor {
    font-family: 'Inter', sans-serif;
}

.ql-toolbar.ql-snow {
    border: 3px solid #000 !important;
    border-bottom: none !important;
    border-radius: 12px 12px 0 0 !important;
    background: var(--gray-50) !important;
    padding: 12px !important;
}

.ql-container.ql-snow {
    border: 3px solid #000 !important;
    border-radius: 0 0 12px 12px !important;
    font-size: 16px !important;
    font-family: 'Inter', sans-serif !important;
}

.ql-editor {
    min-height: 300px;
    padding: 20px !important;
}

.ql-editor.ql-blank::before {
    color: var(--gray-400);
    font-style: normal;
}

/* Toolbar buttons */
.ql-snow .ql-stroke {
    stroke: var(--black) !important;
}

.ql-snow .ql-fill {
    fill: var(--black) !important;
}

.ql-snow .ql-picker-label {
    color: var(--black) !important;
    border: 2px solid var(--black) !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
}

.ql-snow.ql-toolbar button,
.ql-snow .ql-toolbar button {
    width: 32px !important;
    height: 32px !important;
    border: 2px solid transparent !important;
    border-radius: 6px !important;
    transition: all 0.2s !important;
}

.ql-snow.ql-toolbar button:hover,
.ql-snow .ql-toolbar button:hover,
.ql-snow.ql-toolbar button.ql-active,
.ql-snow .ql-toolbar button.ql-active {
    background: var(--primary) !important;
    border: 2px solid var(--black) !important;
}

.ql-snow .ql-picker-label:hover,
.ql-snow .ql-picker-label.ql-active {
    background: var(--primary) !important;
}
</style>

<script>
// ============================================
// GLOBAL FUNCTIONS (Available immediately)
// ============================================

// Delete Post
window.deletePost = function(postId, postTitle, event) {
    console.log('deletePost called', postId, postTitle);
    
    // Prevent any default behavior if event exists
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Show custom confirmation modal
    showConfirmation(
        'Hapus Berita?',
        `Apakah Anda yakin ingin menghapus berita "${postTitle}"? Tindakan ini tidak dapat dibatalkan.`,
        (confirmed) => {
            if (!confirmed) {
                return;
            }
            
            // Show loading toast
            showToast('Menghapus berita...', 'info');
            
            console.log('Sending delete request for post:', postId);
            
            // Create form data
            const formData = new FormData();
            formData.append('csrf_token', '<?= generateCsrfToken() ?>');
            formData.append('action', 'delete');
            formData.append('id', postId);
            
            // Submit delete request
            fetch('<?= APP_URL ?>/admin/posts', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Delete response:', response);
                return response.text();
            })
            .then(data => {
                console.log('Delete response data:', data);
                showToast('Berita berhasil dihapus', 'success');
                
                // Reload page after short delay
                formChanged = false; // Prevent beforeunload warning
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            })
            .catch(error => {
                console.error('Delete error:', error);
                showToast('Gagal menghapus berita. Silakan coba lagi.', 'danger');
            });
        }
    );
    
    return false;
}

// Toggle Publish Status
window.togglePublish = function(postId, isPublished) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= generateCsrfToken() ?>');
    formData.append('action', 'toggle_publish');
    formData.append('id', postId);
    formData.append('is_published', isPublished ? '1' : '0');
    
    fetch('<?= APP_URL ?>/admin/posts', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        // Show success message
        const message = isPublished ? 'Berita berhasil dipublikasikan' : 'Berita berhasil dijadikan draft';
        showToast(message, 'success');
        
        // Reload the page to update the list
        setTimeout(() => {
            window.location.reload();
        }, 500);
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Gagal mengubah status', 'danger');
        // Revert the toggle
        event.target.checked = !isPublished;
    });
}

// Simple toast notification
window.showToast = function(message, type) {
    const toast = document.createElement('div');
    toast.className = `nb-alert nb-alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '80px';
    toast.style.right = '24px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.style.animation = 'slideIn 0.3s ease-out';
    
    // Icon based on type
    let icon = 'check-circle';
    if (type === 'danger') icon = 'x-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    if (type === 'info') icon = 'info-circle';
    
    toast.innerHTML = `<i class="bi bi-${icon}"></i> ${message}`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================
// FORM TRACKING
// ============================================

// Track form changes
let formChanged = false;
let originalFormData = {};

// Initialize form tracking
function initFormTracking() {
    // Store original form data
    const form = document.getElementById('post-form');
    if (form) {
        const formData = new FormData(form);
        originalFormData = {
            title: form.querySelector('[name="title"]').value,
            content: quill.root.innerHTML,
            is_published: form.querySelector('[name="is_published"]')?.checked || false
        };
        
        // Track title changes
        form.querySelector('[name="title"]').addEventListener('input', function() {
            checkFormChanges();
        });
        
        // Track content changes
        quill.on('text-change', function() {
            checkFormChanges();
        });
        
        // Track checkbox changes
        const publishCheckbox = form.querySelector('[name="is_published"]');
        if (publishCheckbox) {
            publishCheckbox.addEventListener('change', function() {
                checkFormChanges();
            });
        }
    }
}

// Check if form has changes
function checkFormChanges() {
    const form = document.getElementById('post-form');
    if (!form) return false;
    
    const currentData = {
        title: form.querySelector('[name="title"]').value,
        content: quill.root.innerHTML,
        is_published: form.querySelector('[name="is_published"]')?.checked || false
    };
    
    // If form is in create mode (no ID) and still empty, no changes
    const formAction = form.querySelector('[name="action"]').value;
    if (formAction === 'create') {
        const isEmpty = !currentData.title && 
                       (currentData.content === '<p><br></p>' || currentData.content === '' || currentData.content === '<p></p>');
        if (isEmpty) {
            return false;
        }
    }
    
    // Check if any field has changed from original
    formChanged = (
        currentData.title !== originalFormData.title ||
        currentData.content !== originalFormData.content ||
        currentData.is_published !== originalFormData.is_published
    );
    
    return formChanged;
}

// Handle edit click
function handleEditClick(postId, event) {
    // Check if form has unsaved changes
    if (checkFormChanges()) {
        // Show confirmation dialog using custom modal
        showConfirmation(
            'Perubahan Belum Disimpan',
            'Form memiliki perubahan yang belum disimpan.\n\nApakah Anda ingin melanjutkan mengedit berita lain? Perubahan saat ini akan hilang.',
            (confirmed) => {
                if (confirmed) {
                    // User confirmed, load the new post
                    loadPostForEdit(postId);
                }
            },
            {
                confirmLabel: 'Ya, Lanjutkan',
                cancelLabel: 'Batal',
                confirmStyle: 'nb-btn-warning'
            }
        );
        return;
    }
    
    // No unsaved changes, load post data via AJAX
    loadPostForEdit(postId);
}

// Load post data and populate form
function loadPostForEdit(postId) {
    // Show loading state
    const form = document.getElementById('post-form');
    const formTitle = form.closest('.nb-card').querySelector('h4');
    const originalTitle = formTitle.textContent;
    formTitle.innerHTML = '<i class="bi bi-hourglass-split"></i> Memuat data...';
    form.style.opacity = '0.5';
    form.style.pointerEvents = 'none';
    
    // Fetch post data
    fetch('<?= APP_URL ?>/admin/posts?ajax=1&get_post=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const post = data.post;
                
                // Check if post is published - show confirmation
                if (post.is_published == 1) {
                    // Reset loading state first
                    formTitle.textContent = originalTitle;
                    form.style.opacity = '1';
                    form.style.pointerEvents = 'auto';
                    
                    // Show confirmation modal
                    showConfirmation(
                        'Edit Berita yang Sudah Dipublikasikan?',
                        'Berita ini sudah dipublikasikan dan dapat dilihat oleh pengunjung.\n\nApakah Anda yakin ingin mengedit berita ini?',
                        (confirmed) => {
                            if (confirmed) {
                                // User confirmed, proceed with loading
                                populateEditForm(post, form, formTitle);
                            }
                        },
                        {
                            confirmLabel: 'Ya, Edit',
                            cancelLabel: 'Batal',
                            confirmStyle: 'nb-btn-primary'
                        }
                    );
                    return;
                }
                
                // If not published, proceed directly
                populateEditForm(post, form, formTitle);
            } else {
                formTitle.textContent = originalTitle;
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
                showToast('Gagal memuat data berita', 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading post:', error);
            formTitle.textContent = originalTitle;
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
            showToast('Gagal memuat data berita', 'danger');
        });
}

// Populate form with post data
function populateEditForm(post, form, formTitle) {
    // Update form title
    formTitle.textContent = 'Edit Berita';
    
    // Update form action
    form.querySelector('[name="action"]').value = 'update';
    
    // Add/update post ID
    let idInput = form.querySelector('[name="id"]');
    if (!idInput) {
        idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        form.insertBefore(idInput, form.firstChild);
    }
    idInput.value = post.id;
    
    // Add/update existing image
    let existingImageInput = form.querySelector('[name="existing_image"]');
    if (!existingImageInput) {
        existingImageInput = document.createElement('input');
        existingImageInput.type = 'hidden';
        existingImageInput.name = 'existing_image';
        form.insertBefore(existingImageInput, form.firstChild);
    }
    existingImageInput.value = post.image || '';
    
    // Populate title
    form.querySelector('[name="title"]').value = post.title;
    
    // Populate content
    quill.root.innerHTML = post.content || '';
    
    // Populate publish checkbox
    const publishCheckbox = form.querySelector('[name="is_published"]');
    if (publishCheckbox) {
        publishCheckbox.checked = post.is_published == 1;
    }
    
    // Show existing image if any
    const imageContainer = form.querySelector('.nb-form-group:has([name="image"])');
    let existingImageDiv = imageContainer.querySelector('.existing-image-preview');
    if (post.image) {
        if (!existingImageDiv) {
            existingImageDiv = document.createElement('div');
            existingImageDiv.className = 'existing-image-preview mb-2';
            imageContainer.insertBefore(existingImageDiv, imageContainer.querySelector('[name="image"]'));
        }
        existingImageDiv.innerHTML = '<img src="<?= APP_URL ?>/uploads/' + post.image + '" style="max-width: 200px; border: 2px solid #000; border-radius: 8px; margin-bottom: 8px;">';
    } else if (existingImageDiv) {
        existingImageDiv.remove();
    }
    
    // Update submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="bi bi-save"></i> Update Berita';
    
    // Show cancel button
    let cancelBtn = form.querySelector('.cancel-edit-btn');
    if (!cancelBtn) {
        cancelBtn = document.createElement('a');
        cancelBtn.href = 'javascript:void(0)';
        cancelBtn.className = 'nb-btn nb-btn-outline cancel-edit-btn';
        cancelBtn.style.fontSize = '13px';
        cancelBtn.style.padding = '10px 20px';
        cancelBtn.innerHTML = '<i class="bi bi-x"></i> Batal Edit';
        cancelBtn.onclick = cancelEdit;
        submitBtn.parentElement.appendChild(cancelBtn);
    }
    
    // Reset form tracking with new data
    originalFormData = {
        title: post.title,
        content: post.content || '',
        is_published: post.is_published == 1
    };
    formChanged = false;
    
    // Smooth fade in
    form.style.transition = 'opacity 0.3s';
    form.style.opacity = '1';
    form.style.pointerEvents = 'auto';
    
    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    // Focus on title
    form.querySelector('[name="title"]').focus();
}

// Cancel edit and reset form
function cancelEdit() {
    if (checkFormChanges()) {
        const confirmed = confirm('Perubahan akan hilang. Yakin ingin membatalkan edit?');
        if (!confirmed) return;
    }
    
    const form = document.getElementById('post-form');
    
    // Reset form
    form.reset();
    quill.setContents([]);
    
    // Update form title
    const formTitle = form.closest('.nb-card').querySelector('h4');
    formTitle.textContent = 'Buat Berita Baru';
    
    // Update form action
    form.querySelector('[name="action"]').value = 'create';
    
    // Remove post ID and existing image
    const idInput = form.querySelector('[name="id"]');
    if (idInput) idInput.remove();
    
    const existingImageInput = form.querySelector('[name="existing_image"]');
    if (existingImageInput) existingImageInput.remove();
    
    // Remove existing image preview
    const existingImageDiv = form.querySelector('.existing-image-preview');
    if (existingImageDiv) existingImageDiv.remove();
    
    // Update submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="bi bi-save"></i> Simpan Berita';
    
    // Remove cancel button
    const cancelBtn = form.querySelector('.cancel-edit-btn');
    if (cancelBtn) cancelBtn.remove();
    
    // Add reset button if not exists
    let resetBtn = form.querySelector('button[type="reset"]');
    if (!resetBtn) {
        resetBtn = document.createElement('button');
        resetBtn.type = 'reset';
        resetBtn.className = 'nb-btn nb-btn-outline';
        resetBtn.style.fontSize = '13px';
        resetBtn.style.padding = '10px 20px';
        resetBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Reset Form';
        submitBtn.parentElement.appendChild(resetBtn);
    }
    
    // Reset form tracking
    originalFormData = {
        title: '',
        content: '',
        is_published: false
    };
    formChanged = false;
}

// ============================================
// QUILL EDITOR INITIALIZATION
// ============================================

var quill; // Global variable

function initQuillEditor() {
    // Only initialize if not already initialized
    if (quill) {
        return;
    }
    
    // Initialize Quill editor
    quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                ['link'],                                          // link
                [{ 'header': [2, 3, false] }],                    // custom button values
                [{ 'align': [] }],                                 // text align
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],     // lists
                ['blockquote', 'code-block'],                      // blocks
                [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                ['clean']                                          // remove formatting button
            ]
        },
        placeholder: 'Tulis konten berita di sini...'
    });

    // Load existing content if editing
    <?php if ($editPost && !empty($editPost['content'])): ?>
    var existingContent = <?= json_encode($editPost['content']) ?>;
    // Check if content is HTML or plain text
    if (existingContent.indexOf('<') !== -1) {
        // HTML content
        quill.root.innerHTML = existingContent;
    } else {
        // Plain text - convert to paragraphs
        quill.setText(existingContent);
    }
    <?php endif; ?>

    // Sync Quill content to hidden input on form submit
    var form = document.getElementById('post-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submission first
            
            var content = quill.root.innerHTML;
            
            // Remove empty paragraphs
            if (content === '<p><br></p>' || content === '<p></p>') {
                content = '';
            }
            
            // Validate content is not empty
            var textContent = quill.getText().trim();
            if (!textContent || textContent === '') {
                alert('Konten berita tidak boleh kosong');
                return false;
            }
            
            // Set the hidden input value
            document.getElementById('content-hidden').value = content;
            
            // Debug: log content
            console.log('Submitting content:', content);
            console.log('Hidden input value:', document.getElementById('content-hidden').value);
            console.log('Content length:', content.length);
            
            // Now submit the form
            form.submit();
        });
    }

    // Update hidden input on text change (real-time sync)
    quill.on('text-change', function() {
        var content = quill.root.innerHTML;
        if (content === '<p><br></p>' || content === '<p></p>') {
            content = '';
        }
        document.getElementById('content-hidden').value = content;
    });

    // Warn user before leaving page with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (checkFormChanges()) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });

    // Reset form changed flag on successful submit
    if (form) {
        form.addEventListener('submit', function() {
            formChanged = false; // Allow navigation after submit
        });
    }

    // Handle reset button
    var resetBtn = document.querySelector('button[type="reset"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            setTimeout(function() {
                quill.setContents([]);
                document.getElementById('content-hidden').value = '';
            }, 10);
        });
    }
}
</script>

<script>
// ============================================
// AJAX PAGINATION
// ============================================

// AJAX Pagination for Posts List
function loadPostsPage(page) {
    const container = document.getElementById('posts-list-container');
    
    // Add loading state
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';
    
    // Fetch posts for the requested page
    fetch('<?= APP_URL ?>/admin/posts?ajax=1&posts_page=' + page)
        .then(response => response.text())
        .then(html => {
            // Parse the HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('posts-list-container');
            
            if (newContent) {
                // Smooth fade out
                container.style.transition = 'opacity 0.2s';
                container.style.opacity = '0';
                
                setTimeout(() => {
                    // Replace content
                    container.innerHTML = newContent.innerHTML;
                    
                    // No need to re-attach listeners - event delegation handles it!
                    
                    // Smooth fade in
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    
                    // Scroll to posts section smoothly
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 200);
            }
        })
        .catch(error => {
            console.error('Error loading posts:', error);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            alert('Gagal memuat data. Silakan coba lagi.');
        });
}

// ============================================
// DELETE POST HANDLER
// ============================================

// ============================================
// INITIALIZE ON PAGE LOAD
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Posts page loaded');
    
    // Initialize Quill editor if it exists
    if (document.getElementById('quill-editor')) {
        initQuillEditor();
    }
    
    // Initialize form tracking
    initFormTracking();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
