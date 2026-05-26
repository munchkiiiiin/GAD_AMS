<template>
  <div class="admin-dashboard bg-background min-h-screen flex">
    <DashboardSidebar 
      :roleLabel="roleLabel" 
      :menuItems="menuItems" 
      @logout="handleLogout" 
    />

    <div class="flex-grow ml-64 flex flex-col">
      <DashboardHeader 
        :title="title" 
        :context="context" 
        :username="user?.username" 
      />

      <main class="p-8 flex flex-col items-center justify-center flex-grow text-center">
        <div class="w-24 h-24 bg-surface-container rounded-full flex items-center justify-center text-primary mb-6">
          <span class="material-symbols-outlined text-5xl">construction</span>
        </div>
        <h1 class="text-3xl font-headline font-extrabold text-primary mb-2">{{ title }}</h1>
        <p class="text-on-surface-variant max-w-md">
          This feature is currently being migrated to the new decoupled architecture. 
          Please check back soon for the updated version of the {{ title }} module.
        </p>
        <button @click="$router.back()" class="mt-10 bg-primary text-white px-8 py-3 rounded-full font-bold shadow-lg hover:opacity-90 transition-all">
          Go Back
        </button>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
// 1. Swap the old raw axios import with your custom dynamic api instance
import api from '../api'; 
import DashboardSidebar from '../components/DashboardSidebar.vue';
import DashboardHeader from '../components/DashboardHeader.vue';

const router = useRouter();
const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const title = computed(() => {
  const name = route.name || '';
  return name.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
});

const context = computed(() => {
  if (route.path.includes('/admin')) return 'Administration';
  if (route.path.includes('/college')) return 'College/Unit';
  if (route.path.includes('/staff')) return 'GAD Staff';
  return 'Module';
});

const roleLabel = computed(() => {
  if (route.path.includes('/admin')) return 'Administrator';
  if (route.path.includes('/college')) return 'College/Unit';
  if (route.path.includes('/staff')) return 'GAD Staff';
  return 'User';
});

const adminMenu = [
  { label: 'Dashboard', icon: 'dashboard', href: '/admin/dashboard' },
  { label: 'Annual Reports', icon: 'description', href: '/admin/annual-report' },
  { label: 'Plan & Budget', icon: 'account_balance_wallet', href: '/admin/gad-plan-budget' },
  { label: 'Mandates', icon: 'gavel', href: '/admin/mandates' },
  { label: 'Archives', icon: 'archive', href: '/admin/archive' },
  { label: 'User Manual', icon: 'menu_book', href: '/admin/user-manual' }
];

const collegeMenu = [
  { label: 'Dashboard', icon: 'dashboard', href: '/college/dashboard' },
  { label: 'Submit Design', icon: 'add_task', href: '/college/submit-design' },
  { label: 'Submit Accomplishment', icon: 'fact_check', href: '/college/submit-accomplishment' },
  { label: 'Technical Assist', icon: 'support_agent', href: '/college/tech-assist' },
  { label: 'Mandates', icon: 'gavel', href: '/college/mandates' },
  { label: 'Archives', icon: 'archive', href: '/college/archive' }
];

const staffMenu = [
  { label: 'Dashboard', icon: 'dashboard', href: '/staff/dashboard' },
  { label: 'Review Submissions', icon: 'rate_review', href: '/staff/reviews' },
  { label: 'Budget Utilization', icon: 'payments', href: '/staff/budget' },
  { label: 'Annual Reports', icon: 'description', href: '/staff/reports' },
  { label: 'Mandates', icon: 'gavel', href: '/staff/mandates' },
  { label: 'User Manual', icon: 'menu_book', href: '/staff/user-manual' }
];

const menuItems = computed(() => {
  if (route.path.includes('/admin')) return adminMenu;
  if (route.path.includes('/college')) return collegeMenu;
  if (route.path.includes('/staff')) return staffMenu;
  return [];
});

// 2. Clear out the hardcoded production link so logout functions offline
const handleLogout = async () => {
  try {
    await api.get('logout');
  } catch (err) {
    console.error('Logout failed tracking context:', err);
  } finally {
    // Always clean up local storage keys and route back to lockscreen
    localStorage.removeItem('user');
    localStorage.removeItem('authToken'); 
    router.push('/login');
  }
};

onMounted(() => {
  if (!user.value.id) {
    router.push('/login');
  }
});
</script>
