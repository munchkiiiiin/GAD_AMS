<template>
  <main class="main-content">
    <div class="content-wrapper">
      
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-main-flex">
          <div>
            <div class="header-badge">
              <span class="material-symbols-outlined text-[15px]">payments</span>
              <span>College / TWG • Financial Monitoring</span>
            </div>
            <h1 class="page-title">Budget Utilization Monitoring</h1>
            <p class="page-subtitle">Track institutional mandate allocations, committed funds, actual disbursed expenditures, and review audit trails for documents submitted by your unit.</p>
          </div>
          <div class="header-actions">
            <!-- Fiscal Year Switcher -->
            <div class="fy-switcher-wrapper">
              <span class="material-symbols-outlined fy-icon">calendar_month</span>
              <select v-model="selectedFiscalYear" @change="fetchBudgetData" class="fy-select" title="Filter by Fiscal Year">
                <option value="all">All Fiscal Years</option>
                <option v-for="yr in availableYears" :key="yr" :value="yr">FY {{ yr }}</option>
              </select>
            </div>

            <!-- Analytics Toggle Button -->
            <button class="btn-analytics" @click="showAnalytics = !showAnalytics" :class="{ 'active': showAnalytics }" title="Toggle burn-rate analytics">
              <span class="material-symbols-outlined">{{ showAnalytics ? 'query_stats' : 'bar_chart' }}</span>
              <span>{{ showAnalytics ? 'Hide Analytics' : 'Analytics' }}</span>
            </button>

            <!-- Export Excel Button -->
            <button class="btn-export-excel" @click="exportToExcel" title="Export formatted compliance Excel (.xlsx)">
              <span class="material-symbols-outlined text-emerald-600">table_view</span>
              <span>Export Excel</span>
            </button>

            <button class="btn-refresh" @click="fetchBudgetData" :disabled="loading" title="Refresh live data">
              <span class="material-symbols-outlined" :class="{ 'spin': loading }">sync</span>
              <span>Refresh</span>
            </button>
            <button class="btn-toggle-all" @click="toggleExpandAll" title="Expand or collapse all audit trails">
              <span class="material-symbols-outlined">{{ allExpanded ? 'unfold_less' : 'unfold_more' }}</span>
              <span>{{ allExpanded ? 'Collapse All' : 'Expand All' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Summary Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-header">
            <div class="stat-icon-wrapper blue">
              <span class="material-symbols-outlined">account_balance</span>
            </div>
            <span class="stat-badge badge-normal">
              {{ selectedFiscalYear === 'all' ? 'All Years' : 'FY ' + selectedFiscalYear }}
            </span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">₱{{ formatNum(totalGadBudget) }}</h3>
            <p class="stat-label">Total GAD Budget</p>
            <div class="stat-sub-info">
              <span>{{ filteredRows.length }} Active Mandates</span>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-card-header">
            <div class="stat-icon-wrapper amber">
              <span class="material-symbols-outlined">hourglass_empty</span>
            </div>
            <span class="stat-badge badge-warning">Committed</span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">₱{{ formatNum(proposedBudget) }}</h3>
            <p class="stat-label">Proposed Budget (Committed ADs)</p>
            <div class="stat-sub-info">
              <span>Pending Accomplishment</span>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-card-header">
            <div class="stat-icon-wrapper green">
              <span class="material-symbols-outlined">trending_up</span>
            </div>
            <span class="stat-badge badge-high">Disbursed</span>
          </div>
          <div class="stat-content">
            <h3 class="stat-value mono">₱{{ formatNum(actualCost) }}</h3>
            <p class="stat-label">Actual Cost (Disbursed)</p>
            <div class="stat-sub-info">
              <span>Verified AR Expenditures</span>
            </div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-card-header">
            <div class="stat-icon-wrapper purple">
              <span class="material-symbols-outlined">pie_chart</span>
            </div>
            <span class="stat-badge" :class="Number(overallUtilizationRate) >= 75 ? 'badge-high' : Number(overallUtilizationRate) >= 40 ? 'badge-med' : 'badge-normal'">
              {{ Number(overallUtilizationRate) >= 75 ? 'Optimal' : Number(overallUtilizationRate) >= 40 ? 'On Track' : 'Starting' }}
            </span>
          </div>
          <div class="stat-content">
            <div class="flex items-baseline justify-between">
              <h3 class="stat-value mono">{{ overallUtilizationRate }}%</h3>
              <span class="text-xs text-purple-300 font-medium">% Utilization</span>
            </div>
            <!-- Utilization Gauge Mini Progress Bar -->
            <div class="util-gauge-track">
              <div 
                class="util-gauge-fill" 
                :style="{ width: Math.min(100, Math.max(0, Number(overallUtilizationRate) || 0)) + '%' }"
              ></div>
            </div>
            <div class="stat-sub-info mt-1.5 flex justify-between">
              <span>Remaining Balance:</span>
              <span class="font-bold text-slate-200 mono">₱{{ formatNum(Math.max(0, totalGadBudget - actualCost - proposedBudget)) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Visual Analytics & Burn-Rate Panel -->
      <div v-if="showAnalytics" class="analytics-container">
        <div class="analytics-header">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-purple-400 text-xl">insights</span>
            <h3 class="analytics-title">Expenditure Burn-Rate & Allocation Distribution</h3>
          </div>
          <span class="text-xs text-slate-400">Live breakdown of filtered mandates for {{ selectedFiscalYear === 'all' ? 'All Fiscal Years' : 'FY ' + selectedFiscalYear }}</span>
        </div>

        <div class="analytics-grid">
          <!-- Card 1: Quarterly Burn-Rate Breakdown -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4 class="chart-card-title">Quarterly Expenditure Burn-Rate</h4>
              <p class="text-[11px] text-slate-400 m-0">Actual Disbursed Cost (ARs) grouped by quarter</p>
            </div>
            
            <div class="quarterly-bars-container">
              <div v-for="q in quarterlyStats" :key="q.label" class="quarter-col">
                <div class="quarter-bar-wrapper">
                  <div class="quarter-bar-fill" :style="{ height: q.pct + '%' }" :title="`${q.label}: ₱${formatNum(q.amount)}`">
                    <span v-if="q.pct > 22" class="quarter-bar-val mono">₱{{ formatCompactNum(q.amount) }}</span>
                  </div>
                </div>
                <span class="quarter-label">{{ q.label }}</span>
                <span class="quarter-subval mono">₱{{ formatCompactNum(q.amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: Spending by Classification (Client vs Org vs Attributed) -->
          <div class="chart-card">
            <div class="chart-card-header">
              <h4 class="chart-card-title">Allocation & Spending by Classification</h4>
              <p class="text-[11px] text-slate-400 m-0">Client-Focused vs. Org-Focused vs. Attributed</p>
            </div>

            <div class="classification-breakdown-list">
              <div v-for="c in classificationStats" :key="c.key" class="class-stat-row">
                <div class="class-stat-meta">
                  <span class="classification-pill" :class="c.pillClass">{{ c.label }}</span>
                  <div class="class-amounts mono">
                    <span class="text-emerald-400 font-bold">₱{{ formatCompactNum(c.disbursed) }}</span>
                    <span class="text-slate-400">/ ₱{{ formatCompactNum(c.allocated) }}</span>
                  </div>
                </div>
                <div class="class-progress-track">
                  <div class="class-progress-disbursed" :style="{ width: c.disbursedPct + '%' }" :title="`Disbursed: ${c.disbursedPct.toFixed(1)}%`"></div>
                  <div class="class-progress-committed" :style="{ width: c.committedPct + '%' }" :title="`Committed: ${c.committedPct.toFixed(1)}%`"></div>
                </div>
                <div class="class-stat-footer text-[11px] text-slate-400 flex justify-between">
                  <span>{{ c.disbursedPct.toFixed(1) }}% Disbursed</span>
                  <span>{{ c.mandateCount }} Mandates</span>
                  <span class="text-purple-300 font-semibold">₱{{ formatCompactNum(c.remaining) }} Left</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Search & Filters Toolbar -->
      <div class="filter-toolbar">
        <div class="search-box">
          <span class="material-symbols-outlined search-icon">search</span>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search mandate, activity, or GPB code..." 
            class="search-input"
          />
          <button v-if="searchQuery" class="clear-search" @click="searchQuery = ''">
            <span class="material-symbols-outlined text-[16px]">close</span>
          </button>
        </div>

        <div class="filter-group">
          <!-- Classification Filter -->
          <div class="select-wrapper">
            <select v-model="selectedClassification" class="filter-select">
              <option value="all">All Classifications</option>
              <option value="client">Client-Focused (CF)</option>
              <option value="org">Organization-Focused (OF)</option>
              <option value="attributed">Attributed Program (AP)</option>
            </select>
          </div>

          <!-- Office Filter -->
          <div class="select-wrapper" v-if="uniqueOffices.length > 0">
            <select v-model="selectedOffice" class="filter-select">
              <option value="all">All Responsible Units</option>
              <option v-for="office in uniqueOffices" :key="office" :value="office">{{ office }}</option>
            </select>
          </div>

          <!-- Document / Activity Filter -->
          <div class="select-wrapper">
            <select v-model="selectedHealth" class="filter-select">
              <option value="all">All Records</option>
              <option value="has_ads">Has Pending ADs</option>
              <option value="has_ars">Has Verified ARs</option>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <button 
            v-if="searchQuery || selectedClassification !== 'all' || selectedOffice !== 'all' || selectedHealth !== 'all'"
            @click="resetFilters" 
            class="btn-reset-filters"
            title="Reset all filters"
          >
            <span class="material-symbols-outlined text-[16px]">filter_alt_off</span>
            <span>Reset</span>
          </button>
        </div>

        <div class="results-count">
          Showing <b class="text-purple-300">{{ filteredRows.length }}</b> of {{ budgetRows.length }} Mandates
        </div>
      </div>

      <!-- Main Data Table Container -->
      <div class="table-container">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr class="table-header-row">
                <th class="table-header-cell col-expand"></th>
                <th class="table-header-cell col-number">#</th>
                <th class="table-header-cell col-unit text-left">Gender Issue / Mandate</th>
                <th class="table-header-cell col-activity text-left">GAD Activity</th>
                <th class="table-header-cell col-allocated">Budget</th>
                <th class="table-header-cell col-pending">Pending (ADs)</th>
                <th class="table-header-cell col-remaining">Remaining</th>
                <th class="table-header-cell col-actual-cost">Actual Cost (ARs)</th>
                <th class="table-header-cell col-actions text-center">Audit Trail</th>
              </tr>
            </thead>
            <tbody class="table-body">
              <tr v-if="loading && budgetRows.length === 0">
                <td colspan="9" class="empty-state">
                  <div class="flex flex-col items-center justify-center gap-2 py-8">
                    <span class="material-symbols-outlined text-4xl spin text-purple-400">sync</span>
                    <span class="text-slate-400 font-medium">Loading budget utilization & audit trail...</span>
                  </div>
                </td>
              </tr>

              <tr v-else-if="filteredRows.length === 0">
                <td colspan="9" class="empty-state">
                  <div class="flex flex-col items-center justify-center gap-2 py-8">
                    <span class="material-symbols-outlined text-4xl text-slate-500">search_off</span>
                    <span class="text-slate-300 font-semibold text-lg">No budget records found</span>
                    <span class="text-slate-400 text-sm">Try adjusting your search keywords or filter dropdowns.</span>
                    <button @click="resetFilters" class="btn-clear-empty mt-2">Clear Filters</button>
                  </div>
                </td>
              </tr>

              <!-- Data Rows -->
              <template v-else v-for="(row, index) in filteredRows" :key="row.id">
                <tr 
                  class="table-row cursor-pointer" 
                  :class="{ 'expanded-row-parent': isExpanded(row.id) }"
                  @click="toggleRow(row.id)"
                >
                  <!-- Expand Icon -->
                  <td class="table-cell cell-expand text-center">
                    <span class="expand-icon material-symbols-outlined" :class="{ 'rotated': isExpanded(row.id) }">
                      keyboard_arrow_right
                    </span>
                  </td>

                  <!-- Number -->
                  <td class="table-cell cell-number">
                    {{ index + 1 }}
                  </td>
                  
                  <!-- Mandate & Metadata -->
                  <td class="table-cell cell-unit">
                    <div class="unit-badges mb-1 flex items-center gap-1.5 flex-wrap">
                      <span class="classification-pill" :class="getClassificationClass(row.section)">
                        {{ getClassificationLabel(row.section) }}
                      </span>
                      <span class="unit-code">{{ row.unit_code }}</span>
                      <span v-if="row.responsible" class="office-pill" :title="'Responsible Unit: ' + row.responsible">
                        {{ row.responsible }}
                      </span>
                    </div>
                    <div class="unit-name">{{ row.mandate }}</div>
                  </td>

                  <!-- Activity -->
                  <td class="table-cell cell-activity">
                    <div class="activity-text">{{ row.activity || 'N/A' }}</div>
                  </td>

                  <!-- Allocated Budget -->
                  <td class="table-cell cell-allocated text-right">
                    <div class="cell-value mono font-semibold">₱{{ formatNum(row.allocated) }}</div>
                  </td>

                  <!-- Pending (ADs) -->
                  <td class="table-cell cell-pending text-right">
                    <div class="cell-value mono" :class="{ 'text-amber-400 font-semibold': row.pending_approved > 0 }">
                      ₱{{ formatNum(row.pending_approved) }}
                    </div>
                    <div v-if="row.pending_ads && row.pending_ads.length > 0" class="sub-badge text-amber-300/80">
                      {{ row.pending_ads.length }} approved {{ row.pending_ads.length === 1 ? 'AD' : 'ADs' }}
                    </div>
                  </td>

                  <!-- Remaining -->
                  <td class="table-cell cell-remaining text-right">
                    <div class="cell-value mono font-bold" :class="getRemainingClass(row.remaining)">
                      ₱{{ formatNum(row.remaining) }}
                    </div>

                    <!-- Segmented Utilization Progress Bar -->
                    <div class="segmented-progress-container mt-1.5" :title="`Disbursed: ₱${formatNum(row.actual_cost)} (${getSegmentPercentages(row).disbursed.toFixed(1)}%) | Committed: ₱${formatNum(row.pending_approved)} (${getSegmentPercentages(row).committed.toFixed(1)}%) | Remaining: ₱${formatNum(row.remaining)} (${getSegmentPercentages(row).remaining.toFixed(1)}%)`">
                      <div class="segmented-bar">
                        <div 
                          v-if="getSegmentPercentages(row).disbursed > 0" 
                          class="segment segment-disbursed" 
                          :style="{ width: getSegmentPercentages(row).disbursed + '%' }"
                        ></div>
                        <div 
                          v-if="getSegmentPercentages(row).committed > 0" 
                          class="segment segment-committed" 
                          :style="{ width: getSegmentPercentages(row).committed + '%' }"
                        ></div>
                        <div 
                          v-if="getSegmentPercentages(row).remaining > 0" 
                          class="segment segment-remaining" 
                          :style="{ width: getSegmentPercentages(row).remaining + '%' }"
                        ></div>
                      </div>
                      <div class="segmented-legend">
                        <span class="text-emerald-400">{{ getSegmentPercentages(row).disbursed.toFixed(0) }}% spent</span>
                        <span v-if="getSegmentPercentages(row).committed > 0" class="text-amber-400">{{ getSegmentPercentages(row).committed.toFixed(0) }}% pending</span>
                        <span class="text-slate-400">{{ getSegmentPercentages(row).remaining.toFixed(0) }}% left</span>
                      </div>
                    </div>
                  </td>

                  <!-- Actual Cost / Disbursed -->
                  <td class="table-cell cell-actual-cost text-right">
                    <div class="cell-value mono font-semibold" :class="{ 'text-emerald-400': row.actual_cost > 0 }">
                      ₱{{ formatNum(row.actual_cost) }}
                    </div>
                    <div v-if="row.completed_ars && row.completed_ars.length > 0" class="sub-badge text-emerald-300/80">
                      {{ row.completed_ars.length }} verified {{ row.completed_ars.length === 1 ? 'AR' : 'ARs' }}
                    </div>
                  </td>

                  <!-- Actions / Audit Trail Toggle -->
                  <td class="table-cell cell-actions text-center" @click.stop="toggleRow(row.id)">
                    <button class="btn-audit-toggle" :class="{ 'active': isExpanded(row.id) }">
                      <span class="material-symbols-outlined text-[16px]">
                        {{ isExpanded(row.id) ? 'folder_open' : 'folder' }}
                      </span>
                      <span>{{ isExpanded(row.id) ? 'Hide' : 'Audit Trail' }}</span>
                      <span v-if="(row.total_docs_count || 0) > 0" class="audit-counter">
                        {{ row.total_docs_count }}
                      </span>
                    </button>
                  </td>
                </tr>

                <!-- Collapsible Expenditure Audit Trail Drawer -->
                <tr v-if="isExpanded(row.id)" class="audit-drawer-row">
                  <td colspan="9" class="audit-drawer-cell">
                    <div class="audit-drawer-container">
                      
                      <!-- Drawer Header Strip -->
                      <div class="drawer-header-strip">
                        <div class="drawer-info">
                          <span class="drawer-chip-code">{{ row.unit_code }}</span>
                          <span class="drawer-mandate-title">{{ row.activity || row.mandate }}</span>
                          <span class="drawer-scope-pill">Unit Audit Trail</span>
                        </div>
                        <div class="drawer-financial-summary mono">
                          <span class="summary-item">
                            <span class="text-slate-400">Allocated:</span>
                            <span class="text-purple-300 font-bold">₱{{ formatNum(row.allocated) }}</span>
                          </span>
                          <span class="summary-sep">|</span>
                          <span class="summary-item">
                            <span class="text-slate-400">Disbursed (AR):</span>
                            <span class="text-emerald-400 font-bold">₱{{ formatNum(row.actual_cost) }}</span>
                          </span>
                          <span class="summary-sep">|</span>
                          <span class="summary-item">
                            <span class="text-slate-400">Committed (AD):</span>
                            <span class="text-amber-400 font-bold">₱{{ formatNum(row.pending_approved) }}</span>
                          </span>
                          <span class="summary-sep">|</span>
                          <span class="summary-item">
                            <span class="text-slate-400">Remaining Balance:</span>
                            <span class="font-bold" :class="getRemainingClass(row.remaining)">₱{{ formatNum(row.remaining) }}</span>
                          </span>
                        </div>
                      </div>

                      <!-- Drawer Visual Progress Bar -->
                      <div class="drawer-progress-wrapper">
                        <div class="segmented-bar drawer-bar">
                          <div 
                            v-if="getSegmentPercentages(row).disbursed > 0" 
                            class="segment segment-disbursed" 
                            :style="{ width: getSegmentPercentages(row).disbursed + '%' }"
                          >
                            <span v-if="getSegmentPercentages(row).disbursed >= 10" class="segment-label">{{ getSegmentPercentages(row).disbursed.toFixed(1) }}% Disbursed (₱{{ formatNum(row.actual_cost) }})</span>
                          </div>
                          <div 
                            v-if="getSegmentPercentages(row).committed > 0" 
                            class="segment segment-committed" 
                            :style="{ width: getSegmentPercentages(row).committed + '%' }"
                          >
                            <span v-if="getSegmentPercentages(row).committed >= 10" class="segment-label">{{ getSegmentPercentages(row).committed.toFixed(1) }}% Committed (₱{{ formatNum(row.pending_approved) }})</span>
                          </div>
                          <div 
                            v-if="getSegmentPercentages(row).remaining > 0" 
                            class="segment segment-remaining" 
                            :style="{ width: getSegmentPercentages(row).remaining + '%' }"
                          >
                            <span v-if="getSegmentPercentages(row).remaining >= 10" class="segment-label">{{ getSegmentPercentages(row).remaining.toFixed(1) }}% Remaining (₱{{ formatNum(row.remaining) }})</span>
                          </div>
                        </div>
                      </div>

                      <!-- Sub-Panels Grid (2 Columns: ADs vs ARs) -->
                      <div class="audit-panels-grid">
                        
                        <!-- Panel 1: Committed Pending Activity Designs -->
                        <div class="audit-panel ad-panel">
                          <div class="panel-header">
                            <div class="flex items-center gap-2">
                              <span class="material-symbols-outlined text-amber-400 text-[18px]">pending_actions</span>
                              <h4 class="panel-title">Approved Activity Designs (Committed Pending)</h4>
                            </div>
                            <span class="panel-badge-count amber">
                              {{ (row.pending_ads || []).length }} Pending
                            </span>
                          </div>

                          <div v-if="!row.pending_ads || row.pending_ads.length === 0" class="panel-empty">
                            <span class="material-symbols-outlined text-slate-500 text-3xl">task_alt</span>
                            <p>No pending Activity Designs submitted by your unit awaiting accomplishment for this mandate.</p>
                          </div>

                          <div v-else class="doc-list">
                            <div v-for="ad in row.pending_ads" :key="'ad-' + ad.id" class="doc-card">
                              <div class="doc-card-top">
                                <span class="doc-control-badge text-amber-300 bg-amber-900/30 border border-amber-500/30">
                                  {{ ad.control_number || ('AD-' + ad.id) }}
                                </span>
                                <span class="doc-amount mono font-bold text-amber-400">
                                  ₱{{ formatNum(ad.amount) }}
                                </span>
                              </div>
                              <div class="doc-title">{{ ad.title }}</div>
                              <div class="doc-footer">
                                <div class="doc-meta">
                                  <span v-if="ad.college" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">domain</span>
                                    <span>{{ ad.college }}</span>
                                  </span>
                                  <span v-if="ad.created_at" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                    <span>{{ formatDate(ad.created_at) }}</span>
                                  </span>
                                </div>
                                <button 
                                  @click.stop="navigateToDoc('AD', ad.id)"
                                  class="btn-preview-doc"
                                  title="View Activity Design"
                                >
                                  <span class="material-symbols-outlined text-[14px]">visibility</span>
                                  <span>View Proposal</span>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Panel 2: Disbursed Actual Accomplishment Reports -->
                        <div class="audit-panel ar-panel">
                          <div class="panel-header">
                            <div class="flex items-center gap-2">
                              <span class="material-symbols-outlined text-emerald-400 text-[18px]">verified</span>
                              <h4 class="panel-title">Completed Accomplishment Reports (Disbursed Actuals)</h4>
                            </div>
                            <span class="panel-badge-count green">
                              {{ (row.completed_ars || []).length }} Disbursed
                            </span>
                          </div>

                          <div v-if="!row.completed_ars || row.completed_ars.length === 0" class="panel-empty">
                            <span class="material-symbols-outlined text-slate-500 text-3xl">hourglass_empty</span>
                            <p>No verified Accomplishment Reports submitted by your unit recorded yet for this mandate.</p>
                          </div>

                          <div v-else class="doc-list">
                            <div v-for="ar in row.completed_ars" :key="'ar-' + ar.id" class="doc-card">
                              <div class="doc-card-top">
                                <span class="doc-control-badge text-emerald-300 bg-emerald-900/30 border border-emerald-500/30">
                                  {{ ar.control_number || ('AR-' + ar.id) }}
                                </span>
                                <span class="doc-amount mono font-bold text-emerald-400">
                                  ₱{{ formatNum(ar.amount) }}
                                </span>
                              </div>
                              <div class="doc-title">{{ ar.title }}</div>
                              <div class="doc-footer">
                                <div class="doc-meta">
                                  <span v-if="ar.college" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">domain</span>
                                    <span>{{ ar.college }}</span>
                                  </span>
                                  <span v-if="ar.created_at" class="meta-item">
                                    <span class="material-symbols-outlined text-[13px]">calendar_today</span>
                                    <span>{{ formatDate(ar.created_at) }}</span>
                                  </span>
                                </div>
                                <button 
                                  @click.stop="navigateToDoc('AR', ar.id)"
                                  class="btn-preview-doc"
                                  title="View Accomplishment Report"
                                >
                                  <span class="material-symbols-outlined text-[14px]">visibility</span>
                                  <span>View Report</span>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import XLSX from 'xlsx-js-style';
import Swal from 'sweetalert2';
import api from '../../api';

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const budgetRows = ref([]);
const totalGadBudget = ref(0);
const actualCost = ref(0);
const proposedBudget = ref(0);
const overallUtilizationRate = ref('0.0');
const loading = ref(false);
const showAnalytics = ref(false);

// Filter & Search states
const searchQuery = ref('');
const selectedClassification = ref('all');
const selectedOffice = ref('all');
const selectedHealth = ref('all');
const selectedFiscalYear = ref('2026');
const availableYears = ref(['2026']);

// Expandable drawer state
const expandedRows = ref([]);

// Direct document navigation
const navigateToDoc = (type, id) => {
  if (!id) return;
  const role = (user.value.role || user.value.user_role || 'college').toLowerCase();
  const baseRole = (role === 'admin') ? 'admin' : ((role === 'college' || role === 'twg' || role === 'non-twg') ? 'college' : 'staff');
  if (type === 'AD') {
    router.push(`/${baseRole}/ad-view/${id}`);
  } else {
    router.push(`/${baseRole}/ar-view/${id}`);
  }
};

const formatNum = (val) => {
  if (val === undefined || val === null) return '0.00';
  return Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch {
    return dateStr;
  }
};

const formatCompactNum = (val) => {
  const num = Number(val) || 0;
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'k';
  return num.toLocaleString();
};

const updateRowCalculations = (row) => {
  row.remaining = Math.max(0, (row.allocated || 0) - (row.utilized || 0) - (row.pending_approved || 0));
};

const getRemainingClass = (remaining) => {
  if (remaining <= 0) return 'remaining-critical';
  if (remaining < 20000) return 'remaining-warning';
  return 'remaining-healthy';
};

const getHealthLabel = (remaining) => {
  if (remaining <= 0) return 'Exhausted';
  if (remaining < 20000) return 'Low Balance';
  return 'Healthy';
};

const getSegmentPercentages = (row) => {
  const allocated = Number(row.allocated) || 0;
  if (allocated <= 0) return { disbursed: 0, committed: 0, remaining: 100 };
  
  const disbursed = Number(row.utilized ?? row.actual_cost) || 0;
  const committed = Number(row.pending_approved) || 0;
  
  const disbursedPct = Math.min(100, Math.max(0, (disbursed / allocated) * 100));
  const committedPct = Math.min(100 - disbursedPct, Math.max(0, (committed / allocated) * 100));
  const remainingPct = Math.max(0, 100 - disbursedPct - committedPct);
  
  return {
    disbursed: disbursedPct,
    committed: committedPct,
    remaining: remainingPct
  };
};


const getClassificationLabel = (section) => {
  const s = (section || '').toLowerCase();
  if (s === 'client') return 'Client-Focused';
  if (s === 'org') return 'Org-Focused';
  if (s === 'attributed') return 'Attributed';
  return 'General';
};

const getClassificationClass = (section) => {
  const s = (section || '').toLowerCase();
  if (s === 'client') return 'pill-client';
  if (s === 'org') return 'pill-org';
  if (s === 'attributed') return 'pill-attributed';
  return 'pill-general';
};

// Unique Responsible Offices for filter dropdown
const uniqueOffices = computed(() => {
  const set = new Set();
  budgetRows.value.forEach(r => {
    if (r.responsible && typeof r.responsible === 'string') {
      const parts = r.responsible.split(',').map(p => p.trim());
      parts.forEach(p => { if (p) set.add(p); });
    }
  });
  return Array.from(set).sort();
});

// Filtered data rows
const filteredRows = computed(() => {
  return budgetRows.value.filter(row => {
    // Keyword search
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase().trim();
      const matchMandate = (row.mandate || '').toLowerCase().includes(q);
      const matchActivity = (row.activity || '').toLowerCase().includes(q);
      const matchCode = (row.unit_code || '').toLowerCase().includes(q);
      const matchResp = (row.responsible || '').toLowerCase().includes(q);
      if (!matchMandate && !matchActivity && !matchCode && !matchResp) {
        return false;
      }
    }

    // Classification filter
    if (selectedClassification.value !== 'all') {
      if ((row.section || '').toLowerCase() !== selectedClassification.value.toLowerCase()) {
        return false;
      }
    }

    // Office filter
    if (selectedOffice.value !== 'all') {
      const resp = (row.responsible || '').toLowerCase();
      if (!resp.includes(selectedOffice.value.toLowerCase())) {
        return false;
      }
    }

    // Document Activity filter
    if (selectedHealth.value !== 'all') {
      if (selectedHealth.value === 'has_ads') {
        if (!row.pending_ads || row.pending_ads.length === 0) return false;
      } else if (selectedHealth.value === 'has_ars') {
        if (!row.completed_ars || row.completed_ars.length === 0) return false;
      }
    }

    return true;
  });
});

// Quarterly expenditure burn-rate statistics
const quarterlyStats = computed(() => {
  const quarters = [
    { label: 'Q1 (Jan-Mar)', amount: 0, pct: 0 },
    { label: 'Q2 (Apr-Jun)', amount: 0, pct: 0 },
    { label: 'Q3 (Jul-Sep)', amount: 0, pct: 0 },
    { label: 'Q4 (Oct-Dec)', amount: 0, pct: 0 },
  ];

  filteredRows.value.forEach(row => {
    (row.completed_ars || []).forEach(ar => {
      const d = ar.created_at ? new Date(ar.created_at) : null;
      if (d && !isNaN(d.getTime())) {
        const m = d.getMonth(); // 0 to 11
        const qIdx = Math.floor(m / 3);
        if (qIdx >= 0 && qIdx < 4) {
          quarters[qIdx].amount += (Number(ar.amount) || 0);
        }
      } else {
        quarters[0].amount += (Number(ar.amount) || 0);
      }
    });
  });

  const maxAmount = Math.max(...quarters.map(q => q.amount), 1);
  quarters.forEach(q => {
    q.pct = Math.min(100, Math.max(10, (q.amount / maxAmount) * 100));
  });

  return quarters;
});

// Classification statistics
const classificationStats = computed(() => {
  const map = {
    client: { key: 'client', label: 'Client-Focused (CF)', pillClass: 'pill-client', allocated: 0, disbursed: 0, committed: 0, remaining: 0, mandateCount: 0 },
    org: { key: 'org', label: 'Organization-Focused (OF)', pillClass: 'pill-org', allocated: 0, disbursed: 0, committed: 0, remaining: 0, mandateCount: 0 },
    attributed: { key: 'attributed', label: 'Attributed Program (AP)', pillClass: 'pill-attributed', allocated: 0, disbursed: 0, committed: 0, remaining: 0, mandateCount: 0 }
  };

  filteredRows.value.forEach(row => {
    const sec = (row.section || 'client').toLowerCase();
    const target = map[sec] || map.client;
    target.allocated += Number(row.allocated) || 0;
    target.disbursed += Number(row.actual_cost ?? row.utilized) || 0;
    target.committed += Number(row.pending_approved) || 0;
    target.remaining += Number(row.remaining) || 0;
    target.mandateCount += 1;
  });

  return Object.values(map).map(c => {
    const total = c.allocated > 0 ? c.allocated : 1;
    return {
      ...c,
      disbursedPct: Math.min(100, (c.disbursed / total) * 100),
      committedPct: Math.min(100, (c.committed / total) * 100)
    };
  });
});

const resetFilters = () => {
  searchQuery.value = '';
  selectedClassification.value = 'all';
  selectedOffice.value = 'all';
  selectedHealth.value = 'all';
};

// Expand / Collapse Drawer logic
const isExpanded = (id) => expandedRows.value.includes(id);

const toggleRow = (id) => {
  const index = expandedRows.value.indexOf(id);
  if (index > -1) {
    expandedRows.value.splice(index, 1);
  } else {
    expandedRows.value.push(id);
  }
};

const allExpanded = computed(() => {
  return filteredRows.value.length > 0 && expandedRows.value.length >= filteredRows.value.length;
});

const toggleExpandAll = () => {
  if (allExpanded.value) {
    expandedRows.value = [];
  } else {
    expandedRows.value = filteredRows.value.map(r => r.id);
  }
};



// Data Fetching
const fetchBudgetData = async () => {
  loading.value = true;
  try {
    const params = {};
    if (selectedFiscalYear.value && selectedFiscalYear.value !== 'all') {
      params.fiscal_year = selectedFiscalYear.value;
    }

    const [monitoringRes, summaryRes] = await Promise.all([
      api.get('college/budget-monitoring', { params }),
      api.get('budget/summary', { params })
    ]);

    if (monitoringRes.data) {
      const rawRows = Array.isArray(monitoringRes.data)
        ? monitoringRes.data
        : (monitoringRes.data.data || []);
      budgetRows.value = rawRows;
      budgetRows.value.forEach(row => {
        updateRowCalculations(row);
      });
      if (monitoringRes.data.available_years && Array.isArray(monitoringRes.data.available_years)) {
        availableYears.value = monitoringRes.data.available_years;
        if (selectedFiscalYear.value !== 'all' && !availableYears.value.includes(selectedFiscalYear.value) && availableYears.value.length > 0) {
          selectedFiscalYear.value = availableYears.value[0];
        }
      }
    }

    if (summaryRes.data && summaryRes.data.success) {
      const b = summaryRes.data.data;
      totalGadBudget.value = b.total_budget || 0;
      actualCost.value = b.total_utilized || 0;
      proposedBudget.value = b.total_pending_approved || 0;
      overallUtilizationRate.value = Number(b.utilization_rate || 0).toFixed(1);
    }
  } catch (err) {
    console.error('Error fetching budget data:', err);
  } finally {
    loading.value = false;
  }
};

// Export formatted compliance Excel (.xlsx) with institutional design & proper number formatting
const exportToExcel = () => {
  const rows = [];

  const fiscalYearLabel = selectedFiscalYear.value === 'all' ? 'ALL YEARS' : selectedFiscalYear.value;
  const currentDate = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  const userName = user.value.name || user.value.full_name || 'College / Implementing Unit';

  // 1. Header Title Banner (Rows 0 - 3)
  rows.push(['BENGUET STATE UNIVERSITY', '', '', '', '', '', '', '', '', '', '', '', '']);
  rows.push(['GENDER AND DEVELOPMENT (GAD) ACTIVITY MANAGEMENT SYSTEM', '', '', '', '', '', '', '', '', '', '', '', '']);
  rows.push([`BUDGET UTILIZATION AND EXPENDITURE MONITORING REPORT - FY ${fiscalYearLabel}`, '', '', '', '', '', '', '', '', '', '', '', '']);
  rows.push([`Generated on: ${currentDate} | Generated by: ${userName}`, '', '', '', '', '', '', '', '', '', '', '', '']);
  rows.push(['', '', '', '', '', '', '', '', '', '', '', '', '']); // Row 4 spacer

  // 2. Executive Financial Summary Cards (Rows 5 - 7)
  const remainingBudgetVal = Math.max(0, totalGadBudget.value - actualCost.value - proposedBudget.value);
  rows.push([
    'TOTAL GAD ALLOCATED BUDGET', '', '',
    'PROPOSED BUDGET (COMMITTED ADs)', '', '',
    'ACTUAL DISBURSED COST (ARs)', '', '',
    'REMAINING AVAILABLE BALANCE', '', '', ''
  ]);
  rows.push([
    Number(totalGadBudget.value) || 0, '', '',
    Number(proposedBudget.value) || 0, '', '',
    Number(actualCost.value) || 0, '', '',
    Number(remainingBudgetVal) || 0, '', '', ''
  ]);
  rows.push([
    `${filteredRows.value.length} Active Mandate(s)`, '', '',
    'Committed Proposals Pending Report', '', '',
    'Verified Disbursed Expenditures', '', '',
    `Overall Utilization Rate: ${overallUtilizationRate.value}%`, '', '', ''
  ]);
  rows.push(['', '', '', '', '', '', '', '', '', '', '', '', '']); // Row 8 spacer

  // 3. Mandate Data Table Header (Row 9)
  const tableHeaders = [
    '#',
    'GPB Code',
    'Classification',
    'Gender Issue / Mandate',
    'GAD Activity',
    'Responsible Unit / Office',
    'Allocated Budget (PHP)',
    'Pending Approved ADs (PHP)',
    'Remaining Balance (PHP)',
    'Actual Disbursed Cost (PHP)',
    'Utilization Rate',
    'Approved ADs Count',
    'Verified ARs Count'
  ];
  rows.push(tableHeaders);

  // 4. Data Rows
  let totalAlloc = 0;
  let totalPending = 0;
  let totalRem = 0;
  let totalDisbursed = 0;
  let totalAdCount = 0;
  let totalArCount = 0;

  filteredRows.value.forEach((r, idx) => {
    const alloc = Number(r.allocated) || 0;
    const pend = Number(r.pending_approved) || 0;
    const rem = Number(r.remaining) || 0;
    const disb = Number(r.actual_cost ?? r.utilized) || 0;
    const rate = alloc > 0 ? (disb / alloc) : 0;
    const adCnt = (r.pending_ads || []).length;
    const arCnt = (r.completed_ars || []).length;

    totalAlloc += alloc;
    totalPending += pend;
    totalRem += rem;
    totalDisbursed += disb;
    totalAdCount += adCnt;
    totalArCount += arCnt;

    rows.push([
      idx + 1,
      r.unit_code || `GPB-${r.id}`,
      getClassificationLabel(r.section),
      r.mandate || '',
      r.activity || '',
      r.responsible || '',
      alloc,
      pend,
      rem,
      disb,
      rate,
      adCnt,
      arCnt
    ]);
  });

  // 5. Total Summary Row
  const totalRowIdx = rows.length;
  const overallRateDecimal = totalAlloc > 0 ? (totalDisbursed / totalAlloc) : 0;
  rows.push([
    'TOTAL GAD FINANCIAL UTILIZATION',
    '',
    '',
    '',
    '',
    '',
    totalAlloc,
    totalPending,
    totalRem,
    totalDisbursed,
    overallRateDecimal,
    totalAdCount,
    totalArCount
  ]);

  const ws = XLSX.utils.aoa_to_sheet(rows);

  // Merges configuration
  ws['!merges'] = [
    // Banner rows
    { s: { r: 0, c: 0 }, e: { r: 0, c: 12 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: 12 } },
    { s: { r: 2, c: 0 }, e: { r: 2, c: 12 } },
    { s: { r: 3, c: 0 }, e: { r: 3, c: 12 } },

    // KPI Cards: Title
    { s: { r: 5, c: 0 }, e: { r: 5, c: 2 } },
    { s: { r: 5, c: 3 }, e: { r: 5, c: 5 } },
    { s: { r: 5, c: 6 }, e: { r: 5, c: 8 } },
    { s: { r: 5, c: 9 }, e: { r: 5, c: 12 } },

    // KPI Cards: Value
    { s: { r: 6, c: 0 }, e: { r: 6, c: 2 } },
    { s: { r: 6, c: 3 }, e: { r: 6, c: 5 } },
    { s: { r: 6, c: 6 }, e: { r: 6, c: 8 } },
    { s: { r: 6, c: 9 }, e: { r: 6, c: 12 } },

    // KPI Cards: Sub
    { s: { r: 7, c: 0 }, e: { r: 7, c: 2 } },
    { s: { r: 7, c: 3 }, e: { r: 7, c: 5 } },
    { s: { r: 7, c: 6 }, e: { r: 7, c: 8 } },
    { s: { r: 7, c: 9 }, e: { r: 7, c: 12 } },

    // Total Row Label
    { s: { r: totalRowIdx, c: 0 }, e: { r: totalRowIdx, c: 5 } }
  ];

  // Column Widths
  ws['!cols'] = [
    { wch: 6 },  // #
    { wch: 14 }, // GPB Code
    { wch: 22 }, // Classification
    { wch: 45 }, // Gender Issue / Mandate
    { wch: 45 }, // GAD Activity
    { wch: 28 }, // Responsible Office
    { wch: 22 }, // Allocated Budget
    { wch: 22 }, // Pending Approved ADs
    { wch: 22 }, // Remaining Balance
    { wch: 22 }, // Actual Disbursed Cost
    { wch: 16 }, // Utilization Rate
    { wch: 16 }, // Approved ADs Count
    { wch: 16 }  // Verified ARs Count
  ];

  // Row Heights
  ws['!rows'] = [
    { hpt: 26 }, // Title 1
    { hpt: 20 }, // Title 2
    { hpt: 22 }, // Title 3
    { hpt: 18 }, // Title 4
    { hpt: 10 }, // Spacer
    { hpt: 20 }, // Card Title
    { hpt: 28 }, // Card Value
    { hpt: 18 }, // Card Sub
    { hpt: 12 }, // Spacer
    { hpt: 28 }  // Table Header
  ];

  // Cell Styles
  const borderThin = {
    top: { style: 'thin', color: { rgb: 'CBD5E1' } },
    bottom: { style: 'thin', color: { rgb: 'CBD5E1' } },
    left: { style: 'thin', color: { rgb: 'CBD5E1' } },
    right: { style: 'thin', color: { rgb: 'CBD5E1' } }
  };

  const cardBorderPurple = {
    top: { style: 'thin', color: { rgb: 'C084FC' } },
    bottom: { style: 'thin', color: { rgb: 'C084FC' } },
    left: { style: 'thin', color: { rgb: 'C084FC' } },
    right: { style: 'thin', color: { rgb: 'C084FC' } }
  };
  const cardBorderAmber = {
    top: { style: 'thin', color: { rgb: 'FCD34D' } },
    bottom: { style: 'thin', color: { rgb: 'FCD34D' } },
    left: { style: 'thin', color: { rgb: 'FCD34D' } },
    right: { style: 'thin', color: { rgb: 'FCD34D' } }
  };
  const cardBorderEmerald = {
    top: { style: 'thin', color: { rgb: '6EE7B7' } },
    bottom: { style: 'thin', color: { rgb: '6EE7B7' } },
    left: { style: 'thin', color: { rgb: '6EE7B7' } },
    right: { style: 'thin', color: { rgb: '6EE7B7' } }
  };
  const cardBorderBlue = {
    top: { style: 'thin', color: { rgb: '93C5FD' } },
    bottom: { style: 'thin', color: { rgb: '93C5FD' } },
    left: { style: 'thin', color: { rgb: '93C5FD' } },
    right: { style: 'thin', color: { rgb: '93C5FD' } }
  };

  // Helper to apply styles to range
  const setRangeStyle = (rStart, rEnd, cStart, cEnd, styleObj, numFmt = null) => {
    for (let r = rStart; r <= rEnd; r++) {
      for (let c = cStart; c <= cEnd; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c });
        if (!ws[cellRef]) ws[cellRef] = { t: 's', v: '' };
        ws[cellRef].s = JSON.parse(JSON.stringify(styleObj));
        if (numFmt) {
          ws[cellRef].z = numFmt;
          ws[cellRef].s.numFmt = numFmt;
        }
      }
    }
  };

  // 1. Banner Styles
  setRangeStyle(0, 0, 0, 12, {
    font: { name: 'Calibri', sz: 14, bold: true, color: { rgb: 'FFFFFF' } },
    fill: { fgColor: { rgb: '2E1065' } },
    alignment: { horizontal: 'center', vertical: 'center' }
  });
  setRangeStyle(1, 1, 0, 12, {
    font: { name: 'Calibri', sz: 11, bold: true, color: { rgb: 'E9D5FF' } },
    fill: { fgColor: { rgb: '2E1065' } },
    alignment: { horizontal: 'center', vertical: 'center' }
  });
  setRangeStyle(2, 2, 0, 12, {
    font: { name: 'Calibri', sz: 12, bold: true, color: { rgb: 'FFFFFF' } },
    fill: { fgColor: { rgb: '3B0764' } },
    alignment: { horizontal: 'center', vertical: 'center' }
  });
  setRangeStyle(3, 3, 0, 12, {
    font: { name: 'Calibri', sz: 9, italic: true, color: { rgb: 'DDD6FE' } },
    fill: { fgColor: { rgb: '3B0764' } },
    alignment: { horizontal: 'center', vertical: 'center' }
  });

  // 2. KPI Cards Styles
  // Card 1 (Purple)
  setRangeStyle(5, 5, 0, 2, {
    font: { name: 'Calibri', sz: 9, bold: true, color: { rgb: '581C87' } },
    fill: { fgColor: { rgb: 'F3E8FF' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderPurple
  });
  setRangeStyle(6, 6, 0, 2, {
    font: { name: 'Calibri', sz: 15, bold: true, color: { rgb: '581C87' } },
    fill: { fgColor: { rgb: 'F3E8FF' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderPurple
  }, '"₱"#,##0.00');
  setRangeStyle(7, 7, 0, 2, {
    font: { name: 'Calibri', sz: 8.5, color: { rgb: '7E22CE' } },
    fill: { fgColor: { rgb: 'F3E8FF' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderPurple
  });

  // Card 2 (Amber)
  setRangeStyle(5, 5, 3, 5, {
    font: { name: 'Calibri', sz: 9, bold: true, color: { rgb: '92400E' } },
    fill: { fgColor: { rgb: 'FEF3C7' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderAmber
  });
  setRangeStyle(6, 6, 3, 5, {
    font: { name: 'Calibri', sz: 15, bold: true, color: { rgb: '92400E' } },
    fill: { fgColor: { rgb: 'FEF3C7' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderAmber
  }, '"₱"#,##0.00');
  setRangeStyle(7, 7, 3, 5, {
    font: { name: 'Calibri', sz: 8.5, color: { rgb: 'B45309' } },
    fill: { fgColor: { rgb: 'FEF3C7' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderAmber
  });

  // Card 3 (Emerald)
  setRangeStyle(5, 5, 6, 8, {
    font: { name: 'Calibri', sz: 9, bold: true, color: { rgb: '065F46' } },
    fill: { fgColor: { rgb: 'ECFDF5' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderEmerald
  });
  setRangeStyle(6, 6, 6, 8, {
    font: { name: 'Calibri', sz: 15, bold: true, color: { rgb: '065F46' } },
    fill: { fgColor: { rgb: 'ECFDF5' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderEmerald
  }, '"₱"#,##0.00');
  setRangeStyle(7, 7, 6, 8, {
    font: { name: 'Calibri', sz: 8.5, color: { rgb: '047857' } },
    fill: { fgColor: { rgb: 'ECFDF5' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderEmerald
  });

  // Card 4 (Blue)
  setRangeStyle(5, 5, 9, 12, {
    font: { name: 'Calibri', sz: 9, bold: true, color: { rgb: '1E40AF' } },
    fill: { fgColor: { rgb: 'EFF6FF' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderBlue
  });
  setRangeStyle(6, 6, 9, 12, {
    font: { name: 'Calibri', sz: 15, bold: true, color: { rgb: '1E40AF' } },
    fill: { fgColor: { rgb: 'EFF6FF' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderBlue
  }, '"₱"#,##0.00');
  setRangeStyle(7, 7, 9, 12, {
    font: { name: 'Calibri', sz: 8.5, color: { rgb: '2563EB' } },
    fill: { fgColor: { rgb: 'EFF6FF' } },
    alignment: { horizontal: 'center', vertical: 'center' },
    border: cardBorderBlue
  });

  // 3. Table Header Style (Row 9)
  setRangeStyle(9, 9, 0, 12, {
    font: { name: 'Calibri', sz: 10, bold: true, color: { rgb: 'FFFFFF' } },
    fill: { fgColor: { rgb: '4C1D95' } },
    alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
    border: {
      top: { style: 'thin', color: { rgb: '3B0764' } },
      bottom: { style: 'medium', color: { rgb: '3B0764' } },
      left: { style: 'thin', color: { rgb: '3B0764' } },
      right: { style: 'thin', color: { rgb: '3B0764' } }
    }
  });

  // 4. Data Rows Styles
  const startDataRow = 10;
  const numDataRows = filteredRows.value.length;
  for (let i = 0; i < numDataRows; i++) {
    const r = startDataRow + i;
    const bgRgb = i % 2 === 1 ? 'F8FAFC' : 'FFFFFF';
    const rowFill = { fgColor: { rgb: bgRgb } };

    // Col 0: #
    setRangeStyle(r, r, 0, 0, {
      font: { name: 'Calibri', sz: 10 },
      fill: rowFill,
      alignment: { horizontal: 'center', vertical: 'center' },
      border: borderThin
    });
    // Col 1: Code
    setRangeStyle(r, r, 1, 1, {
      font: { name: 'Calibri', sz: 10, bold: true, color: { rgb: '1E293B' } },
      fill: rowFill,
      alignment: { horizontal: 'center', vertical: 'center' },
      border: borderThin
    });
    // Col 2: Classification
    setRangeStyle(r, r, 2, 2, {
      font: { name: 'Calibri', sz: 10 },
      fill: rowFill,
      alignment: { horizontal: 'center', vertical: 'center' },
      border: borderThin
    });
    // Col 3: Mandate
    setRangeStyle(r, r, 3, 3, {
      font: { name: 'Calibri', sz: 9.5 },
      fill: rowFill,
      alignment: { horizontal: 'left', vertical: 'center', wrapText: true },
      border: borderThin
    });
    // Col 4: Activity
    setRangeStyle(r, r, 4, 4, {
      font: { name: 'Calibri', sz: 9.5 },
      fill: rowFill,
      alignment: { horizontal: 'left', vertical: 'center', wrapText: true },
      border: borderThin
    });
    // Col 5: Responsible
    setRangeStyle(r, r, 5, 5, {
      font: { name: 'Calibri', sz: 9.5 },
      fill: rowFill,
      alignment: { horizontal: 'left', vertical: 'center', wrapText: true },
      border: borderThin
    });
    // Cols 6-9: Currency Columns
    for (let c = 6; c <= 9; c++) {
      setRangeStyle(r, r, c, c, {
        font: { name: 'Calibri', sz: 10, color: { rgb: '0F172A' } },
        fill: rowFill,
        alignment: { horizontal: 'right', vertical: 'center' },
        border: borderThin
      }, '"₱"#,##0.00');
    }
    // Col 10: Utilization Rate (%)
    setRangeStyle(r, r, 10, 10, {
      font: { name: 'Calibri', sz: 10, bold: true, color: { rgb: '4338CA' } },
      fill: rowFill,
      alignment: { horizontal: 'center', vertical: 'center' },
      border: borderThin
    }, '0.00%');
    // Cols 11-12: Counts
    setRangeStyle(r, r, 11, 12, {
      font: { name: 'Calibri', sz: 10 },
      fill: rowFill,
      alignment: { horizontal: 'center', vertical: 'center' },
      border: borderThin
    });
  }

  // 5. Total Row Styles
  const totalBorder = {
    top: { style: 'thin', color: { rgb: '7C3AED' } },
    bottom: { style: 'double', color: { rgb: '5B21B6' } },
    left: { style: 'thin', color: { rgb: 'CBD5E1' } },
    right: { style: 'thin', color: { rgb: 'CBD5E1' } }
  };
  const totalFill = { fgColor: { rgb: 'EDE9FE' } };

  setRangeStyle(totalRowIdx, totalRowIdx, 0, 5, {
    font: { name: 'Calibri', sz: 11, bold: true, color: { rgb: '3B0764' } },
    fill: totalFill,
    alignment: { horizontal: 'left', vertical: 'center' },
    border: totalBorder
  });

  for (let c = 6; c <= 9; c++) {
    setRangeStyle(totalRowIdx, totalRowIdx, c, c, {
      font: { name: 'Calibri', sz: 11, bold: true, color: { rgb: '3B0764' } },
      fill: totalFill,
      alignment: { horizontal: 'right', vertical: 'center' },
      border: totalBorder
    }, '"₱"#,##0.00');
  }

  setRangeStyle(totalRowIdx, totalRowIdx, 10, 10, {
    font: { name: 'Calibri', sz: 11, bold: true, color: { rgb: '3B0764' } },
    fill: totalFill,
    alignment: { horizontal: 'center', vertical: 'center' },
    border: totalBorder
  }, '0.00%');

  setRangeStyle(totalRowIdx, totalRowIdx, 11, 12, {
    font: { name: 'Calibri', sz: 11, bold: true, color: { rgb: '3B0764' } },
    fill: totalFill,
    alignment: { horizontal: 'center', vertical: 'center' },
    border: totalBorder
  });

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Budget Monitoring');

  const fileName = `BSU_GAD_College_Budget_Utilization_Report_FY${selectedFiscalYear.value}_${new Date().toISOString().split('T')[0]}.xlsx`;
  XLSX.writeFile(wb, fileName);

  Swal.fire({
    title: 'Excel Export Complete',
    text: `Report saved as ${fileName}`,
    icon: 'success',
    timer: 2500,
    showConfirmButton: false
  });
};



onMounted(() => {
  const role = (user.value.role || user.value.user_role || '').toLowerCase();
  if (!user.value.id || !['twg', 'non-twg', 'college'].some(r => role.includes(r))) { 
    router.push('/login'); 
  } else { 
    fetchBudgetData(); 
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&display=swap');

.mono {
  font-family: 'IBM Plex Mono', monospace;
}

.main-content {
  padding: 0;
  flex-grow: 1;
  color: #cbd5e1;
}

.content-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Page Header */
.page-header {
  padding: 0 0.25rem;
}

.header-main-flex {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(147, 51, 234, 0.1);
  border: 1px solid rgba(147, 51, 234, 0.3);
  color: #7e22ce;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

.page-title {
  font-size: 1.85rem;
  font-weight: 900;
  letter-spacing: -0.025em;
  color: #0f172a; /* Deep crisp slate for light page background */
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  color: #475569; /* Slate 600 for high legibility */
  margin: 0;
  line-height: 1.5;
  max-width: 820px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.fy-switcher-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0 10px 0 32px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
}

.fy-switcher-wrapper:hover {
  border-color: #9333ea;
  box-shadow: 0 2px 6px rgba(147, 51, 234, 0.15);
}

.fy-icon {
  position: absolute;
  left: 10px;
  font-size: 16px;
  color: #7e22ce;
  pointer-events: none;
}

.fy-select {
  background: transparent;
  border: none;
  color: #1e293b;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 12px 8px 0;
  outline: none;
  cursor: pointer;
  appearance: auto;
}

.btn-refresh, .btn-toggle-all, .btn-export-excel, .btn-analytics {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #1e293b;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
}

.btn-refresh:hover, .btn-toggle-all:hover {
  background: #f8fafc;
  border-color: #9333ea;
  color: #7e22ce;
  box-shadow: 0 2px 6px rgba(147, 51, 234, 0.15);
}

.btn-export-excel:hover {
  background: #f0fdf4;
  border-color: #10b981;
  color: #047857;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.15);
}

.btn-analytics:hover, .btn-analytics.active {
  background: #f8fafc;
  border-color: #6366f1;
  color: #4338ca;
  box-shadow: 0 2px 6px rgba(99, 102, 241, 0.15);
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

/* Stats KPI Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  padding: 1.25rem;
  border-radius: 1rem;
  border: 1px solid rgba(192, 132, 252, 0.15);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25);
  background: linear-gradient(135deg, #13111f 0%, #1e1b2e 100%);
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(192, 132, 252, 0.35);
  box-shadow: 0 14px 20px -3px rgba(0, 0, 0, 0.35);
}

.stat-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.25rem;
}

.stat-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.badge-high {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.4);
}

.badge-med {
  background: rgba(192, 132, 252, 0.2);
  color: #d8b4fe;
  border: 1px solid rgba(192, 132, 252, 0.4);
}

.badge-warning {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.4);
}

.badge-normal {
  background: rgba(148, 163, 184, 0.15);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.25);
}

.stat-icon-wrapper {
  width: 38px;
  height: 38px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-bottom: 0.5rem;
}

.stat-icon-wrapper.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.stat-icon-wrapper.green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.stat-icon-wrapper.amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.stat-icon-wrapper.purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; }

.stat-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.2;
  margin: 0;
}

.stat-label {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
  margin-top: 0.35rem;
}

.stat-sub-info {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 0.35rem;
}

.util-gauge-track {
  width: 100%;
  height: 7px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
  margin-top: 8px;
}

.util-gauge-fill {
  height: 100%;
  background: linear-gradient(90deg, #9333ea 0%, #c084fc 60%, #10b981 100%);
  border-radius: 999px;
  transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Visual Analytics Container */
.analytics-container {
  background: #141120;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 14px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-6px); }
  to { opacity: 1; transform: translateY(0); }
}

.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(192, 132, 252, 0.15);
  flex-wrap: wrap;
  gap: 8px;
}

.analytics-title {
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

@media (max-width: 900px) {
  .analytics-grid {
    grid-template-columns: 1fr;
  }
}

.chart-card {
  background: #1a1628;
  border: 1px solid rgba(192, 132, 252, 0.12);
  border-radius: 12px;
  padding: 1.25rem;
}

.chart-card-header {
  margin-bottom: 1.25rem;
}

.chart-card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0 0 2px 0;
}

/* Quarterly Burn-Rate Chart */
.quarterly-bars-container {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 160px;
  padding-top: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.quarter-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 22%;
  height: 100%;
  justify-content: flex-end;
}

.quarter-bar-wrapper {
  width: 100%;
  max-width: 44px;
  height: 120px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.quarter-bar-fill {
  width: 100%;
  background: linear-gradient(180deg, #10b981 0%, #059669 100%);
  border-radius: 6px 6px 0 0;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 4px;
  transition: height 0.5s ease;
  min-height: 8px;
}

.quarter-bar-val {
  font-size: 9px;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
}

.quarter-label {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  margin-top: 8px;
  white-space: nowrap;
}

.quarter-subval {
  font-size: 10px;
  color: #34d399;
  font-weight: 700;
}

/* Classification Breakdown List */
.classification-breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.class-stat-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.class-stat-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.class-amounts {
  font-size: 11.5px;
  display: flex;
  gap: 6px;
}

.class-progress-track {
  display: flex;
  width: 100%;
  height: 8px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
}

.class-progress-disbursed {
  height: 100%;
  background: linear-gradient(90deg, #059669, #10b981);
  transition: width 0.5s ease;
}

.class-progress-committed {
  height: 100%;
  background: linear-gradient(90deg, #d97706, #f59e0b);
  transition: width 0.5s ease;
}

/* Filter Toolbar */
.filter-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: #141120;
  border: 1px solid rgba(192, 132, 252, 0.15);
  border-radius: 12px;
  padding: 12px 16px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  font-size: 20px;
  pointer-events: none;
}

.search-input {
  width: 100%;
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 8px;
  color: #ffffff;
  font-size: 13px;
  padding: 8px 36px 8px 38px;
  outline: none;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #c084fc;
  box-shadow: 0 0 0 2px rgba(192, 132, 252, 0.2);
}

.clear-search {
  position: absolute;
  right: 10px;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.clear-search:hover { color: #ffffff; }

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-select {
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 8px;
  color: #e2e8f0;
  font-size: 13px;
  padding: 8px 12px;
  outline: none;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:focus {
  border-color: #c084fc;
}

.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  padding: 7px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.btn-reset-filters:hover {
  background: rgba(239, 68, 68, 0.25);
  color: #ffffff;
}

.results-count {
  font-size: 12px;
  color: #94a3b8;
  white-space: nowrap;
}

/* Data Table */
.table-container {
  border-radius: 14px;
  border: 1px solid rgba(192, 132, 252, 0.15);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  background: #141120;
}

.table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1180px;
}

.col-expand { width: 44px; text-align: center; }
.col-number { width: 48px; text-align: center; }
.col-unit { width: 260px; }
.col-activity { width: 230px; }
.col-allocated { width: 135px; text-align: right; }
.col-pending { width: 135px; text-align: right; }
.col-remaining { width: 195px; text-align: right; }
.col-actual-cost { width: 145px; text-align: right; }
.col-actions { width: 110px; text-align: center; }

.table-header-row {
  border-bottom: 1px solid rgba(192, 132, 252, 0.15);
  background: #1a1628;
}

.table-header-cell {
  padding: 12px 14px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #c084fc;
}

.table-row {
  border-bottom: 1px solid rgba(192, 132, 252, 0.08);
  transition: background 0.15s ease;
}

.table-row:hover {
  background: rgba(192, 132, 252, 0.05);
}

.expanded-row-parent {
  background: rgba(192, 132, 252, 0.08) !important;
  border-bottom: 1px solid rgba(192, 132, 252, 0.25);
}

.table-cell {
  padding: 12px 14px;
  vertical-align: middle;
  font-size: 13px;
}

.expand-icon {
  font-size: 20px;
  color: #94a3b8;
  transition: transform 0.2s ease, color 0.2s ease;
}

.expand-icon.rotated {
  transform: rotate(90deg);
  color: #c084fc;
}

.cell-number {
  color: #94a3b8;
  font-weight: 600;
}

.classification-pill {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.03em;
}

.pill-client {
  background: rgba(59, 130, 246, 0.2);
  color: #93c5fd;
  border: 1px solid rgba(59, 130, 246, 0.35);
}

.pill-org {
  background: rgba(168, 85, 247, 0.2);
  color: #d8b4fe;
  border: 1px solid rgba(168, 85, 247, 0.35);
}

.pill-attributed {
  background: rgba(245, 158, 11, 0.2);
  color: #fde68a;
  border: 1px solid rgba(245, 158, 11, 0.35);
}

.pill-general {
  background: rgba(148, 163, 184, 0.15);
  color: #cbd5e1;
}

.unit-code {
  font-size: 10px;
  font-family: 'IBM Plex Mono', monospace;
  background: rgba(0, 0, 0, 0.4);
  color: #cbd5e1;
  padding: 2px 6px;
  border-radius: 4px;
}

.office-pill {
  font-size: 10px;
  font-weight: 600;
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #6ee7b7;
  padding: 2px 6px;
  border-radius: 4px;
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.unit-name {
  font-size: 12.5px;
  color: #e2e8f0;
  font-weight: 500;
  line-height: 1.4;
}

.activity-text {
  font-size: 12.5px;
  color: #cbd5e1;
  line-height: 1.4;
  font-weight: 500;
}

.cell-value {
  font-size: 13.5px;
}

.sub-badge {
  font-size: 11px;
  margin-top: 2px;
}

.remaining-healthy {
  color: #34d399;
}

.remaining-warning {
  color: #fbbf24;
}

.remaining-critical {
  color: #f87171;
}



/* Segmented Progress Bars */
.segmented-progress-container {
  width: 100%;
  min-width: 140px;
}

.segmented-bar {
  display: flex;
  width: 100%;
  height: 6px;
  border-radius: 999px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.08);
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.4);
}

.segment {
  height: 100%;
  transition: width 0.4s ease;
  position: relative;
}

.segment-disbursed {
  background: linear-gradient(90deg, #059669, #10b981);
}

.segment-committed {
  background: linear-gradient(90deg, #d97706, #f59e0b);
}

.segment-remaining {
  background: rgba(147, 51, 234, 0.25);
  border-left: 1px solid rgba(255, 255, 255, 0.1);
}

.segmented-legend {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 9.5px;
  font-family: 'IBM Plex Mono', monospace;
  margin-top: 3px;
}

/* Drawer Progress Bar */
.drawer-progress-wrapper {
  margin-top: 10px;
  margin-bottom: 4px;
}

.drawer-bar {
  height: 18px;
  border-radius: 6px;
  background: rgba(0, 0, 0, 0.4);
}

.drawer-bar .segment {
  display: flex;
  align-items: center;
  justify-content: center;
}

.segment-label {
  font-size: 9.5px;
  font-weight: 700;
  font-family: 'IBM Plex Mono', monospace;
  color: #ffffff;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
  white-space: nowrap;
  padding: 0 4px;
}

.btn-audit-toggle {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(192, 132, 252, 0.1);
  border: 1px solid rgba(192, 132, 252, 0.25);
  color: #d8b4fe;
  padding: 5px 9px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-audit-toggle:hover, .btn-audit-toggle.active {
  background: #c084fc;
  color: #0f172a;
  border-color: #c084fc;
}

.audit-counter {
  background: rgba(0, 0, 0, 0.35);
  color: #ffffff;
  padding: 1px 5px;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 700;
}

.btn-audit-toggle.active .audit-counter {
  background: #0f172a;
  color: #c084fc;
}

/* Collapsible Audit Drawer */
.audit-drawer-row {
  background: #0d0a17;
  border-bottom: 2px solid rgba(192, 132, 252, 0.2);
}

.audit-drawer-cell {
  padding: 0 !important;
}

.audit-drawer-container {
  padding: 16px 20px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.drawer-header-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #1a1628;
  border: 1px solid rgba(192, 132, 252, 0.2);
  border-radius: 10px;
  padding: 10px 14px;
  flex-wrap: wrap;
  gap: 10px;
}

.drawer-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.drawer-chip-code {
  font-size: 11px;
  font-family: 'IBM Plex Mono', monospace;
  font-weight: 700;
  background: #9333ea;
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 4px;
}

.drawer-mandate-title {
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
}

.drawer-scope-pill {
  font-size: 10.5px;
  font-weight: 600;
  background: rgba(192, 132, 252, 0.15);
  border: 1px solid rgba(192, 132, 252, 0.35);
  color: #d8b4fe;
  padding: 2px 7px;
  border-radius: 999px;
  letter-spacing: 0.02em;
}

.drawer-financial-summary {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  flex-wrap: wrap;
}

.summary-sep {
  color: rgba(255, 255, 255, 0.15);
}

.summary-item {
  display: inline-flex;
  gap: 5px;
}

/* 2-Column Audit Panels */
.audit-panels-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

@media (max-width: 900px) {
  .audit-panels-grid {
    grid-template-columns: 1fr;
  }
}

.audit-panel {
  background: #141120;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.audit-panel.ad-panel {
  border-color: rgba(245, 158, 11, 0.25);
}

.audit-panel.ar-panel {
  border-color: rgba(16, 185, 129, 0.25);
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.panel-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.panel-badge-count {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
}

.panel-badge-count.amber {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.panel-badge-count.green {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.panel-empty {
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #64748b;
  gap: 8px;
  font-size: 12px;
}

.doc-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 340px;
  overflow-y: auto;
  padding-right: 4px;
}

.doc-card {
  background: #1b172a;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 8px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  transition: all 0.2s;
}

.doc-card:hover {
  border-color: rgba(192, 132, 252, 0.3);
  background: #201b33;
}

.doc-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.doc-control-badge {
  font-size: 11px;
  font-family: 'IBM Plex Mono', monospace;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
}

.doc-amount {
  font-size: 13px;
}

.doc-title {
  font-size: 12.5px;
  color: #e2e8f0;
  font-weight: 500;
  line-height: 1.35;
}

.doc-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 4px;
  padding-top: 6px;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
}

.doc-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 11px;
  color: #94a3b8;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-preview-doc {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(192, 132, 252, 0.15);
  border: 1px solid rgba(192, 132, 252, 0.3);
  color: #d8b4fe;
  padding: 3px 8px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-preview-doc:hover {
  background: #c084fc;
  color: #0f172a;
}

.btn-clear-empty {
  background: #1e1b2e;
  border: 1px solid rgba(192, 132, 252, 0.3);
  color: #d8b4fe;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.btn-clear-empty:hover {
  background: rgba(192, 132, 252, 0.2);
}

/* Print / PDF Export Media Styles */
@media print {
  aside,
  nav,
  .header-actions,
  .filter-toolbar,
  .btn-audit-toggle,
  .btn-refresh,
  .btn-toggle-all,
  .btn-export-excel,
  .btn-analytics,
  .fy-switcher-wrapper {
    display: none !important;
  }

  body,
  .main-content,
  .content-wrapper {
    background: #ffffff !important;
    color: #0f172a !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .page-title {
    color: #0f172a !important;
    font-size: 18pt !important;
  }

  .page-subtitle {
    color: #475569 !important;
    font-size: 10pt !important;
  }

  .stats-grid {
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 8px !important;
    margin-bottom: 16px !important;
  }

  .stat-card {
    background: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: none !important;
    color: #0f172a !important;
    padding: 10px !important;
  }

  .stat-value {
    color: #0f172a !important;
    font-size: 13pt !important;
  }

  .stat-label {
    color: #475569 !important;
  }

  .table-container {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: none !important;
  }

  .data-table {
    width: 100% !important;
    color: #0f172a !important;
    min-width: unset !important;
  }

  .table-header-row {
    background: #f1f5f9 !important;
    border-bottom: 2px solid #94a3b8 !important;
  }

  .table-header-cell {
    color: #0f172a !important;
    font-size: 8.5pt !important;
    padding: 6px 8px !important;
  }

  .table-row {
    border-bottom: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    page-break-inside: avoid;
  }

  .table-cell {
    padding: 6px 8px !important;
    font-size: 9pt !important;
  }

  .unit-name,
  .activity-text,
  .cell-value {
    color: #0f172a !important;
  }

  .unit-code {
    background: #e2e8f0 !important;
    color: #334155 !important;
  }

  .analytics-container {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
    box-shadow: none !important;
  }

  .chart-card {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
  }

  .analytics-title,
  .chart-card-title {
    color: #0f172a !important;
  }
}
</style>
