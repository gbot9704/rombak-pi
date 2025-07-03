<?php
// pages/overhead_management.php
// Halaman manajemen biaya overhead dan tenaga kerja

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Pesan sukses atau error setelah proses
$message = '';
$message_type = '';
if (isset($_SESSION['overhead_message'])) {
    $message = $_SESSION['overhead_message']['text'];
    $message_type = $_SESSION['overhead_message']['type'];
    unset($_SESSION['overhead_message']);
}

// Handle AJAX requests
$isAjax = isset($_GET['ajax']);

// Initialize variables for overhead
$overhead_costs = [];
$search_overhead = $_GET['search_overhead'] ?? '';
$limit_overhead = isset($_GET['limit_overhead']) && in_array((int)$_GET['limit_overhead'], [5, 10, 25, 50]) ? (int)$_GET['limit_overhead'] : 10;
$page_overhead = isset($_GET['page_overhead']) ? max((int)$_GET['page_overhead'], 1) : 1;
$offset_overhead = ($page_overhead - 1) * $limit_overhead;
$total_overhead = 0;
$total_pages_overhead = 1;

// Initialize variables for labor
$labor_costs = [];
$search_labor = $_GET['search_labor'] ?? '';
$limit_labor = isset($_GET['limit_labor']) && in_array((int)$_GET['limit_labor'], [5, 10, 25, 50]) ? (int)$_GET['limit_labor'] : 10;
$page_labor = isset($_GET['page_labor']) ? max((int)$_GET['page_labor'], 1) : 1;
$offset_labor = ($page_labor - 1) * $limit_labor;
$total_labor = 0;
$total_pages_labor = 1;

try {
    $conn = $db;

    // Fetch overhead costs with search and pagination
    $where_overhead = "WHERE is_active = 1";
    $params_overhead = [];
    
    if (!empty($search_overhead)) {
        $where_overhead .= " AND name LIKE :search_overhead";
        $params_overhead[':search_overhead'] = '%' . $search_overhead . '%';
    }

    // Count total overhead
    $count_query_overhead = "SELECT COUNT(*) FROM overhead_costs " . $where_overhead;
    $count_stmt_overhead = $conn->prepare($count_query_overhead);
    foreach ($params_overhead as $key => $value) {
        $count_stmt_overhead->bindValue($key, $value);
    }
    $count_stmt_overhead->execute();
    $total_overhead = $count_stmt_overhead->fetchColumn();
    $total_pages_overhead = ceil($total_overhead / $limit_overhead);

    // Fetch overhead data
    $query_overhead = "SELECT * FROM overhead_costs " . $where_overhead . " ORDER BY name ASC LIMIT :limit OFFSET :offset";
    $stmt_overhead = $conn->prepare($query_overhead);
    foreach ($params_overhead as $key => $value) {
        $stmt_overhead->bindValue($key, $value);
    }
    $stmt_overhead->bindValue(':limit', $limit_overhead, PDO::PARAM_INT);
    $stmt_overhead->bindValue(':offset', $offset_overhead, PDO::PARAM_INT);
    $stmt_overhead->execute();
    $overhead_costs = $stmt_overhead->fetchAll(PDO::FETCH_ASSOC);

    // Fetch labor costs with search and pagination
    $where_labor = "WHERE is_active = 1";
    $params_labor = [];
    
    if (!empty($search_labor)) {
        $where_labor .= " AND position_name LIKE :search_labor";
        $params_labor[':search_labor'] = '%' . $search_labor . '%';
    }

    // Count total labor
    $count_query_labor = "SELECT COUNT(*) FROM labor_costs " . $where_labor;
    $count_stmt_labor = $conn->prepare($count_query_labor);
    foreach ($params_labor as $key => $value) {
        $count_stmt_labor->bindValue($key, $value);
    }
    $count_stmt_labor->execute();
    $total_labor = $count_stmt_labor->fetchColumn();
    $total_pages_labor = ceil($total_labor / $limit_labor);

    // Fetch labor data
    $query_labor = "SELECT * FROM labor_costs " . $where_labor . " ORDER BY position_name ASC LIMIT :limit OFFSET :offset";
    $stmt_labor = $conn->prepare($query_labor);
    foreach ($params_labor as $key => $value) {
        $stmt_labor->bindValue($key, $value);
    }
    $stmt_labor->bindValue(':limit', $limit_labor, PDO::PARAM_INT);
    $stmt_labor->bindValue(':offset', $offset_labor, PDO::PARAM_INT);
    $stmt_labor->execute();
    $labor_costs = $stmt_labor->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error di Overhead Management: " . $e->getMessage());
    $message = "Terjadi kesalahan saat memuat data.";
    $message_type = "error";
}

