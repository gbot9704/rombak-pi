
// bahan_baku.js
// JavaScript functions for bahan baku management

const unitOptions = ['kg', 'gram', 'liter', 'ml', 'pcs', 'buah', 'roll', 'meter', 'box', 'botol', 'lembar'];
const typeOptions = ['bahan', 'kemasan'];

// Currency formatting for price input
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('purchase_price_per_unit');
    
    if (priceInput) {
        // Format input saat user mengetik
        priceInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                e.target.value = formatNumber(value);
            }
        });

        // Convert ke number saat submit
        const form = priceInput.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                priceInput.value = priceInput.value.replace(/[^\d]/g, '');
            });
        }
    }

    // Dynamic label and button update based on type selection
    const typeSelect = document.getElementById('type');
    const purchaseSizeLabel = document.getElementById('purchase_size_label');
    const currentStockLabel = document.getElementById('current_stock_label');
    const submitButton = document.getElementById('submit_button');

    if (typeSelect && purchaseSizeLabel && currentStockLabel && submitButton) {
        typeSelect.addEventListener('change', function() {
            if (this.value === 'bahan') {
                purchaseSizeLabel.textContent = 'Ukuran Per Bahan';
                currentStockLabel.textContent = 'Jumlah Bahan Tersedia';
                submitButton.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Bahan
                `;
            } else {
                purchaseSizeLabel.textContent = 'Ukuran Per Kemasan';
                currentStockLabel.textContent = 'Jumlah Kemasan Tersedia';
                submitButton.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Kemasan
                `;
            }
        });
    }

    // Reset button functionality
    const resetRawBtn = document.getElementById('reset_raw_btn');
    const resetKemasanBtn = document.getElementById('reset_kemasan_btn');
    
    if (resetRawBtn) {
        resetRawBtn.addEventListener('click', function() {
            const searchInput = document.getElementById('search_raw');
            const limitSelect = document.getElementById('bahan_limit');
            if (searchInput) searchInput.value = '';
            if (limitSelect) limitSelect.value = '6';
            applySearchRealtime('raw', '', '6');
        });
    }

    if (resetKemasanBtn) {
        resetKemasanBtn.addEventListener('click', function() {
            const searchInput = document.getElementById('search_kemasan');
            const limitSelect = document.getElementById('kemasan_limit');
            if (searchInput) searchInput.value = '';
            if (limitSelect) limitSelect.value = '6';
            applySearchRealtime('kemasan', '', '6');
        });
    }

    // Cancel edit button event
    const cancelButton = document.getElementById('cancel_edit_button');
    if (cancelButton) {
        cancelButton.addEventListener('click', resetForm);
    }
});

function formatNumber(num) {
    return parseInt(num).toLocaleString('id-ID');
}

