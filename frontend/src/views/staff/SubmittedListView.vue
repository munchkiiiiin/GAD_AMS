<template>
      <main class="twg-main-content">
      
        <div class="twg-content-wrapper">
          
          <div class="twg-header">
            <h1 class="twg-title">Submission Tracker</h1>
            <p class="twg-subtitle">Monitor, review, and evaluate submitted Activity Designs and Accomplishment Reports per institutional unit.</p>
          </div>

          <section class="stats-section">
            <div 
              v-for="stat in metricsStats" 
              :key="stat.label" 
              class="stat-card"
            >
              <div class="stat-card-inner">
                <div :class="['stat-icon', stat.bgClass]">
                  <span class="material-symbols-outlined stat-icon-symbol" :class="stat.iconColor">
                    {{ stat.icon }}
                  </span>
                </div>
                <div class="stat-info">
                  <h3 class="stat-value">
                    {{ stat.value }}
                  </h3>
                  <p class="stat-label">
                    {{ stat.label }}
                  </p>
                </div>
              </div>
            </div>
          </section>

          <section class="filter-section">
            <div class="filter-controls">
              <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input 
                  v-model="searchQuery"
                  type="text" 
                  placeholder="Search unit name or code..." 
                  class="search-input"
                />
              </div>

              <div class="select-wrapper">
                <select 
                  v-model="statusFilter"
                  class="filter-select"
                >
                  <option value="all">All Units</option>
                  <option value="active">With Submissions</option>
                  <option value="empty">No Submissions</option>
                </select>
                <span class="select-arrow">▼</span>
              </div>
            </div>

            <div class="per-page-controls">
              <span class="per-page-label">Show</span>
              <select 
                v-model="perPage"
                @change="handlePerPageChange"
                class="per-page-select"
              >
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
              <span class="per-page-label">records</span>
            </div>
          </section>

          <div class="table-container">
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr class="table-header-row">
                    <th class="table-header-cell table-header-number">#</th>
                    <th class="table-header-cell">College / Office / Unit</th>
                    <th class="table-header-cell table-header-center">TWG</th>
                    <th class="table-header-cell table-header-center">Proponent</th>
                    <th class="table-header-cell table-header-center">Staff</th>
                    <th class="table-header-cell table-header-center">Activity Designs</th>
                    <th class="table-header-cell table-header-center">Accomplishment Reports</th>
                    <th class="table-header-cell table-header-center">Total Status</th>
                  </tr>
                </thead>
                <tbody class="table-body">
                  <tr v-if="filteredUnits.length === 0">
                    <td colspan="8" class="empty-state">
                      No matching Technical Working Group records or submissions discovered in the repository.
                    </td>
                  </tr>
                  
                  <tr 
                    v-else
                    v-for="(unit, index) in filteredUnits" 
                    :key="unit.id"
                    class="table-row"
                  >
                    <td class="table-cell table-cell-number">
                      {{ (currentPage - 1) * perPage + index + 1 }}
                    </td>
                    <td class="table-cell">
                      <div class="unit-name">{{ unit.name }}</div>
                    </td>
                    <td class="table-cell table-cell-center table-cell-count">
                      {{ unit.twg_count || 0 }}
                    </td>
                    <td class="table-cell table-cell-center table-cell-count">
                      {{ unit.nontwg_count || 0 }}
                    </td>
                    <td class="table-cell table-cell-center table-cell-count">
                      {{ unit.staff_count || 0 }}
                    </td>
                    <td class="table-cell table-cell-center table-cell-count">
                      {{ unit.activity_designs_count || 0 }}
                    </td>
                    <td class="table-cell table-cell-center table-cell-count">
                      {{ unit.accomplishment_reports_count || 0 }}
                    </td>
                    <td class="table-cell table-cell-center">
                      <span 
                        class="submission-badge"
                        :class="unit.total_submissions > 0 ? 'submission-badge-active' : 'submission-badge-empty'"
                      >
                        {{ unit.total_submissions || 0 }} Submissions
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="pagination-container">
              <p class="pagination-info">
                Showing <span class="pagination-highlight">{{ paginationMeta.from || 0 }}</span> to <span class="pagination-highlight">{{ paginationMeta.to || 0 }}</span> of <span class="pagination-highlight">{{ paginationMeta.total || 0 }}</span> Technical Working Groups
              </p>
              
              <div class="pagination-controls">
                <button 
                  @click="changePage(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="pagination-btn"
                >
                  ←
                </button>
                <button 
                  v-for="page in paginationMeta.last_page" 
                  :key="page"
                  @click="changePage(page)"
                  :class="['pagination-page', currentPage === page && 'pagination-page-active']"
                >
                  {{ page }}
                </button>
                <button 
                  @click="changePage(currentPage + 1)"
                  :disabled="currentPage === paginationMeta.last_page"
                  class="pagination-btn"
                >
                  →
                </button>
              </div>
            </div>
          </div>

          <div class="footer-note">
            <p class="footer-text">
              📋 Tracking submission configurations for all university Technical Working Groups
            </p>
          </div>

        </div>
      </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';