// Handle AJAX requests for overhead
if ($isAjax && $_GET['ajax'] === 'overhead') {
    ?>
    <div class="space-y-4">
        <?php if (!empty($overhead_costs)): ?>
            <?php foreach ($overhead_costs as $overhead): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($overhead['name']); ?></h4>
                            <?php if (!empty($overhead['description'])): ?>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($overhead['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold text-blue-600">Rp <?php echo number_format($overhead['amount'], 0, ',', '.'); ?></span>
                            <p class="text-xs text-gray-500">per bulan</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button onclick="editOverhead(<?php echo htmlspecialchars(json_encode($overhead)); ?>)" 
                                class="inline-flex items-center px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-600 border border-blue-300 rounded-lg transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteOverhead(<?php echo $overhead['id']; ?>, '<?php echo htmlspecialchars($overhead['name']); ?>')" 
                                class="inline-flex items-center px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-600 border border-red-300 rounded-lg transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p class="text-lg font-medium">Belum ada biaya overhead</p>
                <p class="text-sm">Tambahkan biaya overhead pertama Anda di atas</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination for overhead -->
    <?php if ($total_pages_overhead > 1): ?>
        <div class="flex justify-center items-center space-x-2 mt-6">
            <?php if ($page_overhead > 1): ?>
                <button onclick="loadOverheadData(<?php echo $page_overhead - 1; ?>)" 
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Prev
                </button>
            <?php endif; ?>

            <?php 
            $start_page = max(1, $page_overhead - 2);
            $end_page = min($total_pages_overhead, $page_overhead + 2);
            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <button onclick="loadOverheadData(<?php echo $i; ?>)" 
                        class="px-3 py-2 text-sm <?php echo $i == $page_overhead ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>

            <?php if ($page_overhead < $total_pages_overhead): ?>
                <button onclick="loadOverheadData(<?php echo $page_overhead + 1; ?>)" 
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Next
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php
    exit;
}

// Handle AJAX requests for labor
if ($isAjax && $_GET['ajax'] === 'labor') {
    ?>
    <div class="space-y-4">
        <?php if (!empty($labor_costs)): ?>
            <?php foreach ($labor_costs as $labor): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($labor['position_name']); ?></h4>
                            <p class="text-sm text-gray-600">Posisi/Jabatan</p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold text-green-600">Rp <?php echo number_format($labor['hourly_rate'], 0, ',', '.'); ?></span>
                            <p class="text-xs text-gray-500">per jam</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button onclick="editLabor(<?php echo htmlspecialchars(json_encode($labor)); ?>)" 
                                class="inline-flex items-center px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-600 border border-blue-300 rounded-lg transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteLabor(<?php echo $labor['id']; ?>, '<?php echo htmlspecialchars($labor['position_name']); ?>')" 
                                class="inline-flex items-center px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-600 border border-red-300 rounded-lg transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-lg font-medium">Belum ada posisi tenaga kerja</p>
                <p class="text-sm">Tambahkan posisi tenaga kerja pertama Anda di atas</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination for labor -->
    <?php if ($total_pages_labor > 1): ?>
        <div class="flex justify-center items-center space-x-2 mt-6">
            <?php if ($page_labor > 1): ?>
                <button onclick="loadLaborData(<?php echo $page_labor - 1; ?>)" 
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Prev
                </button>
            <?php endif; ?>

            <?php 
            $start_page = max(1, $page_labor - 2);
            $end_page = min($total_pages_labor, $page_labor + 2);
            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <button onclick="loadLaborData(<?php echo $i; ?>)" 
                        class="px-3 py-2 text-sm <?php echo $i == $page_labor ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>

            <?php if ($page_labor < $total_pages_labor): ?>
                <button onclick="loadLaborData(<?php echo $page_labor + 1; ?>)" 
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Next
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php
    exit;
}
?>

<?php include_once __DIR__ . '/../includes/header.php'; ?>
<div class="flex h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-sans">
    <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Manajemen Biaya Overhead & Tenaga Kerja</h1>
                    <p class="text-gray-600">Kelola biaya overhead dan upah tenaga kerja untuk perhitungan HPP yang akurat</p>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg border-l-4 <?php echo ($message_type == 'success' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700'); ?>" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <?php if ($message_type == 'success'): ?>
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                <?php else: ?>
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Column 1: Overhead Forms and Lists -->
                    <div class="space-y-6">
                        <!-- Form Tambah Biaya Overhead -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center mb-6">
                                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Biaya Overhead</h2>
                                    <p class="text-sm text-gray-600 mt-1">Kelola semua biaya overhead</p>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4" id="overhead_form_title">Tambah Biaya Overhead Baru</h3>
                                <form action="/cornerbites-sia/process/simpan_overhead.php" method="POST">
                                    <input type="hidden" name="type" value="overhead">
                                    <input type="hidden" name="overhead_id" id="overhead_id_to_edit">
                                    
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label for="overhead_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Biaya</label>
                                            <input type="text" id="overhead_name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Listrik, Sewa Tempat" required>
                                        </div>
                                        
                                        <div>
                                            <label for="overhead_amount" class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp)</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-sm font-medium">Rp</span>
                                                </div>
                                                <input type="text" id="overhead_amount" name="amount" class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="500.000" required>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label for="overhead_description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                                            <textarea id="overhead_description" name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Deskripsi tambahan..."></textarea>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 mt-4">
                                        <button type="submit" id="overhead_submit_button" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Tambah Overhead
                                        </button>
                                        <button type="button" id="overhead_cancel_edit_button" class="hidden inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors" onclick="resetOverheadForm()">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Batal Edit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Daftar Biaya Overhead -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Daftar Biaya Overhead
                                </h3>
                                <span class="text-sm text-gray-600">Total: <span class="font-semibold text-blue-600"><?php echo $total_overhead; ?> overhead</span></span>
                            </div>

                            <!-- Search and Filter -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pencarian</label>
                                        <div class="relative">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            <input type="text" id="search-overhead-input" value="<?php echo htmlspecialchars($search_overhead); ?>" placeholder="Cari nama overhead..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Per Halaman</label>
                                        <select id="limit-overhead-select" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="5" <?php echo $limit_overhead == 5 ? 'selected' : ''; ?>>5</option>
                                            <option value="10" <?php echo $limit_overhead == 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo $limit_overhead == 25 ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo $limit_overhead == 50 ? 'selected' : ''; ?>>50</option>
                                        </select>
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <button id="filter-overhead-btn" class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                            </svg>
                                            Filter
                                        </button>
                                        <button id="reset-overhead-btn" class="inline-flex items-center px-3 py-2 text-sm bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="overhead-container">
                                <div class="space-y-4">
                                    <?php if (!empty($overhead_costs)): ?>
                                        <?php foreach ($overhead_costs as $overhead): ?>
                                            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($overhead['name']); ?></h4>
                                                        <?php if (!empty($overhead['description'])): ?>
                                                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($overhead['description']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-lg font-bold text-blue-600">Rp <?php echo number_format($overhead['amount'], 0, ',', '.'); ?></span>
                                                        <p class="text-xs text-gray-500">per bulan</p>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end space-x-2">
                                                    <button onclick="editOverhead(<?php echo htmlspecialchars(json_encode($overhead)); ?>)" 
                                                            class="inline-flex items-center px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-600 border border-blue-300 rounded-lg transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Edit
                                                    </button>
                                                    <button onclick="deleteOverhead(<?php echo $overhead['id']; ?>, '<?php echo htmlspecialchars($overhead['name']); ?>')" 
                                                            class="inline-flex items-center px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-600 border border-red-300 rounded-lg transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-12 text-gray-500">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <p class="text-lg font-medium">Belum ada biaya overhead</p>
                                            <p class="text-sm">Tambahkan biaya overhead pertama Anda di atas</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination for overhead -->
                                <?php if ($total_pages_overhead > 1): ?>
                                    <div class="flex justify-center items-center space-x-2 mt-6">
                                        <?php if ($page_overhead > 1): ?>
                                            <button onclick="loadOverheadData(<?php echo $page_overhead - 1; ?>)" 
                                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                                Prev
                                            </button>
                                        <?php endif; ?>

                                        <?php 
                                        $start_page = max(1, $page_overhead - 2);
                                        $end_page = min($total_pages_overhead, $page_overhead + 2);
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <button onclick="loadOverheadData(<?php echo $i; ?>)" 
                                                    class="px-3 py-2 text-sm <?php echo $i == $page_overhead ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                                                <?php echo $i; ?>
                                            </button>
                                        <?php endfor; ?>

                                        <?php if ($page_overhead < $total_pages_overhead): ?>
                                            <button onclick="loadOverheadData(<?php echo $page_overhead + 1; ?>)" 
                                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                                Next
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Labor Forms and Lists -->
                    <div class="space-y-6">
                        <!-- Form Tambah Upah Tenaga Kerja -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center mb-6">
                                <div class="p-2 bg-green-100 rounded-lg mr-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Upah Tenaga Kerja</h2>
                                    <p class="text-sm text-gray-600 mt-1">Kelola posisi dan upah tenaga kerja</p>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4" id="labor_form_title">Tambah Posisi Tenaga Kerja Baru</h3>
                                <form action="/cornerbites-sia/process/simpan_overhead.php" method="POST">
                                    <input type="hidden" name="type" value="labor">
                                    <input type="hidden" name="labor_id" id="labor_id_to_edit">
                                    
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label for="labor_position_name" class="block text-sm font-medium text-gray-700 mb-2">Posisi/Jabatan</label>
                                            <input type="text" id="labor_position_name" name="position_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Koki, Kasir, Helper" required>
                                        </div>
                                        
                                        <div>
                                            <label for="labor_hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">Upah per Jam (Rp)</label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-sm font-medium">Rp</span>
                                                </div>
                                                <input type="text" id="labor_hourly_rate" name="hourly_rate" class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="15.000" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 mt-4">
                                        <button type="submit" id="labor_submit_button" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Tambah Posisi
                                        </button>
                                        <button type="button" id="labor_cancel_edit_button" class="hidden inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors" onclick="resetLaborForm()">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Batal Edit
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Daftar Posisi Tenaga Kerja -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Daftar Posisi Tenaga Kerja
                                </h3>
                                <span class="text-sm text-gray-600">Total: <span class="font-semibold text-green-600"><?php echo $total_labor; ?> posisi</span></span>
                            </div>

                            <!-- Search and Filter -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Pencarian</label>
                                        <div class="relative">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            <input type="text" id="search-labor-input" value="<?php echo htmlspecialchars($search_labor); ?>" placeholder="Cari posisi tenaga kerja..." class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Per Halaman</label>
                                        <select id="limit-labor-select" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="5" <?php echo $limit_labor == 5 ? 'selected' : ''; ?>>5</option>
                                            <option value="10" <?php echo $limit_labor == 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo $limit_labor == 25 ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo $limit_labor == 50 ? 'selected' : ''; ?>>50</option>
                                        </select>
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <button id="filter-labor-btn" class="inline-flex items-center px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                            </svg>
                                            Filter
                                        </button>
                                        <button id="reset-labor-btn" class="inline-flex items-center px-3 py-2 text-sm bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="labor-container">
                                <div class="space-y-4">
                                    <?php if (!empty($labor_costs)): ?>
                                        <?php foreach ($labor_costs as $labor): ?>
                                            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                <div class="flex justify-between items-start mb-3">
                                                    <div class="flex-1">
                                                        <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($labor['position_name']); ?></h4>
                                                        <p class="text-sm text-gray-600">Posisi/Jabatan</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-lg font-bold text-green-600">Rp <?php echo number_format($labor['hourly_rate'], 0, ',', '.'); ?></span>
                                                        <p class="text-xs text-gray-500">per jam</p>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end space-x-2">
                                                    <button onclick="editLabor(<?php echo htmlspecialchars(json_encode($labor)); ?>)" 
                                                            class="inline-flex items-center px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-600 border border-blue-300 rounded-lg transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Edit
                                                    </button>
                                                    <button onclick="deleteLabor(<?php echo $labor['id']; ?>, '<?php echo htmlspecialchars($labor['position_name']); ?>')" 
                                                            class="inline-flex items-center px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-600 border border-red-300 rounded-lg transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-12 text-gray-500">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">Belum ada posisi tenaga kerja</p>
                                            <p class="text-sm">Tambahkan posisi tenaga kerja pertama Anda di atas</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Pagination for labor -->
                                <?php if ($total_pages_labor > 1): ?>
                                    <div class="flex justify-center items-center space-x-2 mt-6">
                                        <?php if ($page_labor > 1): ?>
                                            <button onclick="loadLaborData(<?php echo $page_labor - 1; ?>)" 
                                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                                Prev
                                            </button>
                                        <?php endif; ?>

                                        <?php 
                                        $start_page = max(1, $page_labor - 2);
                                        $end_page = min($total_pages_labor, $page_labor + 2);
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <button onclick="loadLaborData(<?php echo $i; ?>)" 
                                                    class="px-3 py-2 text-sm <?php echo $i == $page_labor ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> rounded-lg transition-colors">
                                                <?php echo $i; ?>
                                            </button>
                                        <?php endfor; ?>

                                        <?php if ($page_labor < $total_pages_labor): ?>
                                            <button onclick="loadLaborData(<?php echo $page_labor + 1; ?>)" 
                                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                                Next
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="/cornerbites-sia/assets/js/overhead.js"></script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