function editBahanBaku(material) {
    document.getElementById('bahan_baku_id').value = material.id;
    document.getElementById('name').value = material.name;
    document.getElementById('brand').value = material.brand || '';
    document.getElementById('type').value = material.type;
    document.getElementById('unit').value = material.unit;
    
    // Format numbers without .00 for display
    const purchaseSize = parseFloat(material.default_package_quantity);
    document.getElementById('purchase_size').value = purchaseSize % 1 === 0 ? purchaseSize.toFixed(0) : purchaseSize.toString();
    
    const currentStock = parseFloat(material.current_stock);
    document.getElementById('current_stock').value = currentStock % 1 === 0 ? currentStock.toFixed(0) : currentStock.toString();
    
    document.getElementById('purchase_price_per_unit').value = formatNumber(material.purchase_price_per_unit);

    // Update labels and button based on type
    const purchaseSizeLabel = document.getElementById('purchase_size_label');
    const currentStockLabel = document.getElementById('current_stock_label');
    const submitButton = document.getElementById('submit_button');
    const cancelButton = document.getElementById('cancel_edit_button');
    
    if (material.type === 'bahan') {
        purchaseSizeLabel.textContent = 'Ukuran Per Bahan';
        currentStockLabel.textContent = 'Jumlah Bahan Tersedia';
        document.getElementById('form-title').textContent = 'Edit Bahan';
        submitButton.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Update Bahan
        `;
    } else {
        purchaseSizeLabel.textContent = 'Ukuran Per Kemasan';
        currentStockLabel.textContent = 'Jumlah Kemasan Tersedia';
        document.getElementById('form-title').textContent = 'Edit Kemasan';
        submitButton.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Update Kemasan
        `;
    }
    
    submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    submitButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    cancelButton.classList.remove('hidden');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('bahan_baku_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('type').value = typeOptions[0];
    document.getElementById('unit').value = unitOptions[0];
    document.getElementById('purchase_size').value = '';
    document.getElementById('purchase_price_per_unit').value = '';
    document.getElementById('current_stock').value = '';

    // Reset labels to default (bahan)
    const purchaseSizeLabel = document.getElementById('purchase_size_label');
    const currentStockLabel = document.getElementById('current_stock_label');
    purchaseSizeLabel.textContent = 'Ukuran Per Bahan';
    currentStockLabel.textContent = 'Jumlah Bahan Tersedia';

    document.getElementById('form-title').textContent = 'Tambah Bahan Baku/Kemasan Baru';
    
    const submitButton = document.getElementById('submit_button');
    const cancelButton = document.getElementById('cancel_edit_button');

    submitButton.innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Tambah Bahan
    `;
    submitButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
    submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
    cancelButton.classList.add('hidden');
}

// Real-time search dengan debouncing
let searchTimeoutRaw;
let searchTimeoutKemasan;
let limitTimeoutRaw;
let limitTimeoutKemasan;

function applySearchRealtime(type, searchTerm, limit = null) {
    let currentUrl = new URL(window.location.href);
    
    // Store current scroll position
    const scrollPosition = window.pageYOffset;

    if (type === 'raw') {
        currentUrl.searchParams.set('search_raw', searchTerm);
        currentUrl.searchParams.set('bahan_page', '1');
        if (limit !== null) {
            currentUrl.searchParams.set('bahan_limit', limit);
        }
    } else {
        currentUrl.searchParams.set('search_kemasan', searchTerm);
        currentUrl.searchParams.set('kemasan_page', '1');
        if (limit !== null) {
            currentUrl.searchParams.set('kemasan_limit', limit);
        }
    }

    // Store scroll position before redirect
    sessionStorage.setItem('scrollPosition', scrollPosition);
    window.location.href = currentUrl.toString();
}

// Restore scroll position after page reload
window.addEventListener('load', function() {
    const scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem('scrollPosition');
    }
});

// Real-time search untuk bahan baku
document.addEventListener('DOMContentLoaded', function() {
    const searchRaw = document.getElementById('search_raw');
    if (searchRaw) {
        searchRaw.addEventListener('input', function() {
            const searchTerm = this.value;
            clearTimeout(searchTimeoutRaw);
            searchTimeoutRaw = setTimeout(() => {
                applySearchRealtime('raw', searchTerm);
            }, 500);
        });

        searchRaw.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                clearTimeout(searchTimeoutRaw);
                applySearchRealtime('raw', this.value);
            }
        });
    }

    // Real-time search untuk kemasan
    const searchKemasan = document.getElementById('search_kemasan');
    if (searchKemasan) {
        searchKemasan.addEventListener('input', function() {
            const searchTerm = this.value;
            clearTimeout(searchTimeoutKemasan);
            searchTimeoutKemasan = setTimeout(() => {
                applySearchRealtime('kemasan', searchTerm);
            }, 500);
        });

        searchKemasan.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                clearTimeout(searchTimeoutKemasan);
                applySearchRealtime('kemasan', this.value);
            }
        });
    }

    // Real-time limit change untuk bahan baku
    const bahanLimit = document.getElementById('bahan_limit');
    if (bahanLimit) {
        bahanLimit.addEventListener('change', function() {
            const limit = this.value;
            const searchTerm = searchRaw ? searchRaw.value : '';
            clearTimeout(limitTimeoutRaw);
            limitTimeoutRaw = setTimeout(() => {
                applySearchRealtime('raw', searchTerm, limit);
            }, 100);
        });
    }

    // Real-time limit change untuk kemasan
    const kemasanLimit = document.getElementById('kemasan_limit');
    if (kemasanLimit) {
        kemasanLimit.addEventListener('change', function() {
            const limit = this.value;
            const searchTerm = searchKemasan ? searchKemasan.value : '';
            clearTimeout(limitTimeoutKemasan);
            limitTimeoutKemasan = setTimeout(() => {
                applySearchRealtime('kemasan', searchTerm, limit);
            }, 100);
        });
    }
});
