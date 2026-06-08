<template>
  <div class="college-dashboard">
    <DashboardSidebar
      roleLabel="College/Unit"
      :menuItems="collegeMenu"
      @logout="handleLogout"
    />

    <div class="dashboard-main bg-slate-50">
      <header class="dashboard-header bg-[#1a1a2e] border-b border-purple-900/30"></header>

      <main class="dashboard-main-content">
        <div class="content-wrapper">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import DashboardSidebar from '../components/DashboardSidebar.vue';

const router = useRouter();

const collegeMenu = [
  { label: 'New Submission', icon: 'add', href: '/college/submit' },
  { label: 'Dashboard', icon: 'dashboard', href: '/college/dashboard' },
  { label: 'Submit Design', icon: 'add_task', href: '/college/submit-design' },
  { label: 'Submit Accomplishment', icon: 'fact_check', href: '/college/submit-report' },
  { label: 'Submitted List', icon: 'list', href: '/college/submitted-list' },
  { label: 'Technical Assist', icon: 'support_agent', href: '/college/tech-assist' },
  { label: 'Mandates', icon: 'gavel', href: '/college/mandates' },
  { label: 'Plan & Budget', icon: 'account_balance_wallet', href: '/college/gad-plan-budget' },
  { label: 'Archives', icon: 'archive', href: '/college/archive' },
  { label: 'User Manual', icon: 'menu_book', href: '/college/user-manual' },
  { label: 'Data Privacy Policy', icon: 'privacy_tip', href: '/college/data-privacy-policy' }
];

const handleLogout = async () => {
  try {
    await api.get('logout');
  } catch (err) {
    console.error('Logout failed:', err);
  } finally {
    localStorage.removeItem('user');
    localStorage.removeItem('authToken');
    router.push('/login');
  }
};

onMounted(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (!user.id || user.role !== 'college') {
    router.push('/login');
  }
});
</script>

<style scoped>
.college-dashboard { min-height: 100vh; display: flex; background-color: #f8fafc; }
.dashboard-main { flex-grow: 1; margin-left: 256px; display: flex; flex-direction: column; min-height: 100vh; }
.dashboard-header { position: fixed; top: 0; left: 256px; right: 0; height: 80px; z-index: 10; display: flex; align-items: center; padding: 0 40px; background: #1a1a2e; border-bottom: 1px solid rgba(185, 121, 204, 0.3); }
.dashboard-main-content { padding-top: 80px; flex-grow: 1; display: block; width: 100%; }
.content-wrapper { padding: 40px; width: 100%; }
</style>
