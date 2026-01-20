<template>
  <div class="dashboard-container">

    <header class="main-header">
      <div class="brand">
        <h1>Expense Tracker</h1>
        <p>Manage your finances with ease</p>
      </div>
    </header>

    <section class="summary-grid">

      <!-- (Loop) : If summaryData contains 5 items, Vue will clone this <div> 5 times -->
      <!-- stat : This is an alias for the "current item" being processed -->
      <!-- (Unique ID) : If the list changes, Vue uses the key to identify which specific DOM element corresponds to which data object -->

      <div v-for="stat in summaryData" :key="stat.currency" class="card summary-card">
        <div class="card-content">
          <span class="currency-label">{{ stat.currency }} Balance</span>
          <h2 class="total-amount">{{ formatCurrency(stat.total_amount) }}</h2>
          <p class="transaction-count">{{ stat.total_transactions }} Transactions</p>
        </div>
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

          <div class="input-row">

            <!-- Amount -->
            <div class="input-field">
              <label>Amount</label>
              <input v-model.number="form.amount" type="number" step="0.01" placeholder="0.00" required />
            </div>

            <!-- Currency -->
            <div class="input-field">
              <label>Currency</label>
              <select v-model="form.currency">
                <option value="USD">USD</option>
                <option value="MYR">MYR</option>
                <option value="EUR">EUR</option>
              </select>
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
          <h3>Recent Transactions</h3>

          <!-- {{ transactions.length }} : Data -->
          <span class="badge">{{ transactions.length }} total</span>
        </div>
        
        <div class="table-wrapper">
          <table class="data-table">
            
            <thead>
              <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="text-right">Amount</th>
              </tr>
            </thead>
            
            <tbody>
              <tr v-for="item in transactions" :key="item.id">
                <td class="date-cell">{{ formatDate(item.transaction_date) }}</td>
                <td class="desc-cell">{{ item.description }}</td>
                <td class="amount-cell text-right" :class="item.amount >= 0 ? 'positive' : 'negative'">
                  {{ item.currency }} {{ formatCurrency(item.amount) }}
                </td>
              </tr>
              <tr v-if="transactions.length === 0">
                <td colspan="3" class="empty-state">No transactions found.</td>
              </tr>
            </tbody>
            
          </table>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
// Import necessary tools from Vue
import { ref, onMounted } from 'vue';

// --- REACTIVE STATE ---
// Use 'ref' so that if these values change, the HTML updates automatically

const transactions = ref([]);   // Store the array of all transaction rows
const summaryData = ref([]);    // Store the summary (totals per currency)

// Represent the data currently sitting in input boxes
const form = ref({
  description: '',
  amount: '',
  currency: 'MYR',
  transaction_date: new Date().toISOString().split('T')[0]    // Set the date input to 'today' by default
});

// --- API CONFIG ---
const API_BASE = "http://localhost/expense-tracker/backend/api";

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
        amount: '', 
        currency: 'MYR', 
        transaction_date: new Date().toISOString().split('T')[0] 
      };
      
      // Refresh the data so the new row appears in the table
      await loadData();
    }
  } catch (err) {
    alert("Check your PHP connection/CORS headers!");
  }
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
  width: 190%;
  min-height: 100vh;
  padding: 40px;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, sans-serif;
}

/* Header */
.main-header { margin-bottom: 30px; }
.brand h1 { margin: 0; color: #1a1f36; font-size: 28px; }
.brand p { margin: 5px 0 0; color: #4f566b; }

/* Summary Cards - Flexbox for spacing */
.summary-grid {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.summary-card {
  flex: 1;
  min-width: 250px;
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
  border: 1px solid #e3e8ee;
}

.currency-label { color: #697386; font-size: 14px; font-weight: 600; }
.total-amount { margin: 10px 0; font-size: 32px; color: #4318ff; }
.transaction-count { font-size: 13px; color: #a3aed0; }

/* Layout Grid */
.main-layout {
  display: grid;
  grid-template-columns: 350px 1fr; /* Form is fixed width, List takes rest */
  gap: 30px;
}

/* Card Style */
.card {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #e3e8ee;
}

h3 { margin-top: 0; margin-bottom: 20px; color: #1a1f36; }

/* Form Styles */
.transaction-form { display: flex; flex-direction: column; gap: 15px; }
.input-field { display: flex; flex-direction: column; gap: 6px; }
.input-field label { font-size: 13px; font-weight: 600; color: #4f566b; }
.input-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

input, 
select {
  width: 100%;                
  padding: 12px 15px;        
  border: 1px solid #dcdfe4;
  border-radius: 10px;       
  background-color: #ffffff;
  font-size: 15px;
  color: #1a1f36;
  font-family: inherit;       
  transition: all 0.2s ease;
  box-sizing: border-box;    
  appearance: none;           
}

/* Specific styling for the select dropdown to add a custom arrow */
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%234f566b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 15px center;   /* Positions the arrow on the right */
  padding-right: 40px;                      /* Space for the arrow */
  cursor: pointer;
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
input, select {
  padding: 14px 16px;   
  height: 50px;         
  font-size: 16px;
}

.submit-btn {
  background: #4318ff;
  color: white;
  border: none;
  padding: 14px;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  margin-top: 10px;
}

/* Table Styles */
.list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.badge { background: #eef2ff; color: #4318ff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 12px; border-bottom: 2px solid #f4f7fe; color: #a3aed0; font-size: 13px; }
.data-table td { padding: 16px 12px; border-bottom: 1px solid #f4f7fe; color: #1a1f36; }

.date-cell { color: #697386; width: 100px; }
.amount-cell { font-weight: 700; }
.text-right { text-align: right; }
.positive { color: #059669; }

/* Responsive adjustments */
@media (max-width: 98%) {
  .main-layout { grid-template-columns: 1fr; }
}
</style>