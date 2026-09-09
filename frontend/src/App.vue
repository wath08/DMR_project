<template>
  <!-- If on login page, render full screen without sidebar/header -->
  <div v-if="$route.path === '/'" class="w-full">
    <router-view />
  </div>

  <!-- Authenticated pages layout with shared sidebar & shared header -->
  <div v-else class="min-h-screen bg-slate-50 flex overflow-hidden">
    <!-- Overlay for mobile -->
    <div 
      v-if="isMobileSidebarOpen" 
      @click="isMobileSidebarOpen = false" 
      class="fixed inset-0 bg-slate-900/50 z-40 md:hidden transition-opacity"
    ></div>

    <!-- Shared Sidebar -->
    <aside 
      :class="[
        isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
        isSidebarCollapsed ? 'md:-ml-[260px]' : 'md:ml-0',
        'fixed inset-y-0 left-0 z-50 w-[65%] sm:w-[260px] md:relative md:translate-x-0 md:flex transition-all duration-300 ease-in-out bg-slate-900 text-white flex flex-col shadow-[4px_0_15px_rgba(0,0,0,0.05)] shrink-0'
      ]"
    >
      <div class="p-6 flex items-center justify-between border-b border-white/10 whitespace-nowrap h-[76px]">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 flex items-center justify-center shrink-0">
            <img src="@/assets/hospital-logo.png" alt="Hospital Logo" class="w-10 h-10 object-contain drop-shadow-md" />
          </div>
          <div class="flex flex-col">
            <span class="font-heading font-bold text-xl leading-tight text-white tracking-wide">Patient</span>
            <span class="text-[11px] text-slate-400 tracking-[0.05em] uppercase font-semibold">Management</span>
          </div>
        </div>
        
        <!-- Desktop Close Button (ON the Sidebar) -->
        <button 
          @click="isSidebarCollapsed = true" 
          class="hidden md:flex text-slate-400 hover:text-white p-1 ml-2 transition-colors group relative"
        >
          <PanelLeftClose class="w-5 h-5" />
          <div class="absolute left-full ml-2 px-2 py-1 bg-slate-800 text-white text-[11px] rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 font-medium">
            Close sidebar
          </div>
        </button>

        <!-- Close button for mobile -->
        <button @click="isMobileSidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
          <X class="w-5 h-5" />
        </button>
      </div>
      
      <nav class="p-5 flex flex-col gap-1.5 flex-1 overflow-y-auto whitespace-nowrap">
        <router-link 
          v-for="item in menuItems" 
          :key="item.path" 
          :to="item.path" 
          @click="isMobileSidebarOpen = false"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition-all duration-200"
          :class="[
            $route.path === item.path 
              ? 'bg-teal-600 text-white font-semibold shadow-[0_0_20px_rgba(13,148,136,0.2)]' 
              : 'text-slate-400 hover:bg-slate-800 hover:text-white font-medium'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5 shrink-0" />
          <span class="truncate">{{ item.name }}</span>
        </router-link>
      </nav>

      <!-- Logout button -->
      <div class="p-5 border-t border-white/10 shrink-0 whitespace-nowrap">
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 text-rose-500 hover:bg-rose-500/10 hover:text-rose-600 rounded-xl font-medium text-[14px] transition-all duration-200 cursor-pointer">
          <LogOut class="w-5 h-5 shrink-0" />
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main View Content Area with Shared Top Header -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">
      <!-- Shared Top Bar Header -->
      <header class="h-[76px] bg-white border-b border-slate-100 flex items-center justify-between px-4 md:px-8 z-30 relative shrink-0 transition-all duration-300">
        
        <!-- Left Side: Mobile Logo OR Desktop Sidebar Toggle -->
        <div class="flex items-center gap-4 h-full">
          <!-- Mobile Logo -->
          <div class="md:hidden flex items-center gap-2">
            <img src="@/assets/hospital-logo.png" alt="Hospital Logo" class="w-8 h-8 object-contain" />
            <span class="font-heading font-bold text-lg text-slate-800">DMR</span>
          </div>
          
          <!-- Desktop Sidebar Expand Toggle (Only visible when sidebar is collapsed) -->
          <button 
            v-if="isSidebarCollapsed"
            @click="isSidebarCollapsed = false" 
            class="hidden md:flex items-center justify-center p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 rounded-lg transition-colors group relative"
            title="Expand Sidebar"
          >
            <PanelLeftOpen class="w-5 h-5" />
            
            <!-- Tooltip -->
            <div class="absolute left-full ml-2 px-2 py-1 bg-slate-800 text-white text-[11px] rounded opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50 font-medium">
              Expand sidebar
            </div>
          </button>
        </div>

        <!-- Center: Daily Quote (Hidden on Mobile/Tablet to save space) -->
        <div class="hidden lg:flex flex-1 justify-center mx-4 xl:mx-8">
          <p class="text-[13px] xl:text-[14px] text-slate-500 italic font-medium px-4 text-center">
            "The good physician treats the disease; the great physician treats the patient who has the disease."
          </p>
        </div>

        <!-- Right Side: Profile -->
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
          <button @click="isProfileOpen = !isProfileOpen" class="flex items-center gap-2.5 md:gap-3 border border-slate-200 hover:border-slate-300 hover:shadow-sm bg-white p-1 pr-1 sm:pr-4 rounded-full transition-all focus:outline-none cursor-pointer">
            <div class="w-[38px] h-[38px] rounded-full bg-blue-600 text-white flex items-center justify-center text-[14px] font-bold tracking-wide shrink-0 shadow-sm">
              {{ userInitials }}
            </div>
            <div class="flex-col text-left hidden sm:flex justify-center">
              <span class="text-[14px] font-semibold text-slate-800 leading-tight tracking-tight">{{ username }}</span>
              <span class="text-[12px] text-slate-500 font-medium leading-none mt-0.5">{{ userEmail }}</span>
            </div>
            <ChevronDown class="w-4 h-4 text-slate-400 hidden sm:block transition-transform duration-200 ml-1" :class="{ 'rotate-180': isProfileOpen }" />
          </button>
          
          <!-- Hamburger Menu (Mobile Only) on the Right -->
          <button 
            @click="isMobileSidebarOpen = true" 
            class="md:hidden p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 rounded-lg transition-colors"
          >
            <Menu class="w-6 h-6" />
          </button>

          <!-- Click outside overlay -->
          <div v-if="isProfileOpen" @click="isProfileOpen = false" class="fixed inset-0 z-40"></div>
          
          <!-- New Simple Profile Dropdown -->
          <div v-if="isProfileOpen" class="absolute top-[70px] right-4 md:right-8 w-[240px] bg-white border border-slate-100 rounded-xl shadow-lg z-50 py-1" style="animation: fadeIn 0.1s ease-out;">
            <div class="px-4 py-3 border-b border-slate-100 flex flex-col">
              <span class="text-[14px] font-bold text-slate-800 leading-tight capitalize">{{ username }}</span>
              <span class="text-[12px] text-slate-500 mt-0.5 truncate">{{ userEmail }}</span>
            </div>
            <div class="py-1 border-b border-slate-100">
              <router-link to="/settings" class="block px-4 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 transition-colors" @click="isProfileOpen = false">
                Account Security
              </router-link>
            </div>
            <div class="py-1">
              <button @click="handleLogout" class="w-full text-left block px-4 py-2.5 text-[14px] font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                Sign out
              </button>
            </div>
          </div>
        </div>
        </div>
      </header>

      <!-- Routed Page Main Body -->
      <div class="flex-1 overflow-y-auto">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { api, resolveServerUrl } from './services/api'
import defaultAvatar from '@/assets/profiledefault.svg'
import {
  Search,
  Bell,
  LineChart, 
  Users, 
  FileText, 
  BarChart3, 
  ShieldCheck, 
  Settings, 
  LogOut, 
  ChevronDown, 
  X, 
  User, 
  Edit,
  Menu,
  PanelLeftClose,
  PanelLeftOpen
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// Reactive user profile refs
const username = ref('admin')
const userEmail = ref('admin@gmail.com')
const userRole = ref<string>('superadmin')
const userAvatar = ref<string>('')
const isProfileOpen = ref(false)
const isMobileSidebarOpen = ref(false)
const isSidebarCollapsed = ref(false)


const userInitials = computed(() => {
  if (!username.value) return 'U'
  const words = username.value.split(' ')
  if (words.length > 1) {
    return (words[0][0] + words[1][0]).toUpperCase()
  }
  return username.value.substring(0, 2).toUpperCase()
})

const handleImageFallback = (e: Event) => {
  const img = e.target as HTMLImageElement
  if (img) {
    img.src = defaultAvatar
  }
}

const loadUserProfile = async () => {
  const storedName = localStorage.getItem('username')
  const storedEmail = localStorage.getItem('userEmail')
  const storedRole = localStorage.getItem('userRole')
  const storedAvatar = localStorage.getItem('userAvatar')
  
  if (storedName) username.value = storedName
  if (storedEmail) userEmail.value = storedEmail
  if (storedRole) userRole.value = storedRole
  if (storedAvatar) userAvatar.value = resolveServerUrl(storedAvatar)

  // Fetch latest profile from backend API
  try {
    const res = await api.get('/api/user/profile')
    if (res && res.status === 'success' && res.data) {
      if (res.data.username) {
        username.value = res.data.username
        localStorage.setItem('username', res.data.username)
      }
      if (res.data.email) {
        userEmail.value = res.data.email
        localStorage.setItem('userEmail', res.data.email)
      }
      if (res.data.role) {
        userRole.value = res.data.role
        localStorage.setItem('userRole', res.data.role)
      }
      if (res.data.avatar) {
        const url = resolveServerUrl(res.data.avatar)
        userAvatar.value = url
        localStorage.setItem('userAvatar', url)
      }
    }
  } catch (err) {
    // Continue with localStorage
  }
}

const onProfileUpdated = () => {
  const storedName = localStorage.getItem('username')
  const storedEmail = localStorage.getItem('userEmail')
  const storedRole = localStorage.getItem('userRole')
  const storedAvatar = localStorage.getItem('userAvatar')
  if (storedName) username.value = storedName
  if (storedEmail) userEmail.value = storedEmail
  if (storedRole) userRole.value = storedRole
  userAvatar.value = storedAvatar ? resolveServerUrl(storedAvatar) : ''
}

onMounted(() => {
  loadUserProfile()
  // Listen for storage events (e.g. avatar changed in SettingsView)
  window.addEventListener('storage', loadUserProfile)
})

onUnmounted(() => {
  window.removeEventListener('storage', loadUserProfile)
})

// Keep profile in sync on route changes
watch(() => route.path, () => {
  loadUserProfile()
  isProfileOpen.value = false
})

// Dynamic RBAC Menu: Superadmin sees "Manage Users", regular users do not!
const menuItems = computed(() => {
  const items = [
    { name: 'Dashboard', path: '/dashboard', icon: LineChart },
    { name: 'Manage Patients', path: '/patients', icon: Users },
    { name: 'Health Records', path: '/health-records', icon: FileText },
    { name: 'Reports', path: '/reports', icon: BarChart3 }
  ]

  // Only show Manage Users for superadmin role
  if (userRole.value === 'superadmin') {
    items.push({ name: 'Manage Users', path: '/users', icon: ShieldCheck })
  }

  items.push({ name: 'Settings', path: '/settings', icon: Settings })

  return items
})

const handleLogout = async () => {
  try {
    await api.post('/api/auth/logout', {})
  } catch (error: any) {
    console.error('Logout error:', error)
  } finally {
    localStorage.removeItem('username')
    localStorage.removeItem('userEmail')
    localStorage.removeItem('userRole')
    localStorage.removeItem('userAvatar')
    router.push('/')
  }
}
</script>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>