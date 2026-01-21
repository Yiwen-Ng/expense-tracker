<template>
  <div class="dashboard-container">

    <header class="main-header">
      <div class="brand">
        <h1>Expense Tracker</h1>
      </div>
    </header>

    <section class="summary-grid">
      <!-- Monthly Overview -->
      <div class="card summary-card monthly-overview">
        <div class="overview-header">
          <span class="label">MONTHLY OVERVIEW</span>
          <span class="month">{{ currentMonthLabel }}</span>
        </div>

        <div class="overview-amount">
          MYR {{ formatCurrency(currentMonthTotal) }}
        </div>

        <div class="comparison-block">
          <div class="comparison-title">
            <span
              :class="percentageChange >= 0 ? 'up' : 'down'"
            >
              {{ percentageChange >= 0 ? '↑' : '↓' }}
              Compared to previous months
            </span>
          </div>

          <ul class="previous-list">
            <li v-for="m in previousMonths" :key="m.label">
              <span class="month">{{ m.label }}</span>
              <span class="amount">MYR {{ formatCurrency(m.total) }}</span>
            </li>
          </ul>

          <div class="percentage-change"
              :class="percentageChange >= 0 ? 'up' : 'down'">
            {{ percentageChange >= 0 ? '+' : '' }}{{ percentageChange }}%
            from last month
          </div>
        </div>
      </div>

      <!-- Top Category -->
      <div class="card summary-card" v-if="topCategory">
        <div class="card-content">
          <span class="summary-title">Top Category</span>

          <h2 class="summary-main">
            {{ topCategory.category }}
          </h2>

          <p class="summary-amount">
            MYR {{ formatCurrency(topCategory.amount) }}
          </p>

          <p class="summary-meta">
            {{ topCategory.percentage }}% of this month’s spending
          </p>

          <!-- Mini progress bar -->
          <div class="mini-bar">
            <div
              class="mini-bar-fill"
              :style="{ width: topCategory.percentage + '%' }"
            ></div>
          </div>

          <!-- Expandable button -->
          <button
            class="expand-btn"
            @click="showTopCategories = !showTopCategories"
          >
            {{ showTopCategories ? 'Hide details' : 'View top 3 categories' }}
          </button>

          <!-- Expandable list -->
          <Transition name="fade">
            <ul v-if="showTopCategories" class="top-category-list">
              <li v-for="item in topCategories" :key="item.category">
                <span>{{ item.category }}</span>
                <strong>MYR {{ formatCurrency(item.amount) }}</strong>
              </li>
            </ul>
          </Transition>

        </div>
      </div>

      <!-- Category Aggregation (Bar Chart) -->
      <div class="card summary-card">
        <span class="currency-label">Spending by Category</span>
        <div class="chart-container">
          <canvas ref="barCanvas"></canvas>
        </div>
      </div>
    </section>

    <!-- Search & Filter -->
    <section class="search-card">
      <div class="search-wrapper">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by category or description"
          class="search-input"
        />

        <!-- Clear button -->
        <button
          v-if="searchQuery"
          class="clear-btn"
          @click="searchQuery = ''"
          aria-label="Clear search"
        >
          <!-- X icon (SVG) -->
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </section>

    <div class="main-layout">
      
      <section class="card form-card">
        <h3>Add New Transaction</h3>
        <form @submit.prevent="submitForm" class="transaction-form">
          
          <!-- Description -->
          <div class="input-field">
            <label>Description</label>
            <input v-model="form.description" type="text" placeholder="What was this for?" required />
          </div>

          <!-- Category -->
          <div class="input-field">
            <label>Category</label>
            <select v-model="form.category" required>
              <option value="">Select category</option>
              <option value="Food">Food</option>
              <option value="Transportation">Transportation</option>
              <option value="Shopping">Shopping</option>
              <option value="Healthcare">Healthcare</option>
              <option value="Insurance">Insurance</option>
              <option value="Utilities">Utilities</option>
              <option value="Travel">Travel</option>
              <option value="Entertainment">Entertainment</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="input-row">
            <!-- Currency -->
            <div class="input-field">
              <label>Currency</label>
              <select v-model="form.currency">
                <option value="MYR">MYR</option>
              </select>
            </div>
            
            <!-- Amount -->
            <div class="input-field">
              <label>Amount</label>
              <input v-model.number="form.amount" type="number" step="0.01" placeholder="0.00" required />
            </div>
          </div>

          <!-- Transaction Date -->
          <div class="input-field">
            <label>Transaction Date</label>
            <input v-model="form.transaction_date" type="date" required />
          </div>

          <button type="submit" class="submit-btn">Save Transaction</button>
        </form>
      </section>

      <section class="card list-card">

        <div class="list-header">
          <h3>Monthly Transactions</h3>

          <div style="display:flex; gap:12px; align-items:center;">
            <!-- Month filter dropdown -->
            <select v-model="selectedMonth">
              <option value="all">All Months</option>
              <option
                v-for="(m, index) in months"
                :key="index"
                :value="index"
              >
                {{ m }}
              </option>
            </select>

            <!-- Year filter dropdown -->
            <select v-model="selectedYear">
              <option
                v-for="y in availableYears"
                :key="y"
                :value="y"
              >
                {{ y }}
              </option>
            </select>

            <button class="export-btn" @click="exportToCSV" title="Export CSV" aria-label="Export CSV">
              <svg 
                xmlns="http://www.w3.org/2000/svg" 
                width="24" 
                height="24" 
                viewBox="0 0 24 24" 
                fill="none" 
                stroke="currentColor" 
                stroke-width="2" 
                stroke-linecap="round" 
                stroke-linejoin="round"
              >
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
              </svg>
            </button>

            <!-- Count updates automatically -->
            <!--span class="badge">{{ filteredTransactions.length }} Shown</span-->
          </div>
        </div>
        
        <div class="table-wrapper">
          <table class="data-table">

            <thead>
              <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Category</th>
                <th class="text-right">Amount</th>
                <th>Action</th>
              </tr>
            </thead>

            <!-- TransitionGroup IS the tbody -->
            <TransitionGroup tag="tbody" name="fade-slide">
              <tr v-for="item in paginatedTransactions" :key="item.id">
                
                <!-- Date -->
                <td class="date-cell">
                  {{ formatDate(item.transaction_date) }}
                </td>

                <!-- Description -->
                <td class="desc-cell">
                  <input
                    v-if="editingRowId === item.id"
                    v-model="editBuffer.description"
                    class="inline-input"
                  />
                  <span v-else>
                    {{ item.description }}
                  </span>
                </td>

                <!-- Category -->
                <td class="desc-cell">
                  <!-- EDIT MODE -->
                  <select
                    v-if="editingRowId === item.id"
                    v-model="editBuffer.category"
                    class="inline-input"
                  >
                    <option value="Food">Food</option>
                    <option value="Transportation">Transportation</option>
                    <option value="Shopping">Shopping</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Travel">Travel</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Other">Other</option>
                  </select>

                  <!-- VIEW MODE -->
                  <span
                    v-else
                    class="category-badge clickable"
                    :class="getCategoryClass(item.category)"
                    @click="filterByCategory(item.category)"
                    title="Filter by category"
                  >
                    {{ item.category }}
                  </span>
                </td>
                  
                <!-- Amount -->
                <td class="amount-cell" :class="editBuffer.amount >= 0 ? 'positive' : 'negative'">
                  <input
                    v-if="editingRowId === item.id"
                    type="number"
                    v-model="editBuffer.amount"
                    class="inline-input amount-input"
                  />
                  <span v-else>
                    {{ item.currency }} {{ formatCurrency(item.amount) }}
                  </span>
                </td>
                  
                <!-- Action -->
                <td class="action-cell">
                  <div class="action-btn-container">
                    <!-- Edit Mode -->
                    <template v-if="editingRowId === item.id">
                      <button @click="saveInlineEdit" class="icon-btn save-btn" title="Save">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </button>

                      <button @click="cancelInlineEdit" class="icon-btn cancel-btn" title="Cancel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <line x1="18" y1="6" x2="6" y2="18"></line>
                          <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                      </button>
                    </template>
                    
                    <!-- View Mode -->
                    <template v-else>
                      <button @click="startInlineEdit(item)" class="icon-btn edit-btn" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                      </button>

                      <button @click="deleteTransaction(item.id)" class="delete-btn" title="Delete Transaction">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M3 6h18"></path>
                          <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                          <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                          <line x1="10" y1="11" x2="10" y2="17"></line>
                          <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                      </button>
                    </template>

                  </div>
                </td>
              </tr>

              <tr v-if="filteredTransactions.length === 0">
                <td colspan="5" class="empty-state">No transactions found.</td>
              </tr>
            </TransitionGroup>           
          </table>
        </div>

       <div class="pagination">
        <!-- Previous -->
        <button
          class="page-btn icon-btn"
          @click="currentPage--"
          :disabled="currentPage === 1"
          title="Previous page"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Page numbers -->
          <button
            v-for="page in visiblePages"
            :key="page"
            class="page-btn"
            :class="{ active: page === currentPage, dots: page === '...' }"
            :disabled="page === '...'"
            @click="currentPage = page"
          >
            {{ page }}
          </button>

        <!-- Next -->
        <button
          class="page-btn icon-btn"
          @click="currentPage++"
          :disabled="currentPage === totalPages || totalPages === 0"
          title="Next page"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      </section>
    </div>

    <Transition name="slide-up">
      <div v-if="showToast" class="toast-notification">
        {{ toastMessage }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import Chart from 'chart.js/auto'

// Import necessary tools from Vue
import { ref, onMounted, computed } from 'vue';
import { watch } from 'vue';

// --- API CONFIG ---
const API_BASE = "http://localhost/expense-tracker/backend/api";

// --- REACTIVE STATE ---
// Use 'ref' so that if these values change, the HTML updates automatically

const transactions = ref([]);   // Store the array of all transaction rows
const summaryData = ref([]);    // Store the summary (totals per currency)

// Represent the data currently sitting in input boxes
const form = ref({
  description: '',
  category: '',  
  amount: '',
  currency: 'MYR',
  transaction_date: new Date().toISOString().split('T')[0]    // Set the date input to 'today' by default
});

// --- SEARCH STATE ---
const searchQuery = ref('');

// --- FILTER STATE ---
// Month and year filter
const selectedMonth = ref(new Date().getMonth())    // 0–11 or 'all'
const selectedYear = ref(new Date().getFullYear())

// Reset page when page size changes
watch(
  [selectedMonth, selectedYear, searchQuery, transactions],
  () => {
    currentPage.value = 1
  }
)

const months = [
  'Jan', 'Feb', 'Mar', 'Apr',
  'May', 'Jun', 'Jul', 'Aug',
  'Sep', 'Oct', 'Nov', 'Dec'
]

const availableYears = computed(() => {
  const years = new Set()

  transactions.value.forEach(tx => {
    years.add(new Date(tx.transaction_date).getFullYear())
  })

  return Array.from(years).sort((a, b) => b - a)
})

// --- EDIT STATE ---
const editingRowId = ref(null)

const editBuffer = ref({
  description: '',
  category: '',
  amount: 0
})

// Editing a row
const startInlineEdit = (item) => {
  editingRowId.value = item.id

  editBuffer.value = {
    description: item.description,
    category: item.category,          // Original is selected
    amount: item.amount
  }
}
// Save edited row
const saveInlineEdit = async () => {
  try {
    const payload = {
      id: editingRowId.value,
      description: editBuffer.value.description,
      category: editBuffer.value.category,
      amount: editBuffer.value.amount
    }

    const res = await fetch(`${API_BASE}/update.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!res.ok) throw new Error('Update failed')

    editingRowId.value = null
    await loadData()

    triggerToast('Transaction updated successfully !')
  } catch (err) {
    console.error(err)
    alert('Failed to update transaction')
  }
}


// Cancel editing
const cancelInlineEdit = () => {
  editingRowId.value = null
}

// --- PAGINATION ---
const currentPage = ref(1);
const itemsPerPage = 5;

// --- FUNCTIONS ---
// 'async'  : Functions happen in the background

// loadData : Grab all the latest data from the database
// Fetch both list and summary data at the same time to be faster
const loadData = async () => {
  try {
    const [listRes, summaryRes] = await Promise.all([
      fetch(`${API_BASE}/fetch.php`),
      fetch(`${API_BASE}/summary.php`)
    ]);
    
    // Convert the raw data from PHP into JavaScript objects
    // Use .value to update a 'ref' variable in Vue

    transactions.value = await listRes.json();
    summaryData.value = await summaryRes.json();

  } catch (error) {
    console.error("Failed to load data:", error);
  }
};

// submitForm : Send the 'form' data to PHP server to save it
const submitForm = async () => {
  try {
    const response = await fetch(`${API_BASE}/create.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)        // Turn the JS object into a text string
    });

    if (response.ok) {
      
      // Reset form and clear the form back to empty / defaults
      form.value = { 
        description: '', 
        category: '',
        amount: '', 
        currency: 'MYR', 
        transaction_date: new Date().toISOString().split('T')[0] 
      };
      
      // Refresh the data so the new row appears in the table
      await loadData();

      triggerToast("Transaction saved successfully !");
    }
  } catch (err) {
    alert("Check your PHP connection/CORS headers!");
  }
};

// deleteTransaction : Delete a transaction
const deleteTransaction = async (id) => {
  // Ask for confirmation so the user doesn't delete by accident
  if (!confirm("Are you sure you want to delete this?")) return;

  try {
    const response = await fetch(`${API_BASE}/delete.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    });

    if (response.ok) {
      // Refresh the data to show the updated list and summary
      await loadData();

      triggerToast("Transaction deleted !");
    }
  } catch (err) {
    console.error("Delete failed:", err);
  }
};

// This month total expenses
const now = new Date();

const currentMonthLabel = computed(() => {
  if (selectedMonth.value === 'all') return 'All Months'

  return new Date(
    selectedYear.value,
    selectedMonth.value
  ).toLocaleString('default', {
    month: 'long',
    year: 'numeric'
  })
})

// Total for current month
const currentMonthTotal = computed(() => {
  return transactions.value
    .filter(tx => {
      const d = new Date(tx.transaction_date)

      return (
        (selectedMonth.value === 'all' ||
          d.getMonth() === selectedMonth.value) &&
        d.getFullYear() === selectedYear.value
      )
    })
    .reduce((sum, tx) => sum + Number(tx.amount), 0)
})

// Total for previous month (1 month before)
const previousMonthTotal = computed(() => {
  if (selectedMonth.value === 'all') return 0

  const prev = new Date(
    selectedYear.value,
    selectedMonth.value - 1
  )

  return transactions.value
    .filter(tx => {
      const d = new Date(tx.transaction_date)
      return (
        d.getMonth() === prev.getMonth() &&
        d.getFullYear() === prev.getFullYear()
      )
    })
    .reduce((sum, tx) => sum + Number(tx.amount), 0)
})

// Previous months summary
const previousMonths = computed(() => {
  const map = {};

  transactions.value.forEach(tx => {
    const d = new Date(tx.transaction_date);
    const label = d.toLocaleString('default', {
      month: 'long',
      year: 'numeric'
    });

    map[label] = (map[label] || 0) + Number(tx.amount);
  });

  return Object.entries(map)
    .map(([label, total]) => ({ label, total }))
    .filter(m => m.label !== currentMonthLabel.value)
    .slice(0, 2);
});

const percentageChange = computed(() => {
  if (!previousMonthTotal.value) return 0;

  const diff =
    currentMonthTotal.value - previousMonthTotal.value;

  return Math.round((diff / previousMonthTotal.value) * 100);
});

// Top Category 
const currentMonthTransactions = computed(() => {
  const now = new Date();
  const currentMonth = now.getMonth();
  const currentYear = now.getFullYear();

  return transactions.value.filter(tx => {
    const d = new Date(tx.transaction_date);
    return (
      d.getMonth() === currentMonth &&
      d.getFullYear() === currentYear
    );
  });
});

const topCategory = computed(() => {
  // No data → no card
  if (currentMonthTransactions.value.length === 0) {
    return null;
  }

  const totals = {};

  // Sum amount by category
  currentMonthTransactions.value.forEach(tx => {
    if (!totals[tx.category]) {
      totals[tx.category] = 0;
    }
    totals[tx.category] += Number(tx.amount);
  });

  // Find highest category
  let topCategoryName = '';
  let topCategoryAmount = 0;

  for (const category in totals) {
    if (totals[category] > topCategoryAmount) {
      topCategoryAmount = totals[category];
      topCategoryName = category;
    }
  }

  // Total spending this month
  const totalMonthSpending = currentMonthTransactions.value.reduce(
    (sum, tx) => sum + Number(tx.amount),
    0
  );

  const percentage =
    totalMonthSpending > 0
      ? ((topCategoryAmount / totalMonthSpending) * 100).toFixed(1)
      : 0;

  return {
    category: topCategoryName,
    amount: topCategoryAmount,
    percentage
  };
});

// Top 3 categories expandable view
const topCategories = computed(() => {
  const totals = {};

  currentMonthTransactions.value.forEach(tx => {
    if (!totals[tx.category]) {
      totals[tx.category] = 0;
    }
    totals[tx.category] += Number(tx.amount);
  });

  return Object.entries(totals)
    .map(([category, amount]) => ({ category, amount }))
    .sort((a, b) => b.amount - a.amount)
    .slice(0, 3);
});

const showTopCategories = ref(false);

// Category aggregation (bar chart)
const barCanvas = ref(null)
let barChart = null

const categoryColors = {
  Food: '#FFCDD2',
  Transportation: '#FFE0B2',
  Shopping: '#FFF9C4',
  Healthcare: '#DCEDC8',
  Insurance: '#B3E5FC',
  Utilities: '#E1BEE7',
  Travel: '#F8BBD0',
  Entertainment: '#B2DFDB',
  Other: '#CFD8DC'
}

const categoryTotals = computed(() => {
  const map = {}

  transactions.value.forEach(t => {
    const d = new Date(t.transaction_date)

    if (
      (selectedMonth.value === 'all' ||
        d.getMonth() === selectedMonth.value) &&
      d.getFullYear() === selectedYear.value
    ) {
      map[t.category] = (map[t.category] || 0) + Number(t.amount)
    }
  })

  return map
})

watch(categoryTotals, () => {
  if (!barCanvas.value) return

  if (barChart) barChart.destroy()

  barChart = new Chart(barCanvas.value, {
    type: 'bar',
    data: {
      labels: Object.keys(categoryTotals.value),
      datasets: [{
        data: Object.values(categoryTotals.value),
        backgroundColor: Object.keys(categoryTotals.value).map(
          c => categoryColors[c] || '#ccc'
        ),
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } }
    }
  })
})

// filteredTransactions : Derive a filtered list from transactions
// Computed means it auto-updates when data or filter changes

const filteredTransactions = computed(() => {
  let result = transactions.value

  // Month + Year filter
  if (selectedMonth.value !== 'all') {
    result = result.filter(item => {
      const d = new Date(item.transaction_date)
      return (
        d.getMonth() === selectedMonth.value &&
        d.getFullYear() === selectedYear.value
      )
    })
  }

  // Search filter
  if (searchQuery.value) {
    const keyword = searchQuery.value.toLowerCase()

    result = result.filter(item =>
      item.description.toLowerCase().includes(keyword) ||
      item.category.toLowerCase().includes(keyword)
    )
  }

  return result
})

// Create paginated computed list
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredTransactions.value.slice(start, end);
});

// Total pages pagination
const totalPages = computed(() => {
  return Math.ceil(filteredTransactions.value.length / itemsPerPage);
});

const pageNumbers = computed(() => {
  return Array.from({ length: totalPages.value }, (_, i) => i + 1);
});

// Visible page numbers
const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 7) {
    // Show all pages
    for (let i = 1; i <= total; i++) pages.push(i);
    return pages;
  }

  // Always show first page
  pages.push(1);

  // Left ellipsis
  if (current > 4) {
    pages.push('...');
  }

  // Middle pages
  const start = Math.max(2, current - 1);
  const end = Math.min(total - 2, current + 1);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  // Right ellipsis
  if (current < total - 3) {
    pages.push('...');
  }

  // Always show last 2 pages
  pages.push(total - 1, total);

  return pages;
});

const getCategoryClass = (category) => {
  switch (category) {
    case 'Food':
      return 'badge-food'
    case 'Transportation':
      return 'badge-transport'
    case 'Shopping':
      return 'badge-shopping'
    case 'Healthcare':
      return 'badge-health'
    case 'Insurance':
      return 'badge-insurance'
    case 'Utilities':
      return 'badge-utilities'
    case 'Travel':
      return 'badge-travel'
    case 'Entertainment':
      return 'badge-entertainment'
    default:
      return 'badge-other'
  }
};

const filterByCategory = (category) => {
  if (editingRowId.value !== null) return
  searchQuery.value = category.toLowerCase()
}

const exportToCSV = () => {
  if (filteredTransactions.value.length === 0) {
    alert("No data to export");
    return;
  }

  // CSV headers
  const headers = [
    "Date",
    "Description",
    "Category",
    "Amount",
    "Currency"
  ];

  // Convert rows to CSV format
  const rows = filteredTransactions.value.map(item => [
    item.transaction_date,
    `"${item.description}"`, // quotes prevent comma issues
    item.category,
    item.amount,
    item.currency
  ]);

  const csvContent = [
    headers.join(","),
    ...rows.map(row => row.join(","))
  ].join("\n");

  // Create file
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  // Trigger download
  const link = document.createElement("a");
  link.href = url;
  link.download = `transactions_${new Date().toISOString().split("T")[0]}.csv`;
  link.click();

  URL.revokeObjectURL(url);

  triggerToast("CSV Exported successfully !");
};

// --- TOAST STATE ---
const showToast = ref(false);
const toastMessage = ref('');

// Helper function to trigger the notification
const triggerToast = (msg) => {
  toastMessage.value = msg;
  showToast.value = true;
  
  // Hide the toast automatically after 3 seconds
  setTimeout(() => {
    showToast.value = false;
  }, 3000);
};

// --- FORMATTERS ---
// These functions don't change the data, they just make it look pretty for the user
const formatDate = (d) => new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
const formatCurrency = (v) => parseFloat(v).toLocaleString(undefined, { minimumFractionDigits: 2 });

// --- LIFECYCLE HOOK ---
// This tells Vue that the moment this component is ready and run loadData
onMounted(loadData);
</script>

<style scoped>
/* Main Container - Full Screen Width */
.dashboard-container {
  --bg-beige: #f9f7f2;      
  --accent-blue: #dbeafe;   
  --text-dark: #1a1f36;
  --card-white: #ffffff;
  --border-soft: #e3e8ee;
  --dark-blue: #325A71;

  /* width: 165%; */           
  min-height: 100vh;
  padding: 20px;
  background-color: var(--bg-beige);
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, sans-serif;
  color: var(--text-dark);
}

/* Header */
.main-header { 
  margin-bottom: 20px; 
}

.brand h1 { 
  margin: 0; 
  color: var(--text-dark); 
  font-size: 32px; 
  font-weight: 700; 
}

/* Summary Cards */
.summary-grid {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.summary-card {
  flex: 1;
  min-width: 250px;
  background: var(--accent-blue);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  border: 1px solid #c9d9f0;
  min-height: 320px;
  display: flex;
  flex-direction: column;
}

/* Monthly Overview Card */
.monthly-overview {
  padding: 24px;
}

.overview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 10px 0;
}

.overview-header .label {
  font-size: 13px;
  letter-spacing: 0.08em;
  color: var(--dark-blue);
  font-weight: 700;
}

.overview-header .month {
  font-size: 13px;
  color: var(--dark-blue);
}

.overview-amount {
  margin: 16px 0;
  font-size: 32px;
  font-weight: 700;
  color: #0f172a;
}

.comparison-block {
  margin-top: 16px;
}

.comparison-title {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 10px;
}

.comparison-title .up {
  color: #16a34a;
}

.comparison-title .down {
  color: #dc2626;
}

.previous-list {
  list-style: none;
  padding: 0;
  margin: 10px 0;
}

.previous-list li {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  margin-bottom: 4px;
}

.percentage-change {
  margin-top: 10px;
  font-size: 14px;
  font-weight: 600;
}

.percentage-change.up {
  color: #16a34a;
}

.percentage-change.down {
  color: #dc2626;
}

.currency-label { 
  color: var(--dark-blue); 
  font-size: 13px; 
  font-weight: 700; 
  text-transform: uppercase; 
  letter-spacing: 0.5px; 
  margin: 10px 0;
}

.total-amount { 
  margin: 10px 0; 
  font-size: 34px; 
  color: var(--text-dark); 
  font-weight: 800; 
}

.transaction-count { 
  font-size: 13px; 
  color: #58667e; 
}

/* Top Category */
.summary-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--dark-blue);
  margin: 10px 0;
  text-transform: uppercase;
}

.summary-main {
  font-size: 1.4rem;
  font-weight: 600;
  margin: 10px 0;
}

.summary-amount {
  font-size: 1.2rem;
  font-weight: 500;
  margin-bottom: 10px;
}

.summary-meta {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 20px;
}

.mini-bar {
  margin-top: 8px;
  height: 8px;
  background: #e5e7eb;
  border-radius: 999px;
  overflow: hidden;
}

.mini-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #22c55e);
  border-radius: 999px;
  transition: width 0.4s ease;
}

/* Expandable List */
.expand-btn {
  margin-top: 15px;
  background: none;
  border: none;
  color: #6366f1;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
}

.top-category-list {
  margin-top: 8px;
  list-style: none;
  padding: 0;
}

.top-category-list li {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  padding: 4px 0;
  border-bottom: 1px dashed #e5e7eb;
}

/* Bar Chart */
.chart-container {
  position: relative;
  flex: 1;
  margin-top: 12px;
  display: flex;
  align-items: center;
}

.chart-container canvas {
  width: 100% !important;
  height: 100% !important;
}

/* Animation */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Layout Grid */
.main-layout {
  display: grid;
  grid-template-columns: 380px 1fr; 
  gap: 30px;
}

/* White Cards for Form and Table */
.card {
  background: var(--card-white);
  border-radius: 20px;
  padding: 30px;
  border: 1px solid var(--border-soft);
  box-shadow: 0 10px 25px rgba(0,0,0,0.02);
}

h3 { 
  margin-top: 0; 
  margin-bottom: 24px; 
  color: var(--text-dark); 
  font-weight: 700; 
}

/* Form Styles */
.transaction-form { 
  display: flex; 
  flex-direction: column; 
  gap: 18px; 
}

.input-field { 
  display: flex;
  flex-direction: column; 
  gap: 8px; 
}

.input-field label { 
  font-size: 13px; 
  font-weight: 600; 
  color: #4f566b; 
}

.input-row { 
  display: grid; 
  grid-template-columns: 1fr 1fr; 
  gap: 10px; 
}

input, 
select {
  width: 100%;                
  padding: 12px 15px;        
  border: 1px solid #dcdfe4;
  border-radius: 10px;       
  background-color: #ffffff;
  font-size: 15px;
  color: var(--text-dark);
  font-family: inherit;       
  transition: all 0.2s ease;
  box-sizing: border-box;    
  appearance: none;           
}

/* Specific styling for the select dropdown to add a custom arrow */
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%234f566b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 15px center;  
  padding-right: 50px;                    
  cursor: pointer;
  text-align: center;
}

/* Focus effect: When the user clicks into the box */
input:focus, 
select:focus {
  outline: none;
  border-color: #4318ff;           
  box-shadow: 0 0 0 3px rgba(67, 24, 255, 0.1);   /* Subtle blue glow */
}

/* Hide the small squeezed arrows for Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Increase the internal padding of all inputs so they feel spacious */
input, 
select {
  padding: 14px 16px;   
  height: 50px;         
  font-size: 16px;
}

.submit-btn {
  background: var(--accent-blue);
  color: var(--dark-blue);
  border: none;
  padding: 14px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  margin-top: 10px;
}

/* Table Styles */
.list-header { 
  display: flex; 
  flex-direction: row;
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 20px; 
  width: 100%;
}

.badge { 
  background: var(--accent-blue); 
  color: var(--dark-blue); 
  padding: 14px 18px;
  width: 150px;
  border-radius: 30px; 
  font-size: 14px; 
  font-weight: bold; 
}

.table-wrapper {
  position: relative;
}

/* Ensure the heading doesn't push the badge away */
.list-header h3 {
  margin: 0;
}

.data-table { 
  width: 100%; 
  border-collapse: collapse;
}

.data-table th { 
  text-align: left; 
  padding: 12px; 
  border-bottom: 2px solid #f4f7fe; 
  color: #a3aed0; 
  font-size: 13px; 
}

.data-table td { 
  padding: 18px 16px; 
  border-bottom: 1px solid #f4f7fe; 
  color: var(--text-dark); 
  font-weight: 500; 
}

.date-cell { 
  color: #697386; 
  width: 100px; 
}

.amount-cell { 
  font-weight: 700; 
}

.text-right { 
  text-align: right; 
}

.positive { 
  color: #059669; 
}

/* Action */
.inline-input {
  width: 100%;
  padding: 6px 8px;
  border: 1px solid #d0d5dd;
  border-radius: 6px;
  font-size: 14px;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  margin-right: 6px;
}

.amount-input {
  text-align: right;
}

.action-cell {
  text-align: left; 
  vertical-align: middle;
}

.action-btn-container {
  display: flex; 
  gap: 4px; 
  justify-content: flex-start;
  align-items: center;
}

/* Base style for all action buttons (Edit, Save, Cancel, Delete) */
.icon-btn, .delete-btn {
  background: transparent;
  border: none;
  color: #64748b; /* Matches your --text-muted grey */
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

/* Edit Button Hover: Blue Glow */
.edit-btn:hover {
  color: #3b82f6; 
  background: rgba(59, 130, 246, 0.1);
  transform: scale(1.1);
}

/* Save Button Hover: Green Glow */
.save-btn:hover {
  color: #10b981; 
  background: rgba(16, 185, 129, 0.1);
  transform: scale(1.1);
}

/* Cancel & Delete Button Hover: Red Glow */
.cancel-btn:hover, .delete-btn:hover {
  color: #ff5c5c; 
  background: rgba(255, 92, 92, 0.1);
  transform: scale(1.1);
}

.toast-notification {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  /* Changed to a lighter, more vibrant purple-blue */
  background: var(--accent-blue); 
  color: var(--dark-blue);
  padding: 14px 28px;
  border-radius: 16px; /* Slightly rounder for the modern look */
  font-weight: 500;
  font-size: 15px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  z-index: 1000;
  
  /* Glassmorphism effect */
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  
  /* Ensure it looks good on mobile */
  white-space: nowrap;
}

/* Animation for the toast sliding up */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.4s ease;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translate(-50%, 20px);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translate(-50%, 20px);
}

.search-card {
  margin-bottom: 20px;
  border-radius: 20px;
  border: 1px solid var(--border-soft);
  box-shadow: 0 10px 25px rgba(0,0,0,0.02);
}

.search-wrapper {
  position: relative;
  width: 100%;
}

.search-input {
  width: 100%;
  padding-right: 40px; /* space for X button */
}

.clear-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  cursor: pointer;
  color: #888;
  padding: 4px;
}

.clear-btn:hover {
  color: #333;
}

.category-badge {
  display: inline-block;
  padding: 6px 12px;
  font-size: 15px;
  font-weight: 800;
  border-radius: 999px;
  color: var(--dark-blue);
  white-space: nowrap;
}

/* Category colors */
.badge-food {
  background-color: #FFEBEE; /* soft blush */
}

.badge-transport {
  background-color: #FFF3E0; /* peach cream */
}

.badge-shopping {
  background-color: #FFFDE7; /* lemon chiffon */
}

.badge-health {
  background-color: #F1F8E9; /* mint cream */
}

.badge-insurance {
  background-color: #E1F5FE; /* pale sky */
}

.badge-utilities {
  background-color: #F3E5F5; /* lavender mist */
}

.badge-travel {
  background-color: #FCE4EC; /* rose water */
}

.badge-entertainment {
  background-color: #E0F2F1; /* dew drop */
}

.badge-other {
  background-color: #ECEFF1; /* cloud grey */
}

.category-badge.clickable {
  cursor: pointer;
}

.category-badge.clickable:hover {
  opacity: 0.85;
  transform: scale(1.05);
}

.export-btn {
  background: transparent;  
  border: none;              
  height: 50px;            
  width: 50px;              
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  color: var(--dark-blue);   
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.export-btn:hover {
  opacity: 0.7;
  transform: scale(1.1);     /* Subtle grow effect */
}

.export-btn svg {
  width: 24px;  
  height: 24px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px;
  margin-top: 16px;
}

/* Base pagination button */
.page-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: #e5e7eb;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease, transform 0.1s ease;
}

/* Page number active */
.page-btn.active {
  background: var(--accent-blue);
  color: var(--dark-blue);
  font-weight: 800;
}

.page-btn.dots {
  cursor: default;
  border: none;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Hover */
.page-btn:hover:not(:disabled):not(.active) {
  background: #d1d5db;
  transform: translateY(-1px);
}

/* Icon buttons (prev / next) */
.icon-btn svg {
  stroke-width: 2.2;
}

/* Disabled state */
.page-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  transform: none;
}

/* Fade IN only */
.fade-enter-active {
  transition: opacity 0.35s ease;
}

.fade-enter-from {
  opacity: 0;
}

.fade-enter-to {
  opacity: 1;
}

/* Disable leave animation completely */
.fade-leave-active,
.fade-leave-from,
.fade-leave-to {
  transition: none !important;
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 98%) {
  .main-layout { grid-template-columns: 1fr; }
}
</style>