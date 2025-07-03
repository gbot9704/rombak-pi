<?php
// pages/produk.php
// Halaman untuk manajemen data produk (daftar, tambah, edit).

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php'; // Sertakan file koneksi database

$products = [];
$search = $_GET['search'] ?? '';

// Handle AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_start();
}

try {
    $conn = $db;

    // Pagination setup
    $limit_options = [10, 25, 50, 100];
    $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
    $offset = ($page - 1) * $limit;

    // Build WHERE clause for search
    $whereClause = "WHERE 1=1";
    $params = [];

    // Search filter
    if (!empty($search)) {
        $whereClause .= " AND name LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    // Hitung total produk dengan filter
    $countQuery = "SELECT COUNT(*) FROM products " . $whereClause;
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);

    // Mengambil semua kolom yang relevan dari tabel products dengan pagination dan filter
    $query = "SELECT id, name, unit, sale_price, stock FROM products " . $whereClause . " ORDER BY name ASC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error di halaman Produk: " . $e->getMessage());
}

// If this is an AJAX request, return only the table content
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual (Rp)</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($product['unit']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($product['stock']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-green-600">Rp <?php echo number_format($product['sale_price'], 0, ',', '.'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                            class="inline-flex items-center px-3 py-1 border border-indigo-300 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                    <a href="/cornerbites-sia/process/simpan_produk.php?action=delete&id=<?php echo htmlspecialchars($product['id']); ?>" 
                                       class="inline-flex items-center px-3 py-1 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">Belum ada produk yang tercatat</p>
                                <p class="text-gray-400 text-sm mt-1">Mulai tambahkan produk pertama Anda menggunakan form di atas</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="bg-white px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($page > 1): ?>
                    <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div class="flex items-center space-x-2">
                    <form id="limitForm" method="get" class="flex items-center space-x-2">
                        <label for="limitSelect" class="text-sm text-gray-700">Per halaman:</label>
                        <select name="limit" id="limitSelect" onchange="document.getElementById('limitForm').submit()"
                                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php foreach ($limit_options as $opt): ?>
                                <option value="<?php echo $opt; ?>" <?php echo ($limit == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="page" value="1">
                    </form>
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium"><?php echo number_format($offset + 1); ?></span> sampai 
                        <span class="font-medium"><?php echo number_format(min($offset + $limit, $totalProducts)); ?></span> dari 
                        <span class="font-medium"><?php echo number_format($totalProducts); ?></span> produk
                    </div>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>

                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>" 
                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo ($i == $page) ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    echo $content;
    exit;
}

// Pesan sukses atau error setelah proses simpan
$message = '';
$message_type = ''; // 'success' or 'error'
if (isset($_SESSION['product_message'])) {
    $message = $_SESSION['product_message']['text'];
    $message_type = $_SESSION['product_message']['type'];
    unset($_SESSION['product_message']);
}
?>

<?php include_once __DIR__ . '/../includes/header.php'; ?>
<div class="flex h-screen bg-gradient-to-br from-gray-50 to-gray-100 font-sans">
    <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden">

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Manajemen Produk</h1>
            <p class="text-gray-600">Kelola data produk, stok, dan harga untuk optimalisasi bisnis Anda</p>
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

        <!-- Daftar Produk -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Produk Anda</h3>
                            <p class="text-sm text-gray-600">Kelola dan pantau semua produk dalam inventori</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <span class="text-sm text-gray-500">Total:</span>
                            <span class="text-lg font-bold text-blue-600 ml-1"><?php echo number_format($totalProducts); ?> produk</span>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                            <input type="text" id="search-input" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                                   placeholder="Cari nama produk..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                        </div>

                        <!-- Per Halaman -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Per Halaman</label>
                            <select id="limit-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                <?php foreach ($limit_options as $opt): ?>
                                    <option value="<?php echo $opt; ?>" <?php echo ($limit == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="button" id="filter-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <button type="button" id="reset-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden" id="products-container">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual (Rp)</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($product['unit']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($product['stock']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-green-600">Rp <?php echo number_format($product['sale_price'], 0, ',', '.'); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                                                        class="inline-flex items-center px-3 py-1 border border-indigo-300 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edit
                                                </button>
                                                <a href="/cornerbites-sia/process/simpan_produk.php?action=delete&id=<?php echo htmlspecialchars($product['id']); ?>" 
                                                   class="inline-flex items-center px-3 py-1 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            <p class="text-gray-500 text-lg font-medium">Belum ada produk yang tercatat</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <?php if ($page > 1): ?>
                                <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>" 
                                   class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                <?php endif; ?>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div class="flex items-center space-x-2">
                                <form id="limitForm" method="get" class="flex items-center space-x-2">
                                    <label for="limitSelect" class="text-sm text-gray-700">Per halaman:</label>
                                    <select name="limit" id="limitSelect" onchange="document.getElementById('limitForm').submit()"
                                            class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <?php foreach ($limit_options as $opt): ?>
                                            <option value="<?php echo $opt; ?>" <?php echo ($limit == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                    <input type="hidden" name="page" value="1">
                                </form>
                                <div class="text-sm text-gray-700">
                                    Menampilkan <span class="font-medium"><?php echo number_format($offset + 1); ?></span> sampai 
                                    <span class="font-medium"><?php echo number_format(min($offset + $limit, $totalProducts)); ?></span> dari 
                                    <span class="font-medium"><?php echo number_format($totalProducts); ?></span> produk
                                </div>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page - 1; ?>" 
                                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>

                                    <?php 
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    for ($i = $startPage; $i <= $endPage; $i++): 
                                    ?>
                                        <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $i; ?>" 
                                           class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo ($i == $page) ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?search=<?php echo urlencode($search); ?>&limit=<?php echo $limit; ?>&page=<?php echo $page + 1; ?>" 
                                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Next</span>
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Tambah/Edit Produk -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 mt-8">
            <div class="flex items-center mb-6">
                <div class="p-2 bg-purple-100 rounded-lg mr-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900" id="form_title">Tambah Produk Baru</h3>
                    <p class="text-sm text-gray-600 mt-1" id="form_description">Isi detail produk baru Anda atau gunakan form ini untuk mengedit produk yang sudah ada.</p>
                </div>
            </div>

            <form action="/cornerbites-sia/process/simpan_produk.php" method="POST">
                <input type="hidden" name="product_id" id="product_id_to_edit" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="product_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Produk:</label>
                        <input type="text" id="product_name" name="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Contoh: Kopi Latte, Donat Cokelat" required>
                    </div>
                    <div>
                        <label for="unit" class="block text-sm font-semibold text-gray-700 mb-2">Satuan:</label>
                        <select id="unit" name="unit" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" required onchange="toggleCustomUnit()">
                            <option value="">Pilih Satuan</option>
                            <option value="pcs">pcs (pieces)</option>
                            <option value="porsi">porsi</option>
                            <option value="bungkus">bungkus</option>
                            <option value="cup">cup</option>
                            <option value="botol">botol</option>
                            <option value="gelas">gelas</option>
                            <option value="slice">slice</option>
                            <option value="pack">pack</option>
                            <option value="box">box</box>
                            <option value="kg">kg (kilogram)</option>
                            <option value="gram">gram</gram>
                            <option value="liter">liter</liter>
                            <option value="ml">ml (mililiter)</option>
                            <option value="custom">Lainnya (ketik sendiri)</option>
                        </select>
                        <input type="text" id="unit_custom" name="unit_custom" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 mt-2 hidden" placeholder="Ketik satuan custom...">
                    </div>
                    <div>
                        <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">Stok Awal/Saat Ini:</label>
                        <input type="number" id="stock" name="stock" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Contoh: 100" min="0" required>
                    </div>
                    <div>
                        <label for="sale_price" class="block text-sm font-semibold text-gray-700 mb-2">Harga Jual (Rp):</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm font-medium">Rp</span>
                            </div>
                            <input type="text" id="sale_price_display" class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Masukkan harga jual" oninput="formatRupiah(this, 'sale_price')">
                            <input type="hidden" id="sale_price" name="sale_price" required>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-200" id="submit_button">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Tambah Produk
                    </button>
                    <button type="button" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-200 hidden" id="cancel_edit_button" onclick="resetForm()">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal Edit
                    </button>
                </div>
            </form>
        </div>
    </div>            
</main>
    </div>
</div>

<script src="/cornerbites-sia/assets/js/produk.js"></script>

<!-- Script untuk AJAX search tanpa reload halaman -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const limitSelect = document.getElementById('limit-select');
    const filterBtn = document.getElementById('filter-btn');
    const resetBtn = document.getElementById('reset-btn');

    let searchTimeout;

    // Function untuk melakukan AJAX request
    function performSearch() {
        const searchValue = searchInput.value;
        const limitValue = limitSelect.value;

        // Buat URL untuk AJAX request
        const params = new URLSearchParams({
            search: searchValue,
            limit: limitValue,
            ajax: '1' // Flag untuk menandakan ini AJAX request
        });

        // Tampilkan loading indicator
        const container = document.getElementById('products-container');
        container.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-2 text-gray-600">Mencari...</span></div>';

        // Lakukan AJAX request
        fetch(`/cornerbites-sia/pages/produk.php?${params.toString()}`)
            .then(response => response.text())
            .then(html => {
                // Update container dengan hasil pencarian
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = '<div class="text-center py-12 text-red-600">Terjadi kesalahan saat mencari data.</div>';
            });
    }

    // Real-time search dengan debouncing
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 500);
        });
    }

    // Filter button
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            performSearch();
        });
    }

    // Limit select change
    if (limitSelect) {
        limitSelect.addEventListener('change', function() {
            performSearch();
        });
    }

    // Reset button
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            limitSelect.value = '10';
            performSearch();
        });
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>