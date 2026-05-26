<template>
  <div class="flex flex-col flex-grow">
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
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import DashboardHeader from '../components/DashboardHeader.vue';

const route = useRoute();
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'));

const title = computed(() => {
  const name = route.name || '';
  return name.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
});

const context = computed(() => {
  if (route.path.startsWith('/admin')) return 'Administration';
  if (route.path.startsWith('/staff')) return 'GAD Staff';
  if (route.path.startsWith('/college')) return 'College / Unit';
  return 'GAD Portal';
});
</script>
