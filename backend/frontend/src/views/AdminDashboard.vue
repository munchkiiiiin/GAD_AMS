<template>
  <div class="admin-dashboard bg-slate-50">
    <DashboardSidebar
      roleLabel="Administrator"
      :menuItems="adminMenu"
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

const adminMenu = [
  { label: 'Dashboard', icon: 'dashboard', href: '/admin/dashboard' },
  { label: 'Annual Reports', icon: 'description', href: '/admin/annual-report' },
  { label: 'Submitted List', icon: 'folder', href: '/admin/submitted-list' },
  { label: 'Activity Design List', icon: 'description', href: '/admin/ad-list' },
  { label: 'Accomplishment Report List', icon: 'description', href: '/admin/ar-list' },
  { label: 'Plan & Budget', icon: 'account_balance_wallet', href: '/admin/gad-plan-budget' },
  { label: 'Mandates Management', icon: 'account_balance', href: '/admin/mandates' },
  { label: 'Archive', icon: 'archive', href: '/admin/archive' },
  { label: 'Report Monitoring', icon: 'bar_chart', href: '/admin/reports' },
  { label: 'Budget Monitoring', icon: 'account_balance_wallet', href: '/admin/budget' },
  { label: 'User Manual', icon: 'help', href: '/admin/user-manual' },
  { label: 'Data Privacy Policy', icon: 'privacy_tip', href: '/admin/data-privacy-policy' }
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
  if (!user.id || user.role !== 'admin') {
    router.push('/login');
  }
});
</script>

<style scoped>
.admin-dashboard { min-height: 100vh; display: flex; background-color: #f8fafc; }
.dashboard-main { flex-grow: 1; margin-left: 256px; display: flex; flex-direction: column; min-height: 100vh; }
.dashboard-header { position: fixed; top: 0; left: 256px; right: 0; height: 80px; z-index: 10; display: flex; align-items: center; padding: 0 40px; background: #1a1a2e; border-bottom: 1px solid rgba(185, 121, 204, 0.3); }
.dashboard-main-content { padding-top: 80px; flex-grow: 1; display: block; width: 100%; }
.content-wrapper { padding: 40px; width: 100%; }
</style>
