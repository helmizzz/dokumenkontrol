<?php
require_once __DIR__ . '/../core/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';
check_auth();

// Fetch filter options
$years = $pdo->query("SELECT * FROM years ORDER BY year_value DESC")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Handle search and filter
$where_clauses = ["d.status = 1"];
$params = [];

if (!empty($_GET['search'])) {
    $where_clauses[] = "(doc_number LIKE :search OR title LIKE :search)";
    $params[':search'] = '%' . trim($_GET['search']) . '%';
}
if (!empty($_GET['year'])) {
    $where_clauses[] = "year_id = :year";
    $params[':year'] = $_GET['year'];
}
if (!empty($_GET['month'])) {
    $where_clauses[] = "month_value = :month";
    $params[':month'] = $_GET['month'];
}

// Department filter logic
if ($_SESSION['role_name'] === 'Superadmin') {
    if (!empty($_GET['dept'])) {
        $where_clauses[] = "dept_id = :dept";
        $params[':dept'] = $_GET['dept'];
    }
} else {
    // Admin & User only see their department's docs, PLUS public docs from other departments
    // Wait, requirement: "Filter Dropdown Departemen: ... otomatis terkunci pada departemen mereka sendiri atau disembunyikan"
    // To keep it simple, we hide the filter and apply strict WHERE clause.
    // Also need to consider Document Access for private files!
    // But for the repository view, let's just use a base query. 
    // If not superadmin, they can only view documents from their department OR public documents OR documents they have explicit access to.
}

if (!empty($_GET['access_type'])) {
    if ($_GET['access_type'] === 'public') {
        $where_clauses[] = "is_public = 1";
    } elseif ($_GET['access_type'] === 'private') {
        $where_clauses[] = "is_public = 0";
    }
}

// Construct Query
$sql = "SELECT d.*, y.year_value, dept.dept_name 
        FROM documents d 
        LEFT JOIN years y ON d.year_id = y.id 
        LEFT JOIN departments dept ON d.dept_id = dept.id";

