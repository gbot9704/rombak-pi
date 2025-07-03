// Global variables
let searchOverheadTimeout;
let searchLaborTimeout;

// Reset form overhead
function resetOverheadForm() {
    document.getElementById('overhead_id_to_edit').value = '';
    document.getElementById('overhead_name').value = '';
    document.getElementById('overhead_amount').value = '';
    document.getElementById('overhead_description').value = '';
    document.getElementById('overhead_form_title').textContent = 'Tambah Biaya Overhead Baru';
    document.getElementById('overhead_submit_button').innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Tambah Overhead
    `;
    document.getElementById('overhead_cancel_edit_button').classList.add('hidden');
}

// Reset form labor
function resetLaborForm() {
    document.getElementById('labor_id_to_edit').value = '';
    document.getElementById('labor_position_name').value = '';
    document.getElementById('labor_hourly_rate').value = '';
    document.getElementById('labor_form_title').textContent = 'Tambah Posisi Tenaga Kerja Baru';
    document.getElementById('labor_submit_button').innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Tambah Posisi
    `;
    document.getElementById('labor_cancel_edit_button').classList.add('hidden');
}

// Format number function with thousand separators, no decimals
function formatNumber(num) {
    // Remove all non-digit characters first
    const cleanNum = num.toString().replace(/[^\d]/g, '');
    if (cleanNum === '' || cleanNum === '0') return '';
    
    // Convert to integer and format with thousand separators using Indonesian locale
    return parseInt(cleanNum).toLocaleString('id-ID');
}

// Parse formatted number back to integer
function parseFormattedNumber(formattedNum) {
    return formattedNum.replace(/[^\d]/g, '');
}