const router = useRouter();

const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const searchQuery = ref('');
const statusFilter = ref('all');
const twgUnits = ref([]);
const currentPage = ref(1);
const perPage = ref(10);

const paginationMeta = ref({
  total: 0,
  from: 0,
  to: 0,
  last_page: 1
});

const GAD_OFFICE_ID = 1;

const normalizeSubmissionUnits = (units) => {
  const gadStaffUnit = units.find(
    (unit) => (unit.office_name || '').toLowerCase() === 'gad.staff'
  );
  const filtered = units.filter(
    (unit) => (unit.office_name || '').toLowerCase() !== 'gad.staff'
  );

  if (!gadStaffUnit) {
    return filtered;
  }

  return filtered.map((unit) => {
    if (unit.id !== GAD_OFFICE_ID) {
      return unit;
    }

    return {
      ...unit,
      twg_count: (unit.twg_count || 0) + (gadStaffUnit.twg_count || 0),
      nontwg_count: (unit.nontwg_count || 0) + (gadStaffUnit.nontwg_count || 0),
      staff_count: (unit.staff_count || 0) + (gadStaffUnit.staff_count || 0),
      activity_designs_count: (unit.activity_designs_count || 0) + (gadStaffUnit.activity_designs_count || 0),
      accomplishment_reports_count: (unit.accomplishment_reports_count || 0) + (gadStaffUnit.accomplishment_reports_count || 0),
      total_submissions: (unit.total_submissions || 0) + (gadStaffUnit.total_submissions || 0),
    };
  });
};

const metricsStats = ref([
  { label: 'Total TWGs', value: '0', icon: 'groups', iconColor: 'text-green-400', bgClass: 'bg-green-500/10' },
  { label: 'Total Proponents', value: '0', icon: 'person_search', iconColor: 'text-amber-400', bgClass: 'bg-amber-500/10' },
  { label: 'Total Act Designs', value: '0', icon: 'description', iconColor: 'text-purple-400', bgClass: 'bg-purple-500/10' },
  { label: 'Total Acc Reports', value: '0', icon: 'analytics', iconColor: 'text-blue-400', bgClass: 'bg-blue-500/10' }
]);

const filteredUnits = computed(() => {
  let records = twgUnits.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    records = records.filter(unit => 
      unit.name.toLowerCase().includes(query)
    );
  }

  if (statusFilter.value === 'active') {
    records = records.filter(unit => (unit.total_submissions || 0) > 0);
  } else if (statusFilter.value === 'empty') {
    records = records.filter(unit => (unit.total_submissions || 0) === 0);
  }

  return records;
});

const fetchTWGSubmissions = async (page = 1) => {
  try {
    // Staged to fetch live data records matching your endpoint framework
    const response = await api.get(`admin/twg-submissions?page=${page}&per_page=${perPage.value}`);
    const normalized = normalizeSubmissionUnits(response.data.data);
    twgUnits.value = normalized.map(unit => ({
      ...unit,
      name: unit.office_name,
    }));
    paginationMeta.value = {
      ...response.data.meta,
      total: normalized.length,
    };
    currentPage.value = page;

    metricsStats.value[0].value = response.data.meta.total_twg || 0;
    metricsStats.value[1].value = response.data.meta.total_nontwg || 0;
    metricsStats.value[2].value = response.data.meta.total_designs || 0;
    metricsStats.value[3].value = response.data.meta.total_reports || 0;
  } catch (err) {
    console.error('Error parsing operational submissions context registry:', err);
  }
};

const handlePerPageChange = () => {
  currentPage.value = 1;
  fetchTWGSubmissions(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= paginationMeta.value.last_page) {
    fetchTWGSubmissions(page);
  }
};

const viewDetails = (unitId) => {
  router.push(`/staff/twg-details/${unitId}`);
};

const handleLogout = async () => {
  try {
    await api.get('logout');
    localStorage.removeItem('user');
    router.push('/login');
  } catch (err) {
    localStorage.removeItem('user');
    router.push('/login');
  }
};

onMounted(() => {
  if (!user.value.id || user.value.role !== 'gad_staff') {
    router.push('/login');
  } else {
    fetchTWGSubmissions();
  }
});
</script>

<style scoped>
.twg-submissions {
  min-height: 100vh;
  display: flex;
  color: #cbd5e1;
  font-family: system-ui, -apple-system, sans-serif;
}

.twg-submissions ::selection {
  background: rgba(153, 13, 209, 0.3);
  color: white;
}

.twg-main-container {
  flex-grow: 1;
  margin-left: 256px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.twg-main-content {
  padding-left: 0;
  flex-grow: 1;
  width: 100%;
  min-width: 0;
  overflow-x: hidden;
}

.twg-content-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 100%;
  min-width: 0;
}

.twg-header {
  padding: 0 0.25rem;
}

.twg-title {
  font-size: 1.5rem;
  font-weight: 900;
  letter-spacing: -0.025em;
  color: #1a1a2e;
}

.twg-subtitle {
  font-size: 1rem;
  color: #475569;
  margin-top: 0.25rem;
}

