<template>
    <div 
      ref="welcomeBanner"
      class="relative overflow-hidden bg-[#0f172a] p-5 md:p-8 rounded-[1.5rem] md:rounded-[2rem] shadow-xl border border-purple-500/20 mb-6 md:mb-8 mt-2 group"
      @mousemove="handleMouseMove"
      @mouseleave="handleMouseLeave"
    >
      <div 
        class="pointer-events-none absolute inset-0 transition-opacity duration-300 opacity-0 group-hover:opacity-100 z-0"
        :style="{
          background: `radial-gradient(600px circle at ${mouseX}px ${mouseY}px, rgba(168, 85, 247, 0.4), transparent 40%)`
        }"
      ></div>
      <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 md:w-64 h-48 md:h-64 bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-32 md:w-48 h-32 md:h-48 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
      <div class="relative z-10">
        <h1 class="text-2xl md:text-3xl font-headline font-bold text-white mb-2">Welcome, <span class="text-purple-300">{{ displayName }}</span>!</h1>
        <p class="text-white font-medium font-body text-sm md:text-lg max-w-2xl">Manage your college's GAD programs, submit activity designs, and upload accomplishment reports.</p>
      </div>
    </div>
    
  <div class="dashboard-grid">
    <div class="main-content-area">
      
      <section class="stats-section">
        <div v-for="stat in metricsStats" :key="stat.label" class="stat-card">
            <div class="stat-card-inner">
            <div :class="['stat-icon', stat.bgClass]">
                <span class="material-symbols-outlined stat-icon-symbol" :class="stat.iconColor">{{ stat.icon }}</span>
            </div>
            <div class="stat-info">
                <h3 class="stat-value">{{ stat.value }}</h3>
                <p class="stat-label">{{ stat.label }}</p>
            </div>
            </div>
        </div>
      </section>

      <div class="table-card min-w-0">
        <div class="table-header-section">
          <h3 class="table-title">Recent Pending Activities</h3>
          <router-link to="/college/submit">
            <button class="new-submission-btn mt-2 sm:mt-0">
              <span class="material-symbols-outlined">add</span>
              New Submission
            </button>
          </router-link>
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="data-table">
            <thead>
              <tr class="table-header-row">
                <th class="table-header-cell">Activity Title</th>
                <th class="table-header-cell">Office / Unit</th>
                <th class="table-header-cell">Type</th>
                <th class="table-header-cell">Status</th>
                <th class="table-header-cell">Date Submitted</th>
              </tr>
            </thead>
            <tbody class="table-body">
              <tr v-if="submissions.length === 0" class="empty-row">
                <td colspan="5" class="empty-cell">
                  <div class="empty-content">
                    <span class="material-symbols-outlined empty-icon">history_edu</span>
                    <p>No pending activities found</p>
                  </div>
                </td>
              </tr>
              <tr v-else v-for="sub in submissions" :key="sub.id" @click="viewSubmission(sub)" class="clickable-row">
                <td class="activity-title-cell">{{ sub.title }}</td>
                <td class="office-cell">{{ sub.office }}</td>
                <td class="type-cell">
                  <span class="type-badge" :class="sub.type === 'design' ? 'type-badge-design' : 'type-badge-report'">
                    {{ sub.typeName }}
                  </span>
                </td>
                <td class="status-cell">
                  <span class="status-pill" :class="sub.statusClass">
                    {{ sub.status }}
                  </span>
                </td>
                <td class="date-cell">{{ sub.date }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="table-card min-w-0" style="margin-top: 1rem;">
        <div class="analytics-chart-container" style="background: rgba(0, 0, 0, 0.25); padding: 1.5rem; border-radius: 1rem; border: 1px solid rgba(147, 51, 234, 0.15); box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.1);">
          <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h4 style="color: #f8fafc; font-weight: 600; font-size: 1.25rem; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
              <span class="title-indicator" style="position: relative; height: 1.25rem; margin-right: 0;"></span>
              Your Gender-Disaggregated Data
            </h4>
            <select v-model="analyticsYear" @change="fetchAnalyticsData" style="background: rgba(15, 23, 42, 0.8); color: #f8fafc; border: 1px solid rgba(147, 51, 234, 0.3); border-radius: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.9rem; outline: none; cursor: pointer;">
              <option v-for="year in availableYears" :key="year" :value="year" style="background: #1e293b; color: white;">{{ year }}</option>
            </select>
          </div>
          
          <div v-if="!analyticsLoading">
            <!-- Yearly Summary -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; justify-content: center; flex-wrap: wrap;">
              <div style="flex: 1; min-width: 120px; background: rgba(147, 51, 234, 0.1); padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(147, 51, 234, 0.2); text-align: center;">
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Total Participants</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #f8fafc;">{{ yearlyTotal }}</div>
              </div>
              <div style="flex: 1; min-width: 120px; background: rgba(6, 182, 212, 0.1); padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(6, 182, 212, 0.2); text-align: center;">
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Total Male</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #22d3ee;">{{ yearlyMale }}</div>
              </div>
              <div style="flex: 1; min-width: 120px; background: rgba(192, 132, 252, 0.1); padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(192, 132, 252, 0.2); text-align: center;">
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Total Female</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: #c084fc;">{{ yearlyFemale }}</div>
              </div>
            </div>

            <!-- Chart -->
            <div style="height: 250px; position: relative; margin-bottom: 1.5rem;">
              <Bar :data="chartData" :options="chartOptions" />
            </div>

            <!-- Monthly Breakdown -->
            <div style="max-height: 250px; overflow-y: auto; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem;">
              <table style="width: 100%; text-align: left; border-collapse: collapse; color: #e2e8f0; font-size: 0.85rem;">
                <thead style="background: #1e293b; position: sticky; top: 0; z-index: 1; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                  <tr>
                    <th style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-weight: 600;">Month</th>
                    <th style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-weight: 600;">Total</th>
                    <th style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-weight: 600; color: #22d3ee;">Male</th>
                    <th style="padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-weight: 600; color: #c084fc;">Female</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(month, index) in monthlyData" :key="index" style="border-bottom: 1px solid rgba(255, 255, 255, 0.02);">
                    <td style="padding: 0.75rem 1rem;">{{ monthNames[index] }}</td>
                    <td style="padding: 0.75rem 1rem; font-weight: 600;">{{ month.male + month.female }}</td>
                    <td style="padding: 0.75rem 1rem; color: rgba(34, 211, 238, 0.9);">{{ month.male }}</td>
                    <td style="padding: 0.75rem 1rem; color: rgba(192, 132, 252, 0.9);">{{ month.female }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <div v-else style="height: 300px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
            <span class="material-symbols-outlined" style="animation: spin 1s linear infinite; font-size: 2rem;">refresh</span>
          </div>
        </div>
      </div>
    </div>

    <div class="sidebar-area min-w-0">
      
      <div class="calendar-card min-w-0 overflow-x-auto">
        <h3 class="widget-title">Calendar</h3>
        <div class="calendar-container">
          <div class="calendar-header">
            <span class="material-symbols-outlined nav-icon" @click="prevMonth">chevron_left</span>
            <span class="calendar-month">{{ currentMonthYear }}</span>
            <span class="material-symbols-outlined nav-icon" @click="nextMonth">chevron_right</span>
          </div>
          <div class="weekdays-grid">
            <span v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day" class="weekday-label">{{ day }}</span>
          </div>
          <div class="dates-grid">
            <div 
              v-for="(day, idx) in calendarDays" 
              :key="idx" 
              class="date-cell"
              :class="{ 
                'date-cell-past': day.isPast, 
                'date-cell-future': day.isFuture,
                'date-cell-today': day.isToday && !day.isRevision && !day.isARDue,
                'date-cell-revision': day.isRevision,
                'date-cell-ardue': day.isARDue
              }"
            >
              <template v-if="!day.blank">{{ day.date }}</template>
            </div>
          </div>
        </div>
      </div>

      <div class="deadlines-card min-w-0">
        <h3 class="widget-title">Upcoming Deadlines</h3>
        <div class="deadlines-list">
          <div v-if="deadlines.length === 0" class="empty-deadlines">
            <span class="material-symbols-outlined empty-icon-small">event_busy</span>
            <p>All clear! No upcoming deadlines.</p>
          </div>
          <div v-else v-for="deadline in deadlines" :key="deadline.id" class="deadline-item">
            <div class="deadline-header-row">
              <span :class="['deadline-badge', deadline.badgeClass]">
                {{ deadline.type }}
              </span>
              <span class="deadline-date-text">{{ deadline.date }}</span>
            </div>
            <h4 class="deadline-title">{{ deadline.title }}</h4>
            <p class="deadline-control-text">Control: {{ deadline.control }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const welcomeBanner = ref(null);
const mouseX = ref(-1000);
const mouseY = ref(-1000);

const handleMouseMove = (e) => {
  if (welcomeBanner.value) {
    const rect = welcomeBanner.value.getBoundingClientRect();
    mouseX.value = e.clientX - rect.left;
    mouseY.value = e.clientY - rect.top;
  }
};

const handleMouseLeave = () => {
  mouseX.value = -1000;
  mouseY.value = -1000;
};

import { useRouter } from 'vue-router';
import api from '../../api';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const router = useRouter();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const displayName = computed(() => {
  if (user.value.full_name && user.value.full_name.trim() !== '') {
    return user.value.full_name;
  }
  return user.value.role === 'twg' ? '(TWG)' : '(Non-TWG)';
});

const calendarBaseDate = ref(new Date());

const prevMonth = () => {
  const d = new Date(calendarBaseDate.value);
  d.setMonth(d.getMonth() - 1);
  calendarBaseDate.value = d;
};

const nextMonth = () => {
  const d = new Date(calendarBaseDate.value);
  d.setMonth(d.getMonth() + 1);
  calendarBaseDate.value = d;
};

const currentMonthYear = computed(() => {
  return calendarBaseDate.value.toLocaleString('default', { month: 'long', year: 'numeric' }).toUpperCase();
});

const calendarDays = computed(() => {
  const currentDate = calendarBaseDate.value;
  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();
  
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  
  const days = [];
  
  const prevMonthDays = new Date(year, month, 0).getDate();
  for (let i = 0; i < firstDay; i++) {
    days.push({ date: prevMonthDays - firstDay + i + 1, blank: true, isPast: true });
  }
  
  const today = new Date();
  for (let i = 1; i <= daysInMonth; i++) {
    const d = new Date(year, month, i);
    d.setHours(0,0,0,0);
    
    let isRevision = false;
    let isARDue = false;
    
    deadlines.value.forEach(dl => {
       if (dl.type.includes('Revision')) {
          if (d.getTime() === new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime()) {
             isRevision = true;
          }
       } else if (dl.type.includes('AR Due')) {
          const dlDate = new Date(dl.sortDate);
          dlDate.setHours(0,0,0,0);
          if (d.getTime() === dlDate.getTime()) {
             isARDue = true;
          }
       }
    });
    
    days.push({
      date: i,
      blank: false,
      isToday: d.getTime() === new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime(),
      isRevision,
      isARDue
    });
  }
  
  const remaining = 42 - days.length; 
  for(let i=1; i<=remaining; i++) {
     if (days.length % 7 === 0 && remaining < 7) break;
     days.push({ date: i, blank: true, isFuture: true });
  }
  
  return days;
});

const metricsStats = ref([
  { label: 'Pending Reviews', value: '0', icon: 'schedule', iconColor: 'text-amber-400', bgClass: 'bg-amber-500/10' },
  { label: 'Total Act Designs', value: '0', icon: 'description', iconColor: 'text-purple-400', bgClass: 'bg-purple-500/10' },
  { label: 'Total Acc Reports', value: '0', icon: 'analytics', iconColor: 'text-blue-400', bgClass: 'bg-blue-500/10' },
]);


const submissions = ref([]);
const deadlines = ref([]);

const getStatusClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s === 'approved' || s === 'verified' || s === 'completed') return 'status-approved';
  if (s === 'pending') return 'status-review';
  if (s === 'revision required' || s === 'revision' || s === 'for revision') return 'status-revision';
  if (s === 'disapproved') return 'status-disapproved';
  return 'status-review';
};

const formatStatus = (status) => {
  if (!status) return 'Unknown';
  const s = status.toLowerCase();
  if (s === 'revision required' || s === 'for revision' || s === 'revision') return 'For Revision';
  if (s === 'pending') return 'Pending';
  if (s === 'disapproved') return 'Disapproved';
  if (s === 'approved' || s === 'completed' || s === 'verified') return 'Approved';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const fetchSubmissions = async () => {
  try {
    const [adRes, arRes, archiveRes] = await Promise.all([
      api.get(`activity-designs/${user.value.id}`),
      api.get(`activity-reports/${user.value.id}`),
      api.get(`archives?user_id=${user.value.id}&role=${user.value.role}`)
    ]);
    
    let pendingCount = 0;
    let adCount = 0;
    let arCount = 0;
    
    let allSubmissions = [];

    // Helper function to check if status is pending or needs revision
    const isPendingOrRevision = (status) => {
      const s = (status || '').toLowerCase();
      return s === 'pending' || s === 'revision required' || s === 'for revision';
    };

    if (adRes.data.success) {
      const designs = adRes.data.data;
      adCount = designs.length;
      designs.forEach(d => {
        if (isPendingOrRevision(d.status)) {
          pendingCount++;
          allSubmissions.push({
            id: d.act_design_id,
            title: d.title || d.activity_title,
            office: d.office || user.value?.office || 'N/A',
            typeName: 'Activity Design',
            date: d.date || d.start_date || (d.created_at ? new Date(d.created_at).toLocaleDateString() : ''),
            status: formatStatus(d.status),
            statusClass: getStatusClass(d.status),
            type: 'design'
          });
        }
      });
    }

    if (arRes.data.success) {
      const reports = arRes.data.data;
      arCount = reports.length;
      reports.forEach(r => {
        if (isPendingOrRevision(r.status)) {
          pendingCount++;
          allSubmissions.push({
            id: r.id,
            title: r.title || r.activity_title,
            office: r.office || user.value?.office || 'N/A',
            typeName: 'Acc. Report',
            date: r.date || r.start_date || (r.created_at ? new Date(r.created_at).toLocaleDateString() : ''),
            status: formatStatus(r.status),
            statusClass: getStatusClass(r.status),
            type: 'report'
          });
        }
      });
    }

    if (archiveRes.data && archiveRes.data.success) {
      const archives = archiveRes.data.data || [];
      archives.forEach(item => {
        if (item.type === 'design') adCount++;
        else if (item.type === 'report') arCount++;
      });
    }

    metricsStats.value[0].value = pendingCount.toString();
    metricsStats.value[1].value = adCount.toString();
    metricsStats.value[2].value = arCount.toString();
    
    allSubmissions.sort((a, b) => b.id - a.id);
    submissions.value = allSubmissions.slice(0, 5);
  } catch (error) {
    console.error("Error fetching submissions for dashboard", error);
  }
};

const fetchDeadlines = async () => {
  try {
    const [adRes, arRes, archiveRes] = await Promise.all([
      api.get(`activity-designs/${user.value.id}`),
      api.get(`activity-reports/${user.value.id}`),
      api.get(`archives?user_id=${user.value.id}&role=${user.value.role || 'twg'}`)
    ]);
    
    let allDeadlines = [];
    const today = new Date();
    today.setHours(0,0,0,0);

    const formatDays = (targetDateStr) => {
      if (!targetDateStr) return 'ASAP';
      const target = new Date(targetDateStr);
      target.setHours(0,0,0,0);
      const diffTime = target - today;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      if (diffDays < 0) return 'Overdue';
      if (diffDays === 0) return 'Today';
      if (diffDays === 1) return 'Tomorrow';
      return `In ${diffDays} days`;
    };

    const designs = adRes.data.success ? adRes.data.data : [];
    const reports = arRes.data.success ? arRes.data.data : [];

    if (archiveRes.data && archiveRes.data.success) {
      const archives = archiveRes.data.data || [];
      archives.forEach(item => {
        if (item.type === 'design' && (item.status || '').toLowerCase() === 'approved') {
          designs.push({
             act_design_id: item.original_id,
             status: item.status,
             control: item.control,
             title: item.title,
             end_date: item.end_date,
             accomplishment_deadline: item.accomplishment_deadline
          });
        }
      });
    }

    designs.forEach(d => {
      const status = (d.status || '').toLowerCase();
      if (status === 'revision required' || status === 'for revision') {
         allDeadlines.push({
           id: 'ad_rev_' + d.act_design_id,
           type: 'AD Revision',
           badgeClass: 'badge-revision',
           date: 'ASAP',
           title: d.title || d.activity_title,
           control: d.control || 'N/A',
           sortDate: new Date(0)
         });
      }
      if (status === 'approved') {
         const hasReport = reports.some(r => r.act_design_id === d.act_design_id || (r.control_number && r.control_number === d.control) || (r.control && r.control === d.control));
         if (!hasReport && (d.end_date || d.accomplishment_deadline)) {
            let arDeadline;
            if (d.accomplishment_deadline) {
               arDeadline = new Date(d.accomplishment_deadline);
            } else {
               arDeadline = new Date(d.end_date);
               arDeadline.setDate(arDeadline.getDate() + 14);
            }
            allDeadlines.push({
               id: 'ar_due_' + d.act_design_id,
               type: 'AR Due',
               badgeClass: 'badge-submission',
               date: formatDays(arDeadline),
               title: d.title || d.activity_title,
               control: d.control || 'N/A',
               sortDate: arDeadline
            });
         }
      }
    });

    reports.forEach(r => {
      const status = (r.status || '').toLowerCase();
      if (status === 'revision required' || status === 'for revision') {
         allDeadlines.push({
           id: 'ar_rev_' + r.id,
           type: 'AR Revision',
           badgeClass: 'badge-revision',
           date: 'ASAP',
           title: r.title || r.activity_title,
           control: r.control || 'N/A',
           sortDate: new Date(0)
         });
      }
    });

    allDeadlines.sort((a, b) => a.sortDate - b.sortDate);
    deadlines.value = allDeadlines;
  } catch (err) {
    console.error('Error fetching deadlines', err);
  }
};

const loadDashboardData = async () => {
  await Promise.all([fetchSubmissions(), fetchDeadlines(), fetchAnalyticsData()]);
};

const getPHYear = () => {
  return new Date(new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" })).getFullYear();
};

const analyticsYear = ref(getPHYear());
const analyticsLoading = ref(true);
const monthlyData = ref([]);
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const availableYears = computed(() => {
  const startYear = 2026;
  const currentYear = getPHYear();
  const maxYear = Math.max(currentYear + 2, startYear + 2); // Show up to 2 years in advance
  const years = [];
  for (let y = startYear; y <= maxYear; y++) {
    years.push(y);
  }
  return years;
});

const yearlyTotal = computed(() => monthlyData.value.reduce((acc, curr) => acc + curr.male + curr.female, 0));
const yearlyMale = computed(() => monthlyData.value.reduce((acc, curr) => acc + curr.male, 0));
const yearlyFemale = computed(() => monthlyData.value.reduce((acc, curr) => acc + curr.female, 0));

const chartData = ref({
  labels: monthNames,
  datasets: [
    {
      label: 'Male',
      backgroundColor: '#06b6d4',
      data: []
    },
    {
      label: 'Female',
      backgroundColor: '#c084fc',
      data: []
    }
  ]
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    x: {
      grid: { color: 'rgba(255, 255, 255, 0.05)' },
      ticks: { color: '#94a3b8' }
    },
    y: {
      grid: { color: 'rgba(255, 255, 255, 0.05)' },
      ticks: { color: '#94a3b8' }
    }
  },
  plugins: {
    legend: {
      labels: { color: '#e2e8f0' }
    }
  }
};

const fetchAnalyticsData = async () => {
  analyticsLoading.value = true;
  try {
    const res = await api.get(`analytics/participants/user/${analyticsYear.value}/${user.value.id}`);
    if (res.data.success) {
      const data = res.data.data;
      monthlyData.value = data;
      chartData.value = {
        labels: chartData.value.labels,
        datasets: [
          {
            ...chartData.value.datasets[0],
            data: data.map(d => d.male)
          },
          {
            ...chartData.value.datasets[1],
            data: data.map(d => d.female)
          }
        ]
      };
    }
  } catch (error) {
    console.error('Failed to load personalized analytics', error);
  } finally {
    analyticsLoading.value = false;
  }
};

const viewSubmission = (sub) => {
  if (sub.type === 'design') {
    if (sub.status.toLowerCase() === 'for revision' || sub.status.toLowerCase() === 'revision required') {
      router.push(`/college/ad-revision/${sub.id}`);
    } else {
      router.push(`/college/ad-view/${sub.id}`);
    }
  } else {
    if (sub.status.toLowerCase() === 'for revision' || sub.status.toLowerCase() === 'revision required') {
      router.push(`/college/ar-revision/${sub.id}`);
    } else {
      router.push(`/college/ar-view/${sub.id}`);
    }
  }
};

onMounted(() => {
  if (user.value && user.value.id) {
    loadDashboardData();
  }
});
</script>

<style scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  min-width: 0;
}

@media (min-width: 768px) {
  .dashboard-grid {
    gap: 1.5rem;
  }
}

@media (min-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: 2.2fr 1fr;
    gap: 2rem;
  }
}

.main-content-area, .grid-main {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  min-width: 0;
}

.sidebar-area {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Base Card Layout Rules */
.table-card, .calendar-card, .deadlines-card {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border: 1px solid rgba(185, 121, 204, 0.15);
  padding: 1.25rem;
  border-radius: 1rem;
  box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.4);
}

@media (min-width: 768px) {
  .table-card, .calendar-card, .deadlines-card {
    padding: 1.75rem;
    border-radius: 1.25rem;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.601);
  }
}

.table-title, .widget-title {
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  letter-spacing: -0.01em;
}

.stats-section {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

  @media (max-width: 1024px) {
    .stats-section {
      grid-template-columns: 1fr;
    }
    .stat-card {
      min-width: auto;
    }
  }

.stat-card {
  padding: 1rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(147, 51, 234, 0.15);
  background: #16213e;
  transition: transform 0.2s ease;
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
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon-symbol {
  font-size: 1.125rem;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 800;
    color:#fff;
}

.stat-label {
    font-size: 0.9rem;
    font-weight: 400;
    color: #cbd5e1;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.new-submission-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.813rem;
  font-weight: 700;
  color: #ffffff;
  background: linear-gradient(135deg, #990dd1 0%, #b979cc 100%);
  border: none;
  border-radius: 0.75rem;
  padding: 0.625rem 1.25rem;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(153, 13, 209, 0.4);
  transition: all 0.2s ease;
}

.new-submission-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(153, 13, 209, 0.6);
  filter: brightness(1.1);
}

.table-header-section {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

  .table-responsive {
    overflow-x: auto;
    width: 100%;
  }

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.table-header-row {
  font-size: 0.60rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #b979cc;
  border-bottom: 1px solid rgba(185, 121, 204, 0.2);
}

.table-header-cell {
  padding: 0.75rem 1rem 1rem 1rem;
}

.clickable-row {
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  transition: background-color 0.2s ease;
}

.clickable-row:hover {
  background-color: rgba(185, 121, 204, 0.06);
}

.control-number-cell {
  padding: 1.25rem 1rem;
  font-weight: 700;
  color: #b979cc;
  font-size: 1.1rem;
}

.title-cell {
  padding: 1.25rem 1rem;
  color: #ffffff;
  font-size: 1.1rem;
}

.status-cell {
  padding: 1rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.6rem;
  border-radius: 0.375rem;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.status-approved { 
  background-color: rgba(34, 197, 94, 0.15); 
  color: #4ade80; 
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-review { 
  background-color: rgba(234, 179, 8, 0.15); 
  color: #facc15; 
  border: 1px solid rgba(234, 179, 8, 0.3);
}

.status-revision { 
  background-color: rgba(239, 68, 68, 0.15); 
  color: #f87171; 
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-disapproved { 
  background-color: rgba(239, 68, 68, 0.2); 
  color: #f87171; 
  border: 1px solid rgba(239, 68, 68, 0.4);
}

.actions-cell {
  padding: 1.25rem 1rem;
}

.text-right {
  text-align: right;
}

.view-button {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(185, 121, 204, 0.15);
  border-radius: 0.5rem;
  padding: 0.45rem;
  cursor: pointer;
  color: #cbd5e1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.view-button:hover {
  color: #ffffff;
  background: rgba(153, 13, 209, 0.2);
  border-color: #b979cc;
}

.view-icon {
  font-size: 1.2rem;
}

.empty-cell {
  padding: 5rem 2rem;
  text-align: center;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.empty-icon {
  font-size: 3rem;
  color: rgba(185, 121, 204, 0.3);
}

.empty-content p, .empty-deadlines p {
  color: #cbd5e1;
  font-size: 0.70rem;
  margin: 0;
  opacity: 0.7;
}

.calendar-card .widget-title {
  margin-bottom: 1.5rem;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.nav-icon {
  cursor: pointer;
  color: #b979cc;
}

.calendar-month {
  font-weight: 700;
  color: #fff;
}

.weekdays-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  margin-bottom: 0.75rem;
}

.weekday-label {
  font-size: 1rem;
  font-weight: 700;
  color: #b979ccc1;
}

.dates-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  text-align: center;
}

.date-cell {
  position: relative;
  padding: 0.5rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
}

.date-cell:hover {
  background: rgba(255, 255, 255, 0.05);
}

.date-cell-past {
  color: rgba(255, 255, 255, 0.2);
}

.date-cell-future {
  color: rgba(255, 255, 255, 0.2);
}

.date-cell-revision {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  color: #fff;
}

.date-cell-ardue {
  background: rgba(234, 179, 8, 0.2);
  border: 1px solid rgba(234, 179, 8, 0.5);
  color: #fff;
}

.date-cell-today {
  border: 1px solid rgba(147, 51, 234, 0.5);
  color: #c084fc;
}

/* Contextual Target Evaluation Logs */
.deadlines-card .widget-title {
  margin-bottom: 1.5rem;
}

.deadlines-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.empty-deadlines {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2.5rem 1rem;
}

.empty-icon-small {
  font-size: 2.25rem;
  color: rgba(185, 121, 204, 0.3);
}

.deadline-item {
  padding: 1rem;
  border-radius: 0.875rem;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(185, 121, 204, 0.1);
  transition: all 0.2s ease;
}

.deadline-item:hover {
  border-color: rgba(185, 121, 204, 0.3);
  background: rgba(0, 0, 0, 0.3);
}

.deadline-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.625rem;
}

.deadline-badge {
  padding: 0.15rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.688rem;
  font-weight: 700;
  text-transform: uppercase;
}

.badge-revision { background-color: rgba(239, 68, 68, 0.15); color: #f87171; }
.badge-submission { background-color: rgba(234, 179, 8, 0.15); color: #facc15; }

.deadline-date-text {
  font-size: 1rem;
  font-weight: 600;
  color: #cbd5e1;
  opacity: 0.8;
}

.deadline-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.375rem 0;
  line-height: 1.4;
}

.deadline-control-text {
  font-size: 0.85rem;
  color: #94a3b8;
  margin: 0;
}

/* Table Specific CSS */
.activity-title-cell {
  padding: 1rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #e2e8f0;
  transition: color 0.2s ease;
}

.table-row:hover .activity-title-cell, .clickable-row:hover .activity-title-cell {
  color: #c084fc;
}

.office-cell {
  padding: 1rem;
  font-size: 1.1rem;
  color: #94a3b8;
}

.type-cell {
  padding: 1rem;
}

.type-badge {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  display: inline-block;
}

.type-badge-design {
  background: rgba(153, 13, 209, 0.2);
  color: #b979cc;
  border: 1px solid rgba(153, 13, 209, 0.3);
}

.type-badge-report {
  background: rgba(6, 182, 212, 0.1);
  color: #22d3ee;
  border: 1px solid rgba(6, 182, 212, 0.2);
}

.date-cell {
  padding: 1rem;
  font-size: 1rem;
  font-family: monospace;
  color: #94a3b8;
}

::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>
