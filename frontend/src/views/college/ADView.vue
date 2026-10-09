<template>
  <div style="width: 100%; overflow-x: hidden;">
    <div style="min-height: 100vh; width: 100%;">
  <main class="main-viewport">
    <div v-if="loading" class="loading-wrapper">
      <div class="loading-spinner"></div>
    </div>

    <div v-else-if="error" class="min-h-[60vh] flex items-center justify-center p-6">
      <div class="bg-black/80 backdrop-blur-3xl rounded-3xl border-2 border-red-500/40 max-w-md w-full text-center p-10 relative overflow-hidden flex flex-col items-center shadow-2xl shadow-red-900/20">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="w-24 h-24 rounded-full bg-red-500/20 border-2 border-red-500/50 flex items-center justify-center mb-6 relative z-10 shadow-lg shadow-red-500/20">
          <span class="material-symbols-outlined text-5xl text-red-400 drop-shadow-md" v-if="error.includes('Access Denied')">gpp_bad</span>
          <span class="material-symbols-outlined text-5xl text-red-400 drop-shadow-md" v-else>error</span>
        </div>
        <h2 class="text-3xl font-headline font-black text-white mb-3 relative z-10 tracking-tight drop-shadow-md">
          {{ error.includes('Access Denied') ? 'Access Restricted' : 'Error Loading Data' }}
        </h2>
        <p class="text-slate-200 font-body text-base font-medium mb-10 relative z-10 leading-relaxed px-2">
          {{ error }}
        </p>
        <button @click="router.back()" class="relative z-10 bg-red-600 hover:bg-red-500 text-white shadow-lg shadow-red-900/50 px-10 py-4 rounded-full font-label text-sm font-extrabold tracking-widest uppercase transition-all hover:-translate-y-1 active:translate-y-0 flex items-center gap-3 group">
          <span class="material-symbols-outlined text-base group-hover:-translate-x-1 transition-transform font-bold">arrow_back</span>
          Go Back
        </button>
      </div>
    </div>

    <div v-else class="page-container">
      <div class="layout-grid">
        <!-- LEFT SECTION - Design Preview -->
        <section class="flex-06 glass-card">
          <div class="report-header">
            <div class="meta-header">
              <div style="display: flex; gap: 8px; align-items: center;">
                <div class="status-badge-view" :class="getStatusClass(design.status)">
                  <span class="status-text">{{ formatStatus(design.status) }}</span>
                </div>
                <div v-if="design.revision_count > 0" class="status-badge-view" style="background: rgba(234,179,8,0.1); border-color: rgba(234,179,8,0.2); padding: 4px 10px;">
                  <span class="status-text" style="color: #facc15; font-size: 11px; font-weight: bold;">Rev: {{ design.revision_count }}</span>
                </div>
                <div v-if="design.modification_count > 0" class="status-badge-view" style="background: rgba(168,85,247,0.1); border-color: rgba(168,85,247,0.2); padding: 4px 10px;">
                  <span class="status-text" style="color: #c084fc; font-size: 11px; font-weight: bold;">Mod: {{ design.modification_count }}</span>
                </div>
              </div>
              <span class="control-number">{{ design.control || 'PENDING ASSIGNMENT' }}</span>
            </div>

            <h2 class="report-title">{{ design.activity_title }}</h2>

            <div class="info-grid">
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Activity Title</span>
                <span class="info-value-white">{{ design.activity_title }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Submitted By</span>
                <button 
                  type="button" 
                  @click="openProponentModal(design.user_id)" 
                  class="flex items-center gap-1.5 text-purple-300 hover:text-purple-200 hover:underline transition-colors cursor-pointer text-left font-semibold text-sm bg-transparent border-0 p-0"
                  title="Click to view proponent profile"
                >
                  <span>{{ design.submitter_name || 'N/A' }}</span>
                  <span class="material-symbols-outlined text-[15px] text-purple-400">account_circle</span>
                </button>
            </div>
            <div class="info-item">
                <span class="info-label">Office / Unit</span>
                <span class="info-value-purple">{{ design.office }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Date Submitted</span>
                <span class="info-value-white">{{ design.date || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value-white">Activity Design</span>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Activity Classification</span>
                <span class="info-value-white">{{ design.activity_classification || '---' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Form Type</span>
                <span class="info-value-white uppercase">{{ design.form_type_name || formatFormType(design.form_type) || '---' }}</span>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Gender Issue / GAD Mandate</span>
                <div v-if="design.gad_mandate" class="mandate-boxes">
                  <span v-for="(mandate, index) in design.gad_mandate.split(';;;')" :key="'m'+index" class="mandate-box">
                    {{ mandate.trim() }}
                  </span>
                </div>
                <span v-else class="info-value-white">---</span>
              </div>
              <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Cause of Gender Issue</span>
                <div v-if="design.gender_issue" class="mandate-boxes">
                  <span v-for="(issue, index) in design.gender_issue.split(';;;')" :key="'i'+index" class="mandate-box">
                    {{ issue.trim() }}
                  </span>
                </div>
                <span v-else class="info-value-white">---</span>
              </div>
            </div>
          </div>

          <div class="report-body">
            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">calendar_month</span>
                <h3 class="section-title">Schedule & Venue</h3>
              </div>
              <div class="grid-2">
                                <div class="full-width-info" style="grid-column: span 2;">
                  <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="flex-1 bg-[#1a1a2e] p-4 rounded-xl border border-pink-500/20 relative overflow-hidden group shadow-lg">
                      <div class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                      <label class="text-[10px] font-bold text-pink-400 uppercase tracking-wider block mb-2">Calculated Start Date</label>
                      <p class="text-white font-medium flex items-center gap-3 text-sm"><span class="material-symbols-outlined text-pink-500 bg-pink-500/10 p-1.5 rounded-lg">calendar_month</span> {{ formatDate(design.start_date) || 'Awaiting schedule...' }}</p>
                    </div>
                    <div class="flex-1 bg-[#1a1a2e] p-4 rounded-xl border border-purple-500/20 relative overflow-hidden group shadow-lg">
                      <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                      <label class="text-[10px] font-bold text-purple-400 uppercase tracking-wider block mb-2">Calculated End Date</label>
                      <p class="text-white font-medium flex items-center gap-3 text-sm"><span class="material-symbols-outlined text-purple-500 bg-purple-500/10 p-1.5 rounded-lg">event</span> {{ formatDate(design.end_date) || 'Awaiting schedule...' }}</p>
                    </div>
                  </div>
                  
                  <div v-if="design.schedules && design.schedules.length" class="schedules-container" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(185, 121, 204, 0.2); border-radius: 20px; padding: 24px; margin-bottom: 24px;">
                    <div class="flex justify-between items-center flex-wrap gap-4" style="cursor: pointer;" @click="isSchedulesExpanded = !isSchedulesExpanded">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="form-label !mb-0 flex items-center gap-2 text-purple-300" style="cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            Activity Schedules
                          </label>
                          <div style="display: flex; background: rgba(0,0,0,0.4); border-radius: 8px; padding: 4px; border: 1px solid rgba(185,121,204,0.3);">
                            <span :style="{ background: 'rgba(185, 121, 204, 0.2)', color: '#e9d5ff', padding: '4px 12px', borderRadius: '6px', fontSize: '11px', fontWeight: 'bold' }">
                              {{ design.schedule_type === 'staggered' ? 'Non Consecutive / Custom' : 'Consecutive Daily' }}
                            </span>
                          </div>
                      </div>
                      <button type="button" @click.stop="isSchedulesExpanded = !isSchedulesExpanded" style="background: rgba(185, 121, 204, 0.1); color: #e9d5ff; border: 1px solid rgba(185, 121, 204, 0.3); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        {{ isSchedulesExpanded ? 'Hide Schedules' : 'View Schedules' }} <span class="material-symbols-outlined" style="font-size: 18px;">{{ isSchedulesExpanded ? 'expand_less' : 'expand_more' }}</span>
                      </button>
                    </div>
                    
                    <transition name="fade">
                    <div v-if="isSchedulesExpanded" style="margin-top: 16px;">
                    <div v-for="(sch, index) in design.schedules" :key="index" style="display: flex; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 12px; background: rgba(0,0,0,0.3); padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(185,121,204,0.15); transition: all 0.3s;" class="hover:bg-white/5 hover:border-purple-400/30">
                      <div style="display: flex; align-items: center; gap: 12px; min-width: 140px;">
                        <span class="material-symbols-outlined text-pink-400 text-lg">calendar_today</span>
                        <span class="text-white font-medium text-sm">{{ formatDate(sch.schedule_date || sch.date) }}</span>
                      </div>
                      <div style="display: flex; align-items: center; gap: 12px; background: rgba(0,0,0,0.4); padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                        <span class="material-symbols-outlined text-purple-400 text-lg">schedule</span>
                        <span class="text-purple-100 font-mono text-sm tracking-wide">{{ formatTime(sch.start_time) }} - {{ formatTime(sch.end_time) }}</span>
                      </div>

                    </div>
                    </div>
                    </transition>
                  </div>
                  
                  <div v-else class="schedules-container" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(185, 121, 204, 0.2); border-radius: 20px; padding: 24px; margin-bottom: 24px;">
                    <div class="flex justify-between items-center flex-wrap gap-4" style="cursor: pointer;" @click="isSchedulesExpanded = !isSchedulesExpanded">
                      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                          <label class="form-label !mb-0 flex items-center gap-2 text-slate-300" style="cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                            Activity Schedules
                          </label>
                          <div style="display: flex; background: rgba(0,0,0,0.4); border-radius: 8px; padding: 4px; border: 1px solid rgba(255,255,255,0.05);">
                            <span style="background: rgba(255,255,255,0.1); color: #cbd5e1; padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: bold;">
                              Legacy Format
                            </span>
                          </div>
                      </div>
                      <button type="button" @click.stop="isSchedulesExpanded = !isSchedulesExpanded" style="background: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.1); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        {{ isSchedulesExpanded ? 'Hide Schedules' : 'View Schedules' }} <span class="material-symbols-outlined" style="font-size: 18px;">{{ isSchedulesExpanded ? 'expand_less' : 'expand_more' }}</span>
                      </button>
                    </div>
                    
                    <transition name="fade">
                    <div v-if="isSchedulesExpanded" style="display: flex; align-items: center; flex-wrap: wrap; gap: 16px; background: rgba(0,0,0,0.3); padding: 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-top: 16px;">
                      <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 200px;">
                        <span class="material-symbols-outlined text-slate-400 text-lg">calendar_month</span>
                        <span class="text-white font-medium text-sm">{{ formatDate(design.start_date) }} <span class="text-slate-500 mx-1">to</span> {{ formatDate(design.end_date) }}</span>
                      </div>
                      <div style="display: flex; align-items: center; gap: 12px; background: rgba(0,0,0,0.4); padding: 8px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                        <span class="material-symbols-outlined text-slate-400 text-lg">schedule</span>
                        <span class="text-slate-200 font-mono text-sm tracking-wide">{{ formatTime(design.start_time) }} - {{ formatTime(design.end_time) }}</span>
                      </div>
                      <div style="flex-basis: 100%; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(255,255,255,0.1);">
                        <span style="font-size: 10px; text-transform: uppercase; font-weight: bold; color: #94a3b8; margin-right: 8px;" class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">restaurant</span> Meals Needed:</span>
                        <span class="text-[11px] text-slate-400 italic">Not specified (Submitted prior to detailed schedules feature)</span>
                      </div>
                    </div>
                    </transition>
                  </div>
                </div>
                <div class="w-full flex flex-col md:flex-row gap-4 mt-6">
                  <div class="flex-1">
                    <label class="info-label">Venue</label>
                    <div v-if="design.venues_list && design.venues_list.length > 0" class="flex flex-col gap-3 mt-2">
                      <div v-for="v in design.venues_list" :key="v.venue_id" class="flex flex-col items-start pb-2 border-b border-white/5 last:border-0 last:pb-0">
                        <p class="info-value-white !mb-1">{{ v.venue_name }}</p>
                        <span :class="v.is_inside_bsu == 1 || v.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                          {{ v.is_inside_bsu == 1 || v.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                        </span>
                      </div>
                    </div>
                    <div v-else class="mt-2 pb-2">
                      <p class="info-value-white !mb-1">{{ design.venue }}</p>
                      <span :class="design.is_inside_bsu == 1 || design.is_inside_bsu === true ? 'venue-badge inside-bsu' : 'venue-badge outside-bsu'">
                        {{ design.is_inside_bsu == 1 || design.is_inside_bsu === true ? '🏫 Inside BSU' : '🌐 Outside BSU' }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-1">
                    <label class="info-label">Overall Expected Attendance (Auto-calculated)</label>
                    <div class="flex items-center gap-3 mt-2 pb-2">
                      <p class="text-white font-medium flex items-center gap-3 text-lg">{{ design.target_participants }} <span class="text-slate-400 font-normal text-sm">individuals</span></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">payments</span>
                <h3 class="section-title">Proposed Budgetary Requirements</h3>
              </div>
              <div v-if="parsedBudget.length" class="budget-groups-container">
                <div v-for="(venue, vIdx) in parsedBudget" :key="vIdx" class="venue-budget-container mb-6 bg-slate-900/40 border border-slate-700/50 rounded-2xl overflow-hidden shadow-lg">
                  <div @click="toggleVenueBudget(vIdx)" class="venue-budget-header cursor-pointer flex justify-between items-center p-4 bg-gradient-to-r from-slate-800 to-slate-900 hover:from-slate-700 hover:to-slate-800 transition-colors">
                    <div class="flex items-center gap-3">
                      <span class="material-symbols-outlined text-purple-400">location_on</span>
                      <h4 class="text-slate-200 font-bold text-lg m-0">{{ venue.venue_name }}</h4>
                    </div>
                    <div class="flex items-center gap-4">
                      <span class="text-pink-400 font-bold bg-pink-500/10 px-3 py-1 rounded-lg">₱{{ formatCurrency(venue.total) }}</span>
                      <span class="material-symbols-outlined text-slate-400 transition-transform duration-300" :class="{ 'rotate-180': venueExpandedState[vIdx] !== false }">expand_more</span>
                    </div>
                  </div>
                  
                  <div v-show="venueExpandedState[vIdx] !== false" class="venue-budget-content p-4 flex flex-col gap-4">
                    <div v-for="(group, gIdx) in venue.groups" :key="gIdx" class="budget-group-card">
                      <div class="budget-group-header">
                        <span class="budget-group-icon">{{ group.icon }}</span>
                        <span class="budget-group-title">{{ group.name }}</span>
                      </div>
                      <div class="budget-group-content">
                        <div v-for="(child, cIdx) in group.children" :key="cIdx" class="budget-row-item">
                          <div class="budget-row-header" style="display: grid; grid-template-columns: minmax(0, 1fr) 160px; align-items: flex-start; column-gap: 16px;">
                            <div class="budget-item-info">
                              <div class="budget-item-title" v-html="formatBudgetName(child.name)"></div>
                              <div v-if="child.formula || child.computation" style="font-size: 12px; color: #94a3b8; font-family: monospace; margin-top: 4px;">
                                {{ child.formula || child.computation }}
                              </div>
                              <div v-else-if="child.sub_item && child.name !== child.sub_item" style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                                {{ child.sub_item }}
                              </div>
                              <div v-if="child.othersBreakdown && child.othersBreakdown.length" class="budget-others-breakdown-container" style="width: 100%; margin-top: 8px;">
                                <div v-for="(other, otherIdx) in child.othersBreakdown" :key="otherIdx" class="budget-others-breakdown-row" style="padding: 6px 10px; background: rgba(0,0,0,0.2); border: 1px dashed rgba(255,255,255,0.1); border-radius: 6px; color: #cbd5e1; font-size: 12px;">
                                  {{ other.name || 'Unnamed Item' }}
                                </div>
                              </div>
                            </div>
                            <div class="budget-item-value">
                              <span class="budget-currency-symbol">₱</span>
                              <div class="budget-card-input-readonly">{{ formatCurrency(child.value) }}</div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="grand-total-banner-card mt-4">
                  <div class="grand-total-label-banner">Grand Total (PHP)</div>
                  <div class="grand-total-value-banner">
                    ₱{{ formatCurrency(grandTotal) }}
                  </div>
                </div>
              </div>
              <div v-else class="empty-budget-notice">
                No budgetary requirements were specified for this design.
              </div>
            </div>

            <div v-if="design.attachment" class="section-card">
              <div class="section-header-row">
                <span class="material-symbols-outlined icon-pink">description</span>
                <h3 class="section-title">Supporting Documents</h3>
              </div>
              <div class="doc-item">
                <div class="doc-info">
                  <span class="material-symbols-outlined doc-pdf-icon">picture_as_pdf</span>
                  <div>
                    <p class="doc-title">{{ design.attachment }}</p>
                    <p class="doc-meta">Document Reference</p>
                  </div>
                </div>
                <button @click="previewFile(design.attachment)" class="preview-btn">Preview</button>
              </div>
            </div>
          </div>
        </section>

        <!-- RIGHT SECTION - Assessment Sidebar -->
        <section class="flex-04-sidebar">
          <div class="assessment-card-custom">
            <div class="assessment-header">
              <div class="assessment-icon">📋</div>
              <div class="assessment-title">Assessment Record</div>
            </div>

            <div class="assessment-form">
              <div class="info-item mb-4">
                <span class="info-label">Assessment Date</span>
                <span class="info-value-white">{{ formatDate(design.assessment_date) || '---' }}</span>
              </div>

              <div class="info-item mb-4">
                <span class="info-label">Accomplishment Deadline</span>
                <span class="info-value-white">{{ formatDate(design.accomplishment_deadline) || '---' }}</span>
              </div>

                      <div class="info-item">
                <span class="info-label">Reviewer Remarks</span>
                <div class="read-only-remarks">
                  {{ design.remarks || 'No remarks provided for this design.' }}
                </div>
              </div>

              <div class="action-buttons">
                <!-- Add Modification Request logic here -->
                <div v-if="design.status === 'Approved' && design.modification_request_status === 'rejected'" style="margin-bottom: 1rem; border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; padding: 1rem; background: rgba(239,68,68,0.05);">
                    <div style="color: #ef4444; font-weight: bold; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;"><span class="material-symbols-outlined">cancel</span> Modification Request Rejected</div>
                    <div style="font-size: 0.85rem; color: #e2e8f0; line-height: 1.4;">
                      <strong>Reviewer's Note:</strong> {{ design.modification_remarks || 'No reason provided.' }}
                    </div>
                </div>
                <!-- Add Modification Request logic here -->
                <button v-if="design.status === 'Approved' && (!design.modification_request_status || design.modification_request_status === 'none' || design.modification_request_status === 'rejected')" @click="openModModal" class="btn-approve" style="margin-bottom: 10px; width: 100%;">
                  <span class="material-symbols-outlined" style="font-size: 1.2rem; margin-right: 4px;">edit_note</span> Request Modification
                </button>
                <div v-else-if="design.status === 'Approved' && design.modification_request_status === 'pending'" class="status-badge" style="background: rgba(245,158,11,0.2); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); text-align: center; margin-bottom: 10px; display: block; padding: 0.75rem; border-radius: 12px;">
                  Modification Requested
                </div>
                <button v-else-if="design.status === 'Approved' && design.modification_request_status === 'approved'" @click="router.push(`/college/ad-revision/${design.act_design_id}`)" class="btn-approve" style="margin-bottom: 10px; width: 100%;">
                  <span class="material-symbols-outlined" style="font-size: 1.2rem; margin-right: 4px;">edit</span> Modify Design
                </button>

                <button @click="handleTrash" class="btn-trash">
                  <span class="material-symbols-outlined">delete</span> MOVE TO TRASH
                </button>
                <button @click="router.back()" class="btn-back">
                  ← Back
                </button>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <PdfPreviewModal :isOpen="isPdfModalOpen" :fileUrl="pdfFileUrl" @close="closePdfModal" />

    <!-- Modification Request Modal -->
    <div v-if="isModModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3 class="modal-title">Request Modification</h3>
        <p class="modal-desc">Please provide a reason for requesting a modification to this approved design.</p>
        <textarea v-model="modRemarks" class="modal-input" rows="4" placeholder="Enter remarks..."></textarea>
        <div class="modal-actions">
          <button @click="closeModModal" class="btn-cancel">Cancel</button>
          <button @click="submitModRequest" class="btn-submit">Submit Request</button>
        </div>
      </div>
    </div>
    <!-- Proponent Profile Modal -->
    <ProponentProfileModal 
      :is-open="isProponentModalOpen" 
      :user-id="selectedProponentId" 
      @close="isProponentModalOpen = false" 
    />
  </main>
    </div>
  </div>
</template>

<script setup>

const formatBudgetName = (name) => {
  if (!name) return '';
  return name.replace(/(\([^)]+\))/g, '<span class="budget-item-subtext">$1</span>');
};

const parsedBudget = computed(() => {
  const d = design.value;
  if (!d || !d.act_design_id) return [];

  // If there are detailed budget items from the new multiple-venue system
  if (d.budget_items && Array.isArray(d.budget_items) && d.budget_items.length > 0) {
    const venuesMap = {};
    
    // Group by venue_id
    d.budget_items.forEach(item => {
      const vid = item.venue_id || 'Legacy';
      if (!venuesMap[vid]) {
        let vName = vid === 'Other' ? 'Other Venue' : `Venue ${vid}`;
        if (vid === 'Legacy') vName = 'General Budget';
        
        venuesMap[vid] = {
          venue_id: vid,
          venue_name: vName,
          items: [],
          totals: {
            meals: 0, snacks: 0, 
            venue: 0, accommodation: 0, equipment: 0, transportation: 0,
            pf: 0, tokens: 0, pf_pax: 0, tokens_pax: 0,
            materials: 0, others: 0
          },
          othersBreakdown: [],
          computations: {},
          cateringItems: []
        };
      }
      
      const vData = venuesMap[vid];
      const amt = Number(item.amount) || 0;
      
      if (item.formula || (typeof item.sub_item === 'string' && !item.sub_item.startsWith('{'))) {
         vData.computations[item.item_name] = item.formula || item.sub_item;
      }
      
      if (['Breakfast', 'Lunch', 'Dinner', 'AM Snack', 'PM Snack'].includes(item.item_name)) {
        vData.cateringItems.push({
          name: item.item_name,
          value: amt,
          computation: vData.computations[item.item_name],
          sub_item: item.sub_item
        });
        if (['Breakfast', 'Lunch', 'Dinner'].includes(item.item_name)) {
          vData.totals.meals += amt;
        } else {
          vData.totals.snacks += amt;
        }
      }
      else if (item.item_name === 'Meals') {
        vData.totals.meals += amt;
        try {
          const parsed = JSON.parse(item.sub_item);
          if (parsed && typeof parsed === 'object') vData.mealsPax = parsed;
        } catch(e) {}
      }
      else if (item.item_name === 'Snacks') {
        vData.totals.snacks += amt;
        try {
          const parsed = JSON.parse(item.sub_item);
          if (parsed && typeof parsed === 'object') vData.snacksPax = parsed;
        } catch(e) {}
      }
      else if (item.item_name === 'Function Room/Venue') vData.totals.venue += amt;
      else if (item.item_name === 'Accommodation') vData.totals.accommodation += amt;
      else if (item.item_name === 'Equipment Rental') vData.totals.equipment += amt;
      else if (item.item_name === 'Transportation') vData.totals.transportation += amt;
      else if (item.item_name === 'Professional Fee/Honoraria') {
        vData.totals.pf += amt;
        vData.totals.pf_pax += Number(item.pax || 0);
      }
      else if (item.item_name === 'Token/s') {
        vData.totals.tokens += amt;
        vData.totals.tokens_pax += Number(item.pax || 0);
      }
      else if (item.item_name === 'Materials and Supplies') vData.totals.materials += amt;
      else {
        vData.totals.others += amt;
        vData.othersBreakdown.push({
          name: item.item_name === 'Others' ? (item.sub_item || 'Unnamed Item') : item.item_name,
          amount: amt,
          computation: vData.computations[item.item_name]
        });
      }
    });

    const result = [];
    for (const vid in venuesMap) {
      const v = venuesMap[vid];
      
      const groups = [
        {
          name: 'Catering & Hospitality', icon: '🍽️',
          total: v.totals.meals + v.totals.snacks,
          children: v.cateringItems.length ? v.cateringItems : [
            { name: 'Meals', value: v.totals.meals, pax: v.mealsPax, computation: v.computations?.['Meals'] },
            { name: 'Snacks', value: v.totals.snacks, pax: v.snacksPax, computation: v.computations?.['Snacks'] }
          ]
        },
        {
          name: 'Venue & Logistics', icon: '🏛️',
          total: v.totals.venue + v.totals.accommodation + v.totals.equipment + v.totals.transportation,
          children: [
            { name: 'Function Room/Venue', value: v.totals.venue, computation: v.computations?.['Function Room/Venue'] },
            { name: 'Accommodation', value: v.totals.accommodation, computation: v.computations?.['Accommodation'] },
            { name: 'Equipment Rental', value: v.totals.equipment, computation: v.computations?.['Equipment Rental'] },
            { name: 'Transportation', value: v.totals.transportation, computation: v.computations?.['Transportation'] }
          ]
        },
        {
          name: 'Program & Speakers', icon: '🎤',
          total: v.totals.pf + v.totals.tokens,
          children: [
            { name: `Professional Fee/Honoraria ${v.totals.pf > 0 ? `(Number of Speakers: ${v.totals.pf_pax})` : ''}`, value: v.totals.pf, computation: v.computations?.['Professional Fee/Honoraria'] },
            { name: `Token/s ${v.totals.tokens > 0 ? `(Number of Recipients: ${v.totals.tokens_pax})` : ''}`, value: v.totals.tokens, computation: v.computations?.['Token/s'] }
          ]
        },
        {
          name: 'Materials & Miscellaneous', icon: '📦',
          total: v.totals.materials + v.totals.others,
          children: [
            { name: 'Materials and Supplies', value: v.totals.materials, computation: v.computations?.['Materials and Supplies'] },
            { name: 'Others', value: v.totals.others, othersBreakdown: v.othersBreakdown }
          ]
        }
      ];
      
      const venueTotal = groups.reduce((sum, g) => sum + g.total, 0);
      
      result.push({
        venue_id: v.venue_id,
        venue_name: v.venue_id === 'Legacy' ? 'General Budget (Legacy Format)' : `Venue: ${v.venue_id}`,
        isExpanded: false,
        groups: groups,
        total: venueTotal
      });
    }
    
    if (Array.isArray(d.venues_list)) {
      try {
        result.forEach(r => {
          if (r.venue_id !== 'Legacy' && r.venue_id !== 'Other') {
             const venue = d.venues_list.find(v => String(v.venue_id) === String(r.venue_id));
             r.venue_name = venue ? venue.venue_name : `Venue: ${r.venue_id}`;
          }
        });
      } catch(e){}
    }
    
    return result;
  }

  // LEGACY FORMAT FALLBACK
  const dbMeals = Number(d.meals_total || 0);
  const dbSnacks = Number(d.snacks_total || 0);
  const legacyMealsSnacks = Number(d.meals_and_snacks || 0);

  let mealsVal = 0;
  let snacksVal = 0;
  if (dbMeals === 0 && dbSnacks === 0 && legacyMealsSnacks > 0) {
      mealsVal = legacyMealsSnacks;
  } else {
      mealsVal = dbMeals;
      snacksVal = dbSnacks;
  }

  const dbMat = Number(d.materials_total || 0);
  let ob = [];
  if (d.materials_others_breakdown) {
    try { ob = JSON.parse(d.materials_others_breakdown); } catch(e){}
  }
  const dbOthers = Number(d.others_total) || ob.reduce((s, o) => s + Number(o.amount || 0), 0);
  const legacyMatOthers = Number(d.materials_and_supplies || 0);

  let matVal = 0;
  let othersVal = 0;
  if (dbMat === 0 && dbOthers === 0 && legacyMatOthers > 0) {
      matVal = legacyMatOthers;
  } else {
      matVal = dbMat;
      othersVal = dbOthers;
  }

  const items = [
    {
      name: 'Catering & Hospitality', icon: '🍽️',
      total: mealsVal + snacksVal,
      children: [
        { name: 'Meals', value: mealsVal },
        { name: 'Snacks', value: snacksVal }
      ]
    },
    {
      name: 'Venue & Logistics', icon: '🏛️',
      total: Number(d.function_room_venue || 0) + Number(d.accommodation || 0) + Number(d.equipment_rental || 0) + Number(d.transportation || 0),
      children: [
        { name: 'Function Room/Venue', value: Number(d.function_room_venue || 0) },
        { name: 'Accommodation', value: Number(d.accommodation || 0) },
        { name: 'Equipment Rental', value: Number(d.equipment_rental || 0) },
        { name: 'Transportation', value: Number(d.transportation || 0) }
      ]
    },
    {
      name: 'Program & Speakers', icon: '🎤',
      total: Number(d.professional_fee_honoria || 0) + Number(d.tokens || 0),
      children: [
        { name: `Professional Fee/Honoraria ${Number(d.professional_fee_honoria || 0) > 0 ? `(Number of Speakers: ${d.pf_pax || 0})` : ''}`, value: Number(d.professional_fee_honoria || 0) },
        { name: `Token/s ${Number(d.tokens || 0) > 0 ? `(Number of Recipients: ${d.tokens_pax || 0})` : ''}`, value: Number(d.tokens || 0) }
      ]
    },
    {
      name: 'Materials & Miscellaneous', icon: '📦',
      total: matVal + othersVal,
      children: [
        { name: 'Materials and Supplies', value: matVal },
        { name: 'Others', value: othersVal, othersBreakdown: ob }
      ]
    }
  ];
  
  return [
    {
      venue_id: 'Legacy',
      venue_name: 'General Budget',
      isExpanded: true,
      groups: items,
      total: items.reduce((s, g) => s + g.total, 0)
    }
  ];
});

const venueExpandedState = ref({});
const toggleVenueBudget = (venueIndex) => {
  venueExpandedState.value[venueIndex] = !venueExpandedState.value[venueIndex];
};

const grandTotal = computed(() => {
  return parsedBudget.value.reduce((sum, v) => sum + (Number(v.total) || 0), 0);
});

import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../api';
import Swal from 'sweetalert2';
import PdfPreviewModal from '../../components/PdfPreviewModal.vue';
import ProponentProfileModal from '../../components/ProponentProfileModal.vue';
import { useHolidays } from '../../utils/useHolidays';

const isProponentModalOpen = ref(false);
const selectedProponentId = ref(null);

const openProponentModal = (userId) => {
  if (!userId) return;
  selectedProponentId.value = userId;
  isProponentModalOpen.value = true;
};

const route = useRoute();
const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));
const design = ref({});
const { getWorkingDaysDiff } = useHolidays();
const isSchedulesExpanded = ref(false);
const loading = ref(true);
const error = ref(null);

const isModModalOpen = ref(false);
const modRemarks = ref('');

const openModModal = () => {
  isModModalOpen.value = true;
  modRemarks.value = '';
};

const closeModModal = () => {
  isModModalOpen.value = false;
};

const submitModRequest = async () => {
  if (!modRemarks.value || !modRemarks.value.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'Reason Required',
      text: 'Please provide a reason for requesting modification.',
      confirmButtonColor: '#f59e0b'
    });
    return;
  }
  try {
    Swal.fire({
      title: 'Processing Request',
      text: 'Please wait while we notify the staff and director...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    const res = await api.post(`activity-designs/${route.params.id}/request-modification`, { remarks: modRemarks.value });
    if (res.data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Modification requested successfully.',
        timer: 1500,
        showConfirmButton: false
      });
      closeModModal();
      fetchDesignDetails();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to request modification: ' + res.data.message
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Error submitting modification request.'
    });
  }
};

const handleTrash = async () => {
  const isArchived = design.value.is_archived == 1;
  if (!isArchived && (design.value.status !== 'Pending' || design.value.is_viewed_by_admin == 1)) {
    Swal.fire({
      icon: 'error',
      title: 'Action Denied',
      text: 'You cannot move this document to trash because it is already being processed or has been viewed by an admin.',
      confirmButtonColor: '#3085d6'
    });
    return;
  }
  const result = await Swal.fire({
    title: 'Move to Trash?',
    text: 'This document will be moved to the trash bin.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#334155',
    confirmButtonText: 'Yes, move it'
  });

  if (result.isConfirmed) {
    try {
      const response = await api.delete(`activity-designs/trash/${route.params.id}`);
      if (response.data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Moved to Trash',
          text: 'Document has been moved to trash.',
          timer: 1500,
          showConfirmButton: false
        });
        router.push(design.value.is_archived == 1 ? '/college/archive' : '/college/submitted-list');
      } else {
        throw new Error(response.data.message || 'Failed to move to trash');
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.message || 'An error occurred while moving to trash'
      });
    }
  }
};