// Enhanced input formatting with cursor position handling
function handleNumberInput(inputElement) {
    inputElement.addEventListener('input', function(e) {
        // Store cursor position
        const cursorPosition = e.target.selectionStart;
        const oldValue = e.target.value;
        const oldLength = oldValue.length;
        
        // Get only digits
        const digitsOnly = oldValue.replace(/[^\d]/g, '');
        
        // Format the number
        let formattedValue = '';
        if (digitsOnly !== '' && digitsOnly !== '0') {
            formattedValue = formatNumber(digitsOnly);
        }
        
        // Update input value
        e.target.value = formattedValue;
        
        // Calculate new cursor position
        const newLength = formattedValue.length;
        const lengthDiff = newLength - oldLength;
        let newCursorPosition = cursorPosition + lengthDiff;
        
        // Ensure cursor position is within bounds
        newCursorPosition = Math.max(0, Math.min(newCursorPosition, formattedValue.length));
        
        // Set cursor position
        setTimeout(() => {
            e.target.setSelectionRange(newCursorPosition, newCursorPosition);
        }, 0);
    });

    // Handle paste events
    inputElement.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const digitsOnly = pastedText.replace(/[^\d]/g, '');
        if (digitsOnly !== '' && digitsOnly !== '0') {
            e.target.value = formatNumber(digitsOnly);
        } else {
            e.target.value = '';
        }
    });

    // Prevent non-numeric input
    inputElement.addEventListener('keypress', function(e) {
        // Allow: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true)) {
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
}

// Edit overhead
function editOverhead(overhead) {
    document.getElementById('overhead_id_to_edit').value = overhead.id;
    document.getElementById('overhead_name').value = overhead.name;
    
    // Format the amount value properly for editing (remove decimals, add thousand separators)
    const amountValue = Math.floor(parseFloat(overhead.amount));
    document.getElementById('overhead_amount').value = formatNumber(amountValue.toString());
    
    document.getElementById('overhead_description').value = overhead.description || '';
    document.getElementById('overhead_form_title').textContent = 'Edit Biaya Overhead';
    document.getElementById('overhead_submit_button').innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Update Overhead
    `;
    document.getElementById('overhead_cancel_edit_button').classList.remove('hidden');
    
    // Scroll to form
    document.getElementById('overhead_form_title').scrollIntoView({ behavior: 'smooth' });
}

// Edit labor
function editLabor(labor) {
    document.getElementById('labor_id_to_edit').value = labor.id;
    document.getElementById('labor_position_name').value = labor.position_name;
    
    // Format the hourly rate value properly for editing (remove decimals, add thousand separators)
    const rateValue = Math.floor(parseFloat(labor.hourly_rate));
    document.getElementById('labor_hourly_rate').value = formatNumber(rateValue.toString());
    
    document.getElementById('labor_form_title').textContent = 'Edit Posisi Tenaga Kerja';
    document.getElementById('labor_submit_button').innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Update Posisi
    `;
    document.getElementById('labor_cancel_edit_button').classList.remove('hidden');
    
    // Scroll to form
    document.getElementById('labor_form_title').scrollIntoView({ behavior: 'smooth' });
}

// Delete overhead
function deleteOverhead(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus biaya overhead "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/cornerbites-sia/process/hapus_overhead.php';
        
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = 'overhead';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'overhead_id';
        idInput.value = id;
        
        form.appendChild(typeInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Delete labor
function deleteLabor(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus posisi "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/cornerbites-sia/process/hapus_overhead.php';
        
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = 'labor';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'labor_id';
        idInput.value = id;
        
        form.appendChild(typeInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Function untuk load overhead data dengan AJAX
function loadOverheadData(page = 1) {
    const searchInput = document.getElementById('search-overhead-input');
    const limitSelect = document.getElementById('limit-overhead-select');
    const container = document.getElementById('overhead-container');
    
    if (!searchInput || !limitSelect || !container) {
        console.error('Element tidak ditemukan untuk overhead');
        return;
    }

    const searchValue = searchInput.value;
    const limitValue = limitSelect.value;

    const params = new URLSearchParams({
        search_overhead: searchValue,
        limit_overhead: limitValue,
        page_overhead: page,
        ajax: 'overhead'
    });

    // Show loading
    container.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-2 text-gray-600">Memuat...</span></div>';

    fetch(`/cornerbites-sia/pages/overhead_management.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading overhead data:', error);
            container.innerHTML = '<div class="text-center py-12 text-red-600">Terjadi kesalahan saat memuat data overhead.</div>';
        });
}

// Function untuk load labor data dengan AJAX
function loadLaborData(page = 1) {
    const searchInput = document.getElementById('search-labor-input');
    const limitSelect = document.getElementById('limit-labor-select');
    const container = document.getElementById('labor-container');
    
    if (!searchInput || !limitSelect || !container) {
        console.error('Element tidak ditemukan untuk labor');
        return;
    }

    const searchValue = searchInput.value;
    const limitValue = limitSelect.value;

    const params = new URLSearchParams({
        search_labor: searchValue,
        limit_labor: limitValue,
        page_labor: page,
        ajax: 'labor'
    });

    // Show loading
    container.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-2 text-gray-600">Memuat...</span></div>';

    fetch(`/cornerbites-sia/pages/overhead_management.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading labor data:', error);
            container.innerHTML = '<div class="text-center py-12 text-red-600">Terjadi kesalahan saat memuat data tenaga kerja.</div>';
        });
}

// Make functions global for pagination links
window.loadOverheadData = loadOverheadData;
window.loadLaborData = loadLaborData;

// Format currency input with automatic thousand separators
document.addEventListener('DOMContentLoaded', function() {
    // Only load data via AJAX if URL contains reload parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('reload') === '1') {
        // Load initial data automatically after form submission
        loadOverheadData(1);
        loadLaborData(1);
    }

    const amountInput = document.getElementById('overhead_amount');
    const hourlyRateInput = document.getElementById('labor_hourly_rate');

    // Apply enhanced number formatting to inputs
    if (amountInput) {
        handleNumberInput(amountInput);

        // Convert ke number saat submit
        const overheadForm = amountInput.closest('form');
        if (overheadForm) {
            overheadForm.addEventListener('submit', function(e) {
                // Convert formatted number back to raw number for submission
                const rawValue = parseFormattedNumber(amountInput.value);
                if (rawValue === '' || rawValue === '0') {
                    e.preventDefault();
                    alert('Jumlah biaya harus diisi dan lebih dari 0!');
                    return false;
                }
                amountInput.value = rawValue;
            });
        }
    }

    if (hourlyRateInput) {
        handleNumberInput(hourlyRateInput);

        // Convert ke number saat submit
        const laborForm = hourlyRateInput.closest('form');
        if (laborForm) {
            laborForm.addEventListener('submit', function(e) {
                // Convert formatted number back to raw number for submission
                const rawValue = parseFormattedNumber(hourlyRateInput.value);
                if (rawValue === '' || rawValue === '0') {
                    e.preventDefault();
                    alert('Upah per jam harus diisi dan lebih dari 0!');
                    return false;
                }
                hourlyRateInput.value = rawValue;
            });
        }
    }

    // Setup event listeners untuk overhead search
    const searchOverheadInput = document.getElementById('search-overhead-input');
    const limitOverheadSelect = document.getElementById('limit-overhead-select');
    const filterOverheadBtn = document.getElementById('filter-overhead-btn');
    const resetOverheadBtn = document.getElementById('reset-overhead-btn');

    // Setup event listeners untuk labor search
    const searchLaborInput = document.getElementById('search-labor-input');
    const limitLaborSelect = document.getElementById('limit-labor-select');
    const filterLaborBtn = document.getElementById('filter-labor-btn');
    const resetLaborBtn = document.getElementById('reset-labor-btn');

    // Real-time search untuk overhead dengan debouncing
    if (searchOverheadInput) {
        searchOverheadInput.addEventListener('input', function() {
            clearTimeout(searchOverheadTimeout);
            searchOverheadTimeout = setTimeout(() => {
                // Only search if user actually typed something
                const currentValue = this.value.trim();
                const initialValue = this.getAttribute('value') || '';
                if (currentValue !== initialValue) {
                    loadOverheadData(1);
                }
            }, 500);
        });
    }

    // Real-time search untuk labor dengan debouncing
    if (searchLaborInput) {
        searchLaborInput.addEventListener('input', function() {
            clearTimeout(searchLaborTimeout);
            searchLaborTimeout = setTimeout(() => {
                // Only search if user actually typed something
                const currentValue = this.value.trim();
                const initialValue = this.getAttribute('value') || '';
                if (currentValue !== initialValue) {
                    loadLaborData(1);
                }
            }, 500);
        });
    }

    // Event listeners untuk overhead
    if (filterOverheadBtn) {
        filterOverheadBtn.addEventListener('click', function() {
            loadOverheadData(1);
        });
    }

    if (limitOverheadSelect) {
        limitOverheadSelect.addEventListener('change', function() {
            loadOverheadData(1);
        });
    }

    if (resetOverheadBtn) {
        resetOverheadBtn.addEventListener('click', function() {
            searchOverheadInput.value = '';
            limitOverheadSelect.value = '10';
            // Reload page to show all data
            window.location.href = '/cornerbites-sia/pages/overhead_management.php';
        });
    }

    // Event listeners untuk labor
    if (filterLaborBtn) {
        filterLaborBtn.addEventListener('click', function() {
            loadLaborData(1);
        });
    }

    if (limitLaborSelect) {
        limitLaborSelect.addEventListener('change', function() {
            loadLaborData(1);
        });
    }

    if (resetLaborBtn) {
        resetLaborBtn.addEventListener('click', function() {
            searchLaborInput.value = '';
            limitLaborSelect.value = '10';
            // Reload page to show all data
            window.location.href = '/cornerbites-sia/pages/overhead_management.php';
        });
    }

    // Setup cancel edit button events - FIXED
    const overheadCancelBtn = document.getElementById('overhead_cancel_edit_button');
    const laborCancelBtn = document.getElementById('labor_cancel_edit_button');

    if (overheadCancelBtn) {
        overheadCancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetOverheadForm();
            return false;
        });
    }

    if (laborCancelBtn) {
        laborCancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetLaborForm();
            return false;
        });
    }
});