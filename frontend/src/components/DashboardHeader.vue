<template>
  <header class="w-full sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm dark:shadow-none font-headline antialiased border-b border-slate-200/60 dark:border-slate-800/80">
    <div class="flex flex-wrap items-center justify-between gap-4 px-10 py-3 w-full max-w-[1600px] mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-4 min-w-0">
        <span class="text-xl font-bold tracking-tight text-purple-900 dark:text-purple-100 truncate">{{ title }}</span>
        <span class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">{{ context }}</span>
      </div>
      <div class="flex items-center gap-4 sm:gap-6 flex-shrink-0">
        <div v-if="showSearch" class="relative hidden md:block">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <span class="material-symbols-outlined text-sm">search</span>
          </span>
          <input class="pl-10 pr-4 py-1.5 bg-surface-container-low border-none rounded-xl text-sm focus:ring-2 focus:ring-primary w-56 lg:w-64 transition-all" :placeholder="searchPlaceholder" type="search" />
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
          <button type="button" class="p-2 text-slate-600 hover:bg-purple-50 rounded-full transition-colors active:scale-95 duration-200">
            <span class="material-symbols-outlined">notifications</span>
          </button>
          <button type="button" class="p-2 text-slate-600 hover:bg-purple-50 rounded-full transition-colors active:scale-95 duration-200">
            <span class="material-symbols-outlined">settings</span>
          </button>
          <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center overflow-hidden ml-1 border border-purple-200/50" :title="username">
            <span class="text-[10px] font-bold text-white select-none">{{ userInitial }}</span>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, default: 'Dashboard' },
  context: { type: String, default: '' },
  showSearch: { type: Boolean, default: true },
  searchPlaceholder: { type: String, default: 'Search...' },
  username: { type: String, default: 'User' }
});

const userInitial = computed(() => props.username.charAt(0).toUpperCase());
</script>