const fetchDesignDetails = async () => {
  loading.value = true;
  try {
    const id = route.params.id;
    const response = await api.get(`activity-design/${id}`);
    if (response.data.success) design.value = response.data.data;
    else error.value = "Activity design not found.";
  } catch (err) {
    error.value = "Failed to load activity design.";
  } finally {
    loading.value = false;
  }
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '---';
const formatTime = (time) => {
  if (!time) return '---';
  const [h, m] = time.split(':');
  return `${h % 12 || 12}:${m} ${h >= 12 ? 'PM' : 'AM'}`;
};

const formatStatus = (status) => {
  if (!status) return 'Unknown';
  if (status.toLowerCase() === 'revision required') return 'For Revision';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const isNonConsecutive = (schedules) => {
  if (!schedules || schedules.length <= 1) return false;
  for (let i = 0; i < schedules.length - 1; i++) {
    const d1 = new Date(schedules[i].schedule_date || schedules[i].date);
    const d2 = new Date(schedules[i + 1].schedule_date || schedules[i + 1].date);
    const diffDays = Math.round(Math.abs((d2 - d1) / (1000 * 60 * 60 * 24)));
    if (diffDays !== 1) {
      const wdDiff = getWorkingDaysDiff(d1, d2);
      if (wdDiff !== 1) return true;
    }
    if (schedules[i].start_time !== schedules[i+1].start_time || schedules[i].end_time !== schedules[i+1].end_time) return true;
  }
  return false;
};


const formatFormType = (type) => {
  if (!type) return '---';
  const map = {
    'employee': 'Employee Training',
    'inset': 'INSET Training',
    'extension': 'Extension Program',
    'student': 'Student Activity'
  };
  return map[type] || type;
};

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s === 'pending') return 'pending';
  if (s === 'approved') return 'approved';
  if (s === 'completed' || s === 'archived') return 'completed';
  if (s === 'cancelled') return 'cancelled';
  if (s === 'revision required' || s === 'revision') return 'revision';
  if (s === 'disapproved') return 'disapproved';
  return 'completed';
};

