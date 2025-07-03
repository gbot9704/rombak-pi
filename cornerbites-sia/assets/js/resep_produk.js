// Resep Produk JavaScript Functions

// Definisikan recipeUnitOptions di JavaScript untuk reset form yang benar
const recipeUnitOptions = ['gram', 'kg', 'ml', 'liter', 'pcs', 'buah', 'sendok teh', 'sendok makan', 'cangkir'];

// Format Rupiah function
function formatRupiah(element, hiddenInputId) {
    let value = element.value.replace(/[^0-9]/g, '');
    
    if (value === '') {
        element.value = '';
        document.getElementById(hiddenInputId).value = '';
        return;
    }
    
    let formatted = new Intl.NumberFormat('id-ID').format(value);
    element.value = formatted;
    document.getElementById(hiddenInputId).value = value;
}

// Edit resep item function
function editResepItem(item) {
    document.getElementById('recipe_item_id').value = item.id;
    document.getElementById('raw_material_id').value = item.raw_material_id;
    document.getElementById('quantity_used').value = item.quantity_used;
    document.getElementById('unit_measurement').value = item.unit_measurement;

    document.getElementById('form-resep-title').textContent = 'Edit Item Resep';
    const submitButton = document.getElementById('submit_resep_button');
    const cancelButton = document.getElementById('cancel_edit_resep_button');
    
    submitButton.innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
        </svg>
        Update Item Resep
    `;
    submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    submitButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    cancelButton.classList.remove('hidden');

    // Smooth scroll to form
    document.getElementById('form-resep-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Reset resep form function
function resetResepForm() {
    document.getElementById('recipe_item_id').value = '';
    document.getElementById('raw_material_id').value = '';
    document.getElementById('quantity_used').value = '';
    document.getElementById('unit_measurement').value = recipeUnitOptions[0];

    document.getElementById('form-resep-title').textContent = 'Tambah Item ke Resep';
    const submitButton = document.getElementById('submit_resep_button');
    const cancelButton = document.getElementById('cancel_edit_resep_button');
    
    submitButton.innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Tambah Item Resep
    `;
    submitButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
    submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
    cancelButton.classList.add('hidden');
}

// Real-time search dengan debouncing dan scroll position preservation
let searchTimeoutRecipe;
let limitTimeoutRecipe;
let currentScrollPosition = 0;

function saveScrollPosition() {
    currentScrollPosition = window.pageYOffset;
}

function restoreScrollPosition() {
    window.scrollTo(0, currentScrollPosition);
}

function applySearchRealtimeRecipe(searchTerm, limit = null) {
    saveScrollPosition();
    
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('search_recipe', searchTerm);
    currentUrl.searchParams.set('recipe_page', '1');
    if (limit !== null) {
        currentUrl.searchParams.set('recipe_limit', limit);
    }
    
    // Store scroll position in sessionStorage
    sessionStorage.setItem('resepScrollPosition', currentScrollPosition);
    
    window.location.href = currentUrl.toString();
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Restore scroll position after page load
    const savedScrollPosition = sessionStorage.getItem('resepScrollPosition');
    if (savedScrollPosition) {
        setTimeout(() => {
            window.scrollTo(0, parseInt(savedScrollPosition));
            sessionStorage.removeItem('resepScrollPosition');
        }, 100);
    }

    // Real-time search untuk resep
    const searchRecipeInput = document.getElementById('search_recipe');
    if (searchRecipeInput) {
        searchRecipeInput.addEventListener('input', function() {
            const searchTerm = this.value;
            clearTimeout(searchTimeoutRecipe);
            searchTimeoutRecipe = setTimeout(() => {
                applySearchRealtimeRecipe(searchTerm);
            }, 500);
        });

        // Enter key support untuk search
        searchRecipeInput.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                clearTimeout(searchTimeoutRecipe);
                applySearchRealtimeRecipe(this.value);
            }
        });
    }

    // Real-time limit change untuk resep
    const recipeLimitSelect = document.getElementById('recipe_limit');
    if (recipeLimitSelect) {
        recipeLimitSelect.addEventListener('change', function() {
            const limit = this.value;
            const searchTerm = document.getElementById('search_recipe').value;
            clearTimeout(limitTimeoutRecipe);
            limitTimeoutRecipe = setTimeout(() => {
                applySearchRealtimeRecipe(searchTerm, limit);
            }, 100);
        });
    }

    console.log('Resep Produk page loaded');
});

// Function untuk menampilkan tab breakdown yang berbeda
function showBreakdownTab(tabName) {
    // Hide semua content
    document.getElementById('content-bahan_baku').classList.add('hidden');
    document.getElementById('content-tenaga_kerja').classList.add('hidden');
    document.getElementById('content-overhead').classList.add('hidden');

    // Reset semua tab button
    document.getElementById('tab-bahan_baku').className = 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700';
    document.getElementById('tab-tenaga_kerja').className = 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700';
    document.getElementById('tab-overhead').className = 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700';

    // Show content yang dipilih
    document.getElementById('content-' + tabName).classList.remove('hidden');

    // Set active tab
    document.getElementById('tab-' + tabName).className = 'px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600 bg-blue-50';
}