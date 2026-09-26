<template>
  <div class="min-h-screen bg-slate-50 flex flex-col overflow-x-clip w-full">
    <!-- Top Navbar for Desktop/Tablet -->
    <DashboardNavbar 
      :menuItems="adminMenu"
      :user="user"
      @toggle-mobile-menu="isSidebarOpen = true"
    />

    <!-- Mobile Sidebar Overlay & Component (Only visible on small screens) -->
    <div class="xl:hidden">
      <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-black/50 z-40"></div>
      <DashboardSidebar
        :isOpen="isSidebarOpen"
        @close="isSidebarOpen = false"
        roleLabel="Administrator"
        :menuItems="adminMenu"
        :user="user"
        @logout="handleLogout"
      />
    </div>

    <main :class="['flex-grow w-full min-w-0 overflow-x-hidden', ($route.path.includes('/plan-and-budget') || $route.path.includes('/budget-distribution')) ? 'p-0' : 'p-4 md:p-10']">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import DashboardNavbar from '../components/DashboardNavbar.vue';
import DashboardSidebar from '../components/DashboardSidebar.vue';

const router = useRouter();
const isSidebarOpen = ref(false);
const isHeaderHidden = ref(false);
const lastScrollY = ref(0);
const user = ref({});

const handleScroll = () => {
  const currentScrollY = window.scrollY;
  if (currentScrollY > lastScrollY.value && currentScrollY > 50) {
    isHeaderHidden.value = true;
  } else {
    isHeaderHidden.value = false;
  }
  lastScrollY.value = currentScrollY;
};

const adminMenu = ref([
  { label: 'Dashboard', icon: 'dashboard', href: '/admin/dashboard' },
  {
    label: 'Documents', icon: 'folder',
    children: [
      { label: 'Submitted List', icon: 'folder', href: '/admin/submitted-list' },
      { label: 'Activity Design List', icon: 'description', href: '/admin/ad-list' },
      { label: 'Accomplishment Report List', icon: 'description', href: '/admin/ar-list' },
      { label: 'Archives', icon: 'archive', href: '/admin/archive' },
      { label: 'Document Trash Bin', icon: 'delete', href: '/admin/trash-bin' }
    ]
  },
  {
    label: 'Plan & Budget Distribution', icon: 'account_balance',
    children: [
      { label: 'Plan & Budget', icon: 'table_chart', href: '/admin/plan-and-budget' },
      { label: 'Budget Distribution', icon: 'account_balance_wallet', href: '/admin/budget-distribution' },
      { label: 'Report Monitoring', icon: 'bar_chart', href: '/admin/reports' },
      { label: 'Budget Monitoring', icon: 'payments', href: '/admin/budget' }
    ]
  },
  {
    label: 'System & Controls', icon: 'admin_panel_settings',
    children: [
      { label: 'User Management', icon: 'manage_accounts', href: '/admin/user-management' },
      { label: 'Campus Resources', icon: 'business_center', href: '/admin/campus-resources' },
      { label: 'Activity Logs', icon: 'history', href: '/admin/activity-logs' }
    ]
  }
]);

// Notifications now handled directly in DashboardNavbar

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
  window.addEventListener('scroll', handleScroll);
  user.value = JSON.parse(localStorage.getItem('user') || '{}');
  if (!user.value.id || user.value.role !== 'admin') {
    router.push('/login');
  }
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
/* No custom layout styles needed; handled by Tailwind */
</style>