const formatCurrency = (amt) => amt ? parseFloat(amt).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '0.00';

const isPdfModalOpen = ref(false);
const pdfFileUrl = ref('');

const previewFile = (fileName) => {
  if (!fileName) return;
  const base = (import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '') : 'https://gad-ams-2-1.onrender.com');
  const folder = Number(design.value.is_archived) === 1 ? 'archived' : 'drafts';
  pdfFileUrl.value = `${base}/api/files/${folder}/${fileName}`;
  isPdfModalOpen.value = true;
};

const closePdfModal = () => {
  isPdfModalOpen.value = false;
  pdfFileUrl.value = '';
};

onMounted(() => {
  if (!user.value.id || !['twg', 'non-twg'].includes(user.value.role)) router.push('/login');
  else fetchDesignDetails();
});
</script>

<style scoped>
.main-viewport { flex: 1; overflow-y: auto; background: transparent; }
.loading-wrapper { display: flex; justify-content: center; align-items: center; min-height: 400px; }
.error-container { max-width: 48rem; margin: 0 auto; padding: 2.5rem; }
.error-box { background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; padding: 1rem; border-radius: 0.75rem; }
.error-title { color: #ef4444; font-weight: 700; }
.error-message { color: #cbd5e1; font-size: 1.1rem; }
.error-back-btn { margin-top: 1rem; font-size: 1.1rem; font-weight: 700; color: #ef4444; background: transparent; border: none; cursor: pointer; }
.page-container { min-height: 100vh;  }
.glass-card { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); backdrop-filter: blur(24px); border-radius: 1.5rem; border: 1px solid rgba(185, 121, 204, 0.2); }

.layout-grid { display: flex; gap: 32px; padding: 2.5rem; max-width: 80rem; margin: 0 auto; }
.flex-06 { flex: 0.6; display: flex; flex-direction: column; overflow: hidden; }
.flex-04-sidebar { flex: 0.4; position: sticky; top: 20px; align-self: flex-start; }

.report-header { padding: 2rem; border-bottom: 1px solid rgba(185, 121, 204, 0.15); background: rgba(0, 0, 0, 0.2); }
.meta-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.report-title { font-size: 26px; color: white; line-height: 1.25; margin: 1rem 0; }
.status-badge-view { padding: 4px 12px; border-radius: 9999px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; }
.status-badge-view.completed { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); }
.status-badge-view.cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.status-badge-view.pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
.status-badge-view.approved { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
.status-badge-view.revision { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
.status-badge-view.disapproved { background: rgba(220, 38, 38, 0.15); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.3); }
.control-number { font-size: 11px; font-weight: 700; color: #b979cc; text-transform: uppercase; margin-left: 12px; font-family: monospace; }
.info-grid { display: flex; flex-wrap: wrap; gap: 24px; padding-top: 16px; border-top: 1px solid rgba(185, 121, 204, 0.1); }
.info-item { display: flex; flex-direction: column; }
.info-label { font-size: 10px; text-transform: uppercase; color: #cbd5e1; font-weight: 700; margin-bottom: 4px; }
.info-value-white { font-size: 14px; font-weight: 600; color: white; }
.info-value-purple { font-size: 14px; font-weight: 600; color: #b979cc; }
.report-body { padding: 2rem; }
.report-body > * + * { margin-top: 1.5rem; }
.section-card { background: rgba(0, 0, 0, 0.2); border-radius: 16px; padding: 24px; border: 1px solid rgba(185, 121, 204, 0.15); }
.section-header-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.section-title { font-weight: 800; font-size: 13px; text-transform: uppercase; color: #b979cc; }
.icon-pink { color: #b979cc; }
.text-sm-light { font-size: 1.1rem; color: #cbd5e1; font-weight: 500; }
.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.metric-box { background: rgba(0, 0, 0, 0.3); border-radius: 12px; padding: 16px; text-align: center; border: 1px solid rgba(185, 121, 204, 0.1); }
.metric-value { font-size: 24px; font-weight: 700; color: white; }
.metric-label { font-size: 10px; color: #cbd5e1; text-transform: uppercase; margin-top: 4px; }
.doc-item { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 16px; background: rgba(0, 0, 0, 0.3); border-radius: 12px; border: 1px solid rgba(185, 121, 204, 0.15); overflow-x: auto; }
.doc-info { display: flex; align-items: center; gap: 12px; }
.doc-pdf-icon { font-size: 1.875rem; color: #ef4444; }
.doc-title { font-size: 13px; font-weight: 700; color: white; white-space: nowrap; }
.doc-meta { font-size: 11px; color: #cbd5e1; margin-top: 2px; white-space: nowrap; }
.preview-btn { color: #b979cc; font-size: 11px; padding: 6px 16px; border-radius: 8px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); font-weight: 700; cursor: pointer; transition: all 0.2s; }
.preview-btn:hover { border-color: #b979cc; color: white; background: rgba(185, 121, 204, 0.1); }

.assessment-card-custom {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(185, 121, 204, 0.2);
}
.assessment-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(185, 121, 204, 0.15); }
.assessment-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; }
.assessment-title { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #b979cc; }
.assessment-form { display: flex; flex-direction: column; }

.read-only-remarks {
  width: 100%;
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 13px;
  background: rgba(0, 0, 0, 0.3);
  color: #cbd5e1;
  min-height: 100px;
  line-height: 1.5;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-content {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border: 1px solid rgba(185, 121, 204, 0.2);
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  padding: 2rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}
.modal-title {
  color: #e2e8f0;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  margin-top: 0;
}
.modal-desc {
  color: #94a3b8;
  font-size: 0.95rem;
  margin-bottom: 1.5rem;
}
.modal-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(185, 121, 204, 0.2);
  color: white;
  border-radius: 8px;
  padding: 1rem;
  font-size: 0.95rem;
  resize: vertical;
  margin-bottom: 1.5rem;
}
.modal-input:focus {
  outline: none;
  border-color: #b979cc;
  box-shadow: 0 0 0 2px rgba(185, 121, 204, 0.2);
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}
.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.15);
}
.btn-submit {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #b979cc 0%, #990dd1 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(185, 121, 204, 0.3);
}

.action-buttons { margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(185, 121, 204, 0.15); }
.btn-back { width: 100%; padding: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #cbd5e1; border-radius: 12px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(185, 121, 204, 0.15); cursor: pointer; transition: all 0.2s; }
.btn-back:hover { color: white; border-color: #b979cc; background: rgba(185, 121, 204, 0.05); }

.btn-approve { width: 100%; background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%); color: white; border: none; border-radius: 14px; padding: 14px; font-size: 12px; font-weight: 800; text-transform: uppercase; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; }
.btn-approve:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-approve:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(153, 13, 209, 0.25); }

.btn-trash {
  width: 100%;
  padding: 12px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  border-radius: 12px;
  font-weight: 800;
  text-transform: uppercase;
  font-size: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 0.75rem;
}
.btn-trash:hover { background: rgba(239, 68, 68, 0.2); border-color: #ef4444; color: #fca5a5; }
.btn-back:hover { color: white; border-color: #b979cc; background: rgba(185, 121, 204, 0.05); }

@media (max-width: 1024px) {
  .layout-grid { flex-direction: column; padding: 1rem; }
  .flex-06, .flex-055, .flex-04-sidebar, .flex-045-sidebar { flex: 1 !important; width: 100% !important; max-width: 100% !important; position: relative !important; top: 0 !important; }
}

@media (max-width: 768px) {
  .grid-2, .grid-3 { grid-template-columns: 1fr !important; }
  .info-grid { flex-direction: column !important; gap: 12px !important; }
}


/* CREATIVE BUDGET TABLE STYLES */
.budget-table-wrapper {
  overflow: hidden;
  border-radius: 16px;
  background: linear-gradient(145deg, rgba(30, 41, 59, 0.4) 0%, rgba(15, 23, 42, 0.6) 100%);
  border: 1px solid rgba(185, 121, 204, 0.25);
  box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(10px);
}

.budget-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  text-align: left;
}

.budget-table-header {
  background: linear-gradient(90deg, rgba(185, 121, 204, 0.2) 0%, rgba(185, 121, 204, 0.05) 100%);
}

.table-header-cell {
  padding: 16px 20px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #e2e8f0;
  border-bottom: 2px solid rgba(185, 121, 204, 0.4);
}

.budget-total-header {
  text-align: right;
}

.budget-table-row {
  transition: all 0.3s ease;
}

.budget-table-row:hover {
  background: rgba(185, 121, 204, 0.1);
  transform: scale(1.002);
}

.budget-table-row td {
  border-bottom: 1px solid rgba(185, 121, 204, 0.1);
}

.budget-table-row:last-child td {
  border-bottom: none;
}

.budget-item-name {
  padding: 16px 20px;
  font-size: 14px;
  font-weight: 600;
  color: #f8fafc;
}

.budget-item-subtext {
  display: inline-block;
  font-size: 11px;
  color: #94a3b8;
  margin-left: 8px;
  font-weight: 400;
  background: rgba(0,0,0,0.2);
  padding: 2px 8px;
  border-radius: 12px;
}

.budget-item-value-cell {
  padding: 16px 20px;
  text-align: right;
}

.budget-item-value {
  font-family: 'Courier New', Courier, monospace;
  font-size: 15px;
  font-weight: 800;
  color: #fff;
  background: linear-gradient(135deg, rgba(185, 121, 204, 0.2) 0%, rgba(153, 13, 209, 0.2) 100%);
  padding: 6px 12px;
  border-radius: 8px;
  border: 1px solid rgba(185, 121, 204, 0.3);
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}

.budget-table-footer {
  background: linear-gradient(90deg, rgba(0,0,0,0.4) 0%, rgba(185, 121, 204, 0.15) 100%);
}

.budget-table-footer td {
  border-top: 2px solid rgba(185, 121, 204, 0.4);
}

.grand-total-label {
  padding: 20px;
  font-size: 13px;
  font-weight: 900;
  color: #b979cc;
  text-align: right;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.grand-total-value-white {
  padding: 20px;
  font-family: 'Courier New', Courier, monospace;
  font-size: 18px;
  font-weight: 900;
  color: #fff;
  text-align: right;
  text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}


.mandate-boxes {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 5px;
}
.mandate-box {
  background: rgba(185, 121, 204, 0.15);
  border: 1px solid rgba(185, 121, 204, 0.3);
  color: #f1f5f9;
  padding: 5px 12px;
  border-radius: 6px;
  font-size: 13px;
  line-height: 1.4;
}


/* GAD Grouped Budget Styles */
.budget-groups-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
}
.budget-group-card {
  background: rgba(30, 41, 59, 0.45);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}
.budget-group-header {
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 12px;
  margin-bottom: 16px;
}
.budget-group-icon { font-size: 18px; }
.budget-group-title {
  font-size: 13px;
  font-weight: 700;
  color: #b979cc;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.budget-group-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.budget-row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}
.budget-row-item:last-child {
  padding-bottom: 0;
  border-bottom: none;
}
.budget-row-item.has-sub-options {
  flex-direction: column;
  align-items: flex-start;
  gap: 12px;
}
.budget-row-header {
  display: flex;
  justify-content: space-between;
  width: 100%;
  align-items: center;
}
.budget-sub-options-container {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  padding-left: 8px;
  margin-top: -4px;
}
.budget-read-only-checkbox {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: not-allowed;
  opacity: 0.9;
}
.budget-checkbox-disabled {
  accent-color: #b979cc;
  width: 15px;
  height: 15px;
}
.budget-checkbox-label-text {
  font-size: 13px;
  color: #cbd5e1;
}
.budget-item-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex-grow: 1;
}
.budget-item-title {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 14px;
}
.budget-item-value {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 160px;
  flex-shrink: 0;
  justify-content: flex-end;
}
.budget-currency-symbol {
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}
.budget-card-input-readonly {
  color: #ffffff;
  font-size: 14px;
  padding: 8px 12px;
  width: 100%;
  text-align: right;
  font-weight: 600;
}
.grand-total-banner-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(185, 121, 204, 0.1) 0%, rgba(153, 13, 209, 0.1) 100%);
  border: 1px solid rgba(185, 121, 204, 0.3);
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 4px 15px -3px rgba(185, 121, 204, 0.1);
}
.grand-total-label-banner {
  font-size: 13px;
  font-weight: 700;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.grand-total-value-banner {
  font-size: 20px;
  font-weight: 800;
  color: #b979cc;
  text-shadow: 0 0 10px rgba(185, 121, 204, 0.2);
}
.venue-badge {
  display: inline-block;
  margin-top: 6px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.03em;
}
.venue-badge.inside-bsu {
  background: rgba(56, 189, 248, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
}
.venue-badge.outside-bsu {
  background: rgba(251, 146, 60, 0.15);
  color: #fb923c;
  border: 1px solid rgba(251, 146, 60, 0.3);
}
@media (max-width: 768px) {
  .budget-row-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .budget-item-value {
    width: 100%;
    justify-content: flex-start;
  }
  .grand-total-banner-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .doc-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .doc-title {
    word-break: break-all;
    white-space: normal;
  }
  .doc-meta {
    word-break: break-all;
    white-space: normal;
  }
  .doc-info {
    min-width: 0;
    width: 100%;
  }
}
</style>