.stats-section {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-card {
  padding: 1rem;
  border-radius: 1rem;
  border: 1px solid rgba(185, 121, 204, 0.15);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(8px);
  transition: all 0.3s;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-card-inner {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon-symbol {
  font-weight: 500;
  font-size: 1.125rem;
}

/* Icon Colors */
.text-green-400 { color: #4ade80; }
.text-amber-400 { color: #fbbf24; }
.text-purple-400 { color: #c084fc; }
.text-blue-400 { color: #60a5fa; }

/* Background Classes */
.bg-green-500\/10 { background: rgba(34, 197, 94, 0.1); }
.bg-amber-500\/10 { background: rgba(245, 158, 11, 0.1); }
.bg-purple-500\/10 { background: rgba(168, 85, 247, 0.1); }
.bg-blue-500\/10 { background: rgba(59, 130, 246, 0.1); }

.stat-info {
  min-width: 0;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 900;
  letter-spacing: -0.025em;
  color: white;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-label {
  font-size: 0.85rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: rgba(203, 213, 225, 0.7);
  margin-top: 0.125rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Filter Section */
.filter-section {
  padding: 1rem;
  border-radius: 1rem;
  border: 1px solid rgba(185, 121, 204, 0.15);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(8px);
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.filter-controls {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
  width: auto;
}

.search-wrapper {
  position: relative;
  width: 288px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1rem;
}

.search-input {
  width: 100%;
  padding: 0.5rem 1rem 0.5rem 2.25rem;
  border-radius: 0.75rem;
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(185, 121, 204, 0.2);
  font-size: 1rem;
  font-weight: 600;
  color: white;
  transition: all 0.3s;
}

.search-input:focus {
  outline: none;
  border-color: rgba(185, 121, 204, 0.5);
}

.search-input::placeholder {
  color: #94a3b8;
}

.select-wrapper {
  position: relative;
  width: 176px;
}

.filter-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border-radius: 0.75rem;
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(185, 121, 204, 0.2);
  font-size: 1rem;
  font-weight: 600;
  color: white;
  appearance: none;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-select:focus {
  outline: none;
  border-color: rgba(185, 121, 204, 0.5);
}

.select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 0.85rem;
  pointer-events: none;
}

.per-page-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.per-page-label {
  font-size: 0.9rem;
  color: #94a3b8;
  font-weight: 500;
}

.per-page-select {
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(185, 121, 204, 0.2);
  font-size: 1rem;
  font-weight: 700;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.per-page-select:focus {
  outline: none;
  border-color: rgba(185, 121, 204, 0.5);
}

/* Table Container */
.table-container {
  border-radius: 1rem;
  border: 1px solid rgba(185, 121, 204, 0.15);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  backdrop-filter: blur(8px);
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.table-wrapper {
  overflow-x: auto;
  width: 100%;
}

.data-table {
  width: 100%;
  text-align: left;
  border-collapse: collapse;
}

.table-header-row {
  border-bottom: 1px solid rgba(185, 121, 204, 0.1);
  background: rgba(0, 0, 0, 0.3);
}

.table-header-cell {
  padding: 1rem 1.5rem;
  font-size: 0.85rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #b979cc;
}

.table-header-number {
  width: 48px;
  text-align: center;
}

.table-header-center {
  text-align: center;
}

.table-header-right {
  text-align: right;
}

.table-body {
  display: table-row-group;
}

.empty-state {
  padding: 3rem 1.5rem;
  text-align: center;
  font-size: 1rem;
  color: #94a3b8;
  font-weight: 500;
}

.table-row {
  transition: all 0.3s;
  border-bottom: 1px solid rgba(185, 121, 204, 0.05);
}

.table-row:hover {
  background: rgba(255, 255, 255, 0.05);
}

.table-cell {
  padding: 1rem 1.5rem;
}

.table-cell-number {
  font-size: 1rem;
  font-family: monospace;
  font-weight: 700;
  color: #94a3b8;
  text-align: center;
}

.table-cell-center {
  text-align: center;
}

.table-cell-right {
  text-align: right;
}

.table-cell-count {
  font-family: monospace;
  font-size: 1rem;
  font-weight: 700;
  color: #cbd5e1;
}

.unit-name {
  font-weight: 700;
  color: #e2e8f0;
}

.unit-code {
  font-size: 0.85rem;
  color: #b979cc;
  font-weight: 500;
  letter-spacing: 0.025em;
  text-transform: uppercase;
  margin-top: 0.125rem;
}

.role-badge {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-twg {
  background: rgba(153, 13, 209, 0.2);
  color: #b979cc;
  border: 1px solid rgba(153, 13, 209, 0.3);
}

.badge-staff {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.badge-nontwg {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.badge-gad-office {
  background: rgba(236, 72, 153, 0.2);
  color: #f472b6;
  border: 1px solid rgba(236, 72, 153, 0.3);
}

.submission-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.submission-badge-active {
  background: rgba(153, 13, 209, 0.2);
  color: #b979cc;
  border: 1px solid rgba(153, 13, 209, 0.3);
}

.submission-badge-empty {
  background: rgba(0, 0, 0, 0.3);
  color: #94a3b8;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.view-details-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-size: 1rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: white;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  cursor: pointer;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(185, 121, 204, 0.15);
}

.view-details-btn:hover {
  color: #b979cc;
}

/* Pagination */
.pagination-container {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(185, 121, 204, 0.1);
  background: rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.pagination-info {
  font-size: 1rem;
  color: #94a3b8;
  font-weight: 500;
}

.pagination-highlight {
  font-weight: 700;
  color: white;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.pagination-btn {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 700;
  color: white;
  border: 1px solid rgba(185, 121, 204, 0.1);
  background: rgba(0, 0, 0, 0.3);
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  border-color: rgba(185, 121, 204, 0.4);
}

.pagination-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.pagination-page {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 700;
  transition: all 0.2s;
  cursor: pointer;
  color: #94a3b8;
  border: 1px solid rgba(185, 121, 204, 0.1);
  background: rgba(0, 0, 0, 0.2);
}

.pagination-page:hover {
  border-color: rgba(185, 121, 204, 0.3);
}

.pagination-page-active {
  color: white;
  background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: none;
}

/* Footer Note */
.footer-note {
  text-align: center;
  padding-top: 0.5rem;
}

.footer-text {
  font-size: 0.85rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  color: #94a3b8;
  text-transform: uppercase;
}

/* Scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
}

::-webkit-scrollbar-thumb {
  background: rgba(185, 121, 204, 0.3);
  border-radius: 99px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(153, 13, 209, 0.5);
}

/* Responsive */
@media (max-width: 1024px) {
  .twg-main-container {
    margin-left: 0;
  }
  
  .stats-section {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .filter-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-controls {
    width: 100%;
    flex-direction: column;
  }
  
  .search-wrapper,
  .select-wrapper {
    width: 100%;
  }
  
  .pagination-container {
    flex-direction: column;
  }
}

@media (max-width: 768px) {
  .twg-content-wrapper {
    padding: 20px;
  }
  
  .stats-section {
    grid-template-columns: 1fr;
  }
}
</style>