// Role-based visibility
if ($_SESSION['role_name'] !== 'Superadmin') {
    $user_id = $_SESSION['user_id'];
    $dept_id = $_SESSION['dept_id'];
    $sql .= " LEFT JOIN document_access da ON (da.document_id = d.id AND da.user_id = $user_id)
              LEFT JOIN document_dept_access dda ON (dda.document_id = d.id AND dda.dept_id = $dept_id)
              WHERE (d.is_public = 1 OR d.dept_id = $dept_id OR da.id IS NOT NULL OR dda.id IS NOT NULL) 
              AND " . implode(' AND ', $where_clauses);
} else {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<h1 class="page-title">Repositori Dokumen</h1>

<!-- Filter Panel -->
<form method="GET" action="documents.php" class="filter-panel">
    <div class="form-group">
        <label for="search">Cari Dokumen</label>
        <input type="text" id="search" name="search" class="form-control" placeholder="Nomor / Judul" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="year">Tahun</label>
        <select id="year" name="year" class="form-control">
            <option value="">Semua Tahun</option>
            <?php foreach ($years as $yr): ?>
                <option value="<?php echo $yr['id']; ?>" <?php echo (($_GET['year'] ?? '') == $yr['id']) ? 'selected' : ''; ?>><?php echo $yr['year_value']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="month">Bulan</label>
        <select id="month" name="month" class="form-control">
            <option value="">Semua Bulan</option>
            <?php foreach ($months as $num => $name): ?>
                <option value="<?php echo $num; ?>" <?php echo (($_GET['month'] ?? '') == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($_SESSION['role_name'] === 'Superadmin'): ?>
    <div class="form-group">
        <label for="dept">Departemen</label>
        <select id="dept" name="dept" class="form-control">
            <option value="">Semua Departemen</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['id']; ?>" <?php echo (($_GET['dept'] ?? '') == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['dept_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="access_type">Sifat Dokumen</label>
        <select id="access_type" name="access_type" class="form-control">
            <option value="">Semua Sifat</option>
            <option value="public" <?php echo (($_GET['access_type'] ?? '') == 'public') ? 'selected' : ''; ?>>Public</option>
            <option value="private" <?php echo (($_GET['access_type'] ?? '') == 'private') ? 'selected' : ''; ?>>Private</option>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn-primary"><i class='bx bx-search'></i> Filter</button>
    </div>
</form>

<!-- Data Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nomor Dokumen</th>
                <th>Judul Dokumen</th>
                <th>Departemen Asal</th>
                <th>Terbit</th>
                <th>Sifat Akses</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($documents) > 0): ?>
                <?php $no = 1; foreach ($documents as $doc): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong>
                        <?php 
                        $disp_doc = $doc['doc_number'];
                        if (isset($doc['revision_number']) && $doc['revision_number'] > 0) {
                            $disp_doc .= '-' . $doc['revision_number'];
                        }
                        echo htmlspecialchars($disp_doc); 
                        ?>
                    </strong></td>
                    <td><?php echo htmlspecialchars($doc['title']); ?></td>
                    <td><?php echo htmlspecialchars($doc['dept_name'] ?? '-'); ?></td>
                    <td><?php echo isset($doc['month_value']) ? $months[$doc['month_value']] . ' ' . $doc['year_value'] : '-'; ?></td>
                    <td>
                        <?php if ($doc['is_public'] == 1): ?>
                            <span class="badge badge-public">🟢 Public</span>
                        <?php else: ?>
                            <span class="badge badge-private">🔴 Private</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-icon" onclick="openDocumentViewer(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" title="View Dokumen">
                                <i class='bx bx-show'></i>
                            </button>
                            
                            <?php 
                                $can_delete = ($_SESSION['role_name'] === 'Superadmin' || $_SESSION['role_name'] === 'Admin' || $_SESSION['user_id'] == $doc['uploaded_by']);
                            ?>
                            <?php if ($_SESSION['role_name'] === 'Superadmin' || $_SESSION['role_name'] === 'Admin'): ?>
                                <button type="button" class="btn-icon" onclick="openHistoryModal(<?php echo $doc['id']; ?>)" title="History Dokumen"><i class='bx bx-history'></i></button>
                                <button type="button" class="btn-icon" onclick="openShareModal(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" title="Share Link"><i class='bx bx-share-alt'></i></button>
                                <a href="metadata.php?id=<?php echo $doc['id']; ?>" class="btn-icon" title="Metadata (Log Aktivitas)"><i class='bx bx-info-circle'></i></a>
                                <a href="edit_document.php?doc_id=<?php echo $doc['id']; ?>" class="btn-icon" title="Edit Dokumen"><i class='bx bx-edit'></i></a>
                            <?php endif; ?>
                            <?php if ($can_delete): ?>
                                <button type="button" class="btn-icon" style="color: #ef4444;" onclick="softDeleteDocument(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" title="Pindah ke Recycle Bin"><i class='bx bx-trash'></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada dokumen yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- History Modal -->
<div class="modal-overlay" id="historyModal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <div class="modal-title">History Revisi Dokumen</div>
            <button class="btn-close" onclick="closeHistoryModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" id="historyContainer" style="padding: 20px; max-height: 60vh; overflow-y: auto;">
            <div style="text-align: center;">Memuat data history...</div>
        </div>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div class="modal-overlay" id="pdfViewerModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title" id="pdfViewerTitle">Document Viewer</div>
            <button class="btn-close" onclick="closeDocumentViewer()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" id="pdfFrameContainer">
            <!-- Object element will be injected here via JS -->
        </div>
    </div>
</div>

<script>
function softDeleteDocument(fileId, title) {
    if (confirm('Anda yakin ingin menghapus sementara dokumen "' + title + '"?\n\nDokumen akan dipindah ke Recycle Bin.')) {
        const formData = new FormData();
        formData.append('file_id', fileId);
        
        fetch('../modules/document/soft_delete_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.trim() === 'success') {
                alert('Dokumen berhasil dipindah ke Recycle Bin!');
                window.location.reload();
            } else {
                alert(data);
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
        });
    }
}

function openDocumentViewer(fileId, title) {
    const modal = document.getElementById('pdfViewerModal');
    const iframeContainer = document.getElementById('pdfFrameContainer');
    const titleEl = document.getElementById('pdfViewerTitle');
    
    titleEl.textContent = title;
    const streamUrl = `view_stream.php?file_id=${fileId}`;
    
    iframeContainer.innerHTML = `
    <div style="position: relative; width: 100%; height: 100%; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; z-index: 9999; opacity: 0.15;">
            <div style="transform: rotate(-45deg); font-size: 5rem; font-weight: bold; color: #000; text-align: center; white-space: nowrap;">
                DOKUMEN KONTROL<br><span style="font-size: 2rem;">INTERNAL USE ONLY</span>
            </div>
        </div>
        <object data="${streamUrl}" type="application/pdf" style="width: 100%; height: 100%; border: none;">
            <div style="padding: 20px; text-align: center;">Browser Anda tidak mendukung penampil PDF internal. Silakan hubungi admin.</div>
        </object>
    </div>`;
    
    modal.classList.add('active');
}

function closeDocumentViewer() {
    const modal = document.getElementById('pdfViewerModal');
    const iframeContainer = document.getElementById('pdfFrameContainer');
    iframeContainer.innerHTML = ''; // Hapus object untuk stop loading PDF
    modal.classList.remove('active');
}

// --- History Logic ---
function openHistoryModal(docId) {
    const modal = document.getElementById('historyModal');
    const container = document.getElementById('historyContainer');
    modal.classList.add('active');
    container.innerHTML = '<div style="text-align: center;">Memuat data history...</div>';
    
    fetch('../modules/document/get_history.php?doc_id=' + docId)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = '<div style="color:red; text-align:center;">Gagal memuat history.</div>';
        });
}

function closeHistoryModal() {
    document.getElementById('historyModal').classList.remove('active');
}

function restoreHistory(revId, revNumber) {
    if (confirm('Anda yakin ingin merestore file ini ke Revisi ' + revNumber + '?\n\nFile aktif saat ini akan digeser menjadi history.')) {
        const formData = new FormData();
        formData.append('rev_id', revId);
        
        fetch('../modules/document/restore_proc.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if(data.trim() === 'success') {
                alert('Restore berhasil!');
                window.location.reload();
            } else {
                alert(data);
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
        });
    }
}

// --- Share Link Logic ---
function openShareModal(docId, title) {
    document.getElementById('shareDocId').value = docId;
    document.getElementById('shareDocTitle').textContent = title;
    document.getElementById('sharePassword').value = '';
    document.getElementById('shareExpiry').value = '';
    document.getElementById('shareResultArea').style.display = 'none';
    document.getElementById('btnBuatLink').disabled = false;
    document.getElementById('btnBuatLink').textContent = 'Buat Link';
    document.getElementById('shareLinkModal').classList.add('active');
}

function closeShareModal() {
    document.getElementById('shareLinkModal').classList.remove('active');
}

async function generateShareLink(e) {
    e.preventDefault();
    const btn = document.getElementById('btnBuatLink');
    btn.disabled = true;
    btn.textContent = 'Membuat...';
    
    const formData = new FormData(document.getElementById('shareForm'));
    
    try {
        const response = await fetch('../modules/document/share_proc.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('generatedLink').value = result.link;
            document.getElementById('shareResultArea').style.display = 'block';
            btn.textContent = 'Link Dibuat';
        } else {
            alert(result.message || 'Terjadi kesalahan');
            btn.disabled = false;
            btn.textContent = 'Buat Link';
        }
    } catch (err) {
        alert('Gagal terhubung ke server');
        btn.disabled = false;
        btn.textContent = 'Buat Link';
    }
}
</script>

<!-- Share Link Modal -->
<div class="modal-overlay" id="shareLinkModal">
    <div class="modal-content" style="max-width: 500px; height: auto;">
        <div class="modal-header">
            <div class="modal-title">Create Share Link</div>
            <button class="btn-close" onclick="closeShareModal()"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" style="padding: 20px; background: #fff;">
            <p style="margin-bottom: 15px; color: #4b5563;">Dokumen: <strong id="shareDocTitle"></strong></p>
            
            <div id="shareResultArea" style="display: none; background: #d1fae5; border: 1px solid #a7f3d0; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <p style="color: #065f46; font-weight: 500; margin-bottom: 10px;">Link Berhasil Dibuat:</p>
                <input type="text" id="generatedLink" class="form-control" readonly onclick="this.select()" style="background: #fff; cursor: pointer; border-color: #6ee7b7;">
            </div>

            <form id="shareForm" onsubmit="generateShareLink(event)">
                <input type="hidden" id="shareDocId" name="doc_id">
                
                <div class="form-group">
                    <label for="sharePassword">Password (Opsional)</label>
                    <input type="password" id="sharePassword" name="password" class="form-control" placeholder="...">
                </div>
                
                <div class="form-group">
                    <label for="shareExpiry">Kadaluarsa (Opsional)</label>
                    <input type="datetime-local" id="shareExpiry" name="expires_at" class="form-control">
                </div>
                
                <div style="text-align: right; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn-secondary" onclick="closeShareModal()" style="margin-right: 10px;">Tutup</button>
                    <button type="submit" class="btn-primary" id="btnBuatLink">Buat Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
