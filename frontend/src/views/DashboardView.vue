<template>
  <div class="px-4 md:px-8 py-6 md:py-8 bg-slate-50/60 min-h-full w-full">
    <div class="mb-6">
      <h1 class="text-[20px] md:text-[24px] font-heading font-bold text-slate-900 tracking-tight">Patient Management System</h1>
      <p class="text-[13px] md:text-[14px] text-slate-500 mt-1 font-medium">Overview of healthcare operations, patient volume, and recent activities.</p>
    </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-8 font-sans w-full">
          <!-- Card 1: Total Patients -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow">
            <div class="w-[52px] h-[52px] flex items-center justify-center bg-teal-50 text-teal-600 rounded-xl mr-4 shrink-0">
              <Users class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[13px] font-medium text-slate-500">Total Patients</p>
              <p class="text-[32px] leading-none font-heading font-bold text-slate-800 mt-1.5 tracking-tight">{{ totalPatients }}</p>
              <p class="text-[12px] font-semibold text-teal-600 mt-1.5 flex items-center">
                <TrendingUp class="w-[14px] h-[14px] mr-1" />
                Registered in system
              </p>
            </div>
          </div>
          
          <!-- Card 2: New Patients -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow">
            <div class="w-[52px] h-[52px] flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl mr-4 shrink-0">
              <UserPlus class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[13px] font-medium text-slate-500">New Patients</p>
              <p class="text-[32px] leading-none font-heading font-bold text-slate-800 mt-1.5 tracking-tight">{{ newPatients }}</p>
              <p class="text-[12px] font-semibold text-indigo-600 mt-1.5 flex items-center">
                <TrendingUp class="w-[14px] h-[14px] mr-1" />
                Joined in {{ currentMonthName }}
              </p>
            </div>
          </div>

          <!-- Card 3: Health Records Logged -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow">
            <div class="w-[52px] h-[52px] flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl mr-4 shrink-0">
              <FileText class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[13px] font-medium text-slate-500">Health Records Logged</p>
              <p class="text-[32px] leading-none font-heading font-bold text-slate-800 mt-1.5 tracking-tight">{{ totalRecords }}</p>
              <p class="text-[12px] font-semibold text-emerald-600 mt-1.5 flex items-center">
                <Check class="w-[14px] h-[14px] mr-1" />
                All synced
              </p>
            </div>
          </div>

          <!-- Card 4: Active In-Care Patients -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center hover:shadow-md transition-shadow">
            <div class="w-[52px] h-[52px] flex items-center justify-center bg-cyan-50 text-cyan-600 rounded-xl mr-4 shrink-0">
              <UserCheck class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[13px] font-medium text-slate-500">Active Patients</p>
              <p class="text-[32px] leading-none font-heading font-bold text-slate-800 mt-1.5 tracking-tight">{{ activePatients }}</p>
              <p class="text-[12px] font-medium text-cyan-600 mt-1.5">Currently In-Care</p>
            </div>
          </div>
        </div>

        <!-- Charts and Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
          
          <!-- Health Records Chart -->
          <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col">
            <div class="flex justify-between items-center pb-6 border-b border-slate-100 mb-6 font-sans">
              <div>
                <h3 class="text-[16px] font-bold text-slate-800 font-heading">Weekly Health Records</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daily logged medical encounters (Last 7 Days)</p>
              </div>
              <span class="px-3 py-1 bg-teal-50 text-teal-700 border border-teal-100 rounded-full text-[12px] font-bold tracking-wide flex items-center gap-1.5 font-sans">
                <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                Live Data
              </span>
            </div>
            <!-- Chart.js implementation -->
            <div class="h-[280px] w-full mt-2 relative">
              <Bar :data="chartData" :options="chartOptions" />
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col font-sans">
            <div class="mb-6">
              <h3 class="text-[18px] font-bold text-slate-800 font-heading">Recent Activity</h3>
            </div>

            <!-- Loading State -->
            <div v-if="isLoadingActivities" class="py-12 text-center text-xs text-slate-400 font-sans flex items-center justify-center gap-2">
              <div class="w-4 h-4 border-2 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
              <span>Loading activities...</span>
            </div>
            
            <!-- Empty State -->
            <div v-else-if="recentActivities.length === 0" class="py-10 text-center text-slate-400 font-sans">
              <ActivityIcon class="w-8 h-8 mx-auto mb-2 opacity-30 text-slate-300" />
              <p class="text-xs font-semibold text-slate-600">No actions recorded yet</p>
              <p class="text-[11px] text-slate-400 mt-0.5">Actions performed will appear here live.</p>
            </div>

            <!-- Dynamic Activity Feed -->
            <div v-else class="space-y-5 max-h-[360px] overflow-y-auto pr-1">
              <div 
                v-for="act in recentActivities" 
                :key="act.id" 
                class="flex gap-3.5 items-start"
              >
                <div class="mt-0.5 shrink-0">
                  <component 
                    :is="getActivityDetails(act).icon" 
                    class="w-5 h-5" 
                    :class="getActivityDetails(act).color"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-baseline">
                    <p class="text-[14px] font-bold text-slate-800 leading-tight font-heading">{{ act.title }}</p>
                    <span class="text-[12px] text-slate-400 font-normal whitespace-nowrap ml-2 font-sans">{{ act.time_ago }}</span>
                  </div>
                  <p class="text-[13px] text-slate-500 mt-1 leading-snug break-words font-sans">{{ act.description }}</p>
                </div>
              </div>
            </div>
          </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { api } from '../services/api'
import { 
  Users, 
  TrendingUp, 
  HeartPulse, 
  UserPlus, 
  FileText, 
  Check, 
  UserCheck,
  Trash2,
  Edit3,
  Stethoscope,
  Activity as ActivityIcon
} from 'lucide-vue-next'

import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const totalPatients = ref(0)
const newPatients = ref(0)
const totalRecords = ref(0)
const activePatients = ref(0)
const recentActivities = ref<any[]>([])
const isLoadingActivities = ref(false)

const currentMonthName = computed(() => {
  return new Date().toLocaleString('en-US', { month: 'long' })
})

const currentMonthYear = computed(() => {
  return new Date().toLocaleString('en-US', { month: 'short', year: 'numeric' })
})

const healthRecordsList = ref<any[]>([])

const getActivityDetails = (act: any) => {
  const desc = (act.description || '').toLowerCase()
  const title = (act.title || '').toLowerCase()
  const type = (act.type || '').toLowerCase()
  const iconType = (act.icon_type || '').toLowerCase()

  if (type.includes('delete') || title.includes('delete') || iconType === 'delete') {
    return {
      icon: Trash2,
      color: 'text-rose-500'
    }
  }

  if (type.includes('patient') || title.includes('patient') || iconType === 'patient') {
    if (type.includes('update') || title.includes('update') || iconType === 'edit') {
      return {
        icon: Edit3,
        color: 'text-amber-500'
      }
    }
    return {
      icon: UserPlus,
      color: 'text-teal-600'
    }
  }

  if (desc.includes('bp') || desc.includes('pulse') || desc.includes('vital') || title.includes('vital') || iconType === 'vitals') {
    return {
      icon: HeartPulse,
      color: 'text-rose-500'
    }
  }

  if (desc.includes('checkup') || desc.includes('exam') || desc.includes('routine') || iconType === 'checkup') {
    return {
      icon: Stethoscope,
      color: 'text-sky-600'
    }
  }

  if (desc.includes('lab') || desc.includes('result') || desc.includes('note') || iconType === 'lab') {
    return {
      icon: FileText,
      color: 'text-indigo-600'
    }
  }

  if (type.includes('update') || title.includes('update') || iconType === 'edit') {
    return {
      icon: Edit3,
      color: 'text-amber-500'
    }
  }

  return {
    icon: FileText,
    color: 'text-teal-600'
  }
}

const fetchDashboardStats = async () => {
  isLoadingActivities.value = true
  try {
    // ⚡ 1. Try unified high-speed dashboard endpoint (1 single roundtrip)
    const unifiedRes = await api.get('/api/dashboard/stats').catch(() => null)
    if (unifiedRes && unifiedRes.status === 'success' && unifiedRes.data) {
      const d = unifiedRes.data
      totalPatients.value = d.totalPatients || 0
      activePatients.value = d.activePatients || 0
      newPatients.value = d.newPatients || 0
      totalRecords.value = d.totalRecords || 0
      if (Array.isArray(d.healthRecordsList)) {
        healthRecordsList.value = d.healthRecordsList
      }
      if (Array.isArray(d.activities)) {
        recentActivities.value = d.activities
      }
      return
    }

    // 2. Fallback to individual requests if unified endpoint is unavailable
    const [patientsRes, recordsRes, activitiesRes] = await Promise.all([
      api.get('/api/patients').catch(() => null),
      api.get('/api/health-records').catch(() => null),
      api.get('/api/activities').catch(() => null)
    ])

    if (patientsRes && patientsRes.status === 'success' && Array.isArray(patientsRes.data)) {
      totalPatients.value = patientsRes.data.length
      activePatients.value = patientsRes.data.filter((p: any) => (p.status || 'Active').toLowerCase() === 'active').length
      
      const currentMonth = new Date().getMonth()
      const currentYear = new Date().getFullYear()
      newPatients.value = patientsRes.data.filter((p: any) => {
        if (!p.created_at) return true
        const d = new Date(p.created_at)
        return d.getMonth() === currentMonth && d.getFullYear() === currentYear
      }).length
    } else if (Array.isArray(patientsRes)) {
      totalPatients.value = patientsRes.length
      activePatients.value = patientsRes.length
      newPatients.value = patientsRes.length
    }

    if (recordsRes && recordsRes.status === 'success' && Array.isArray(recordsRes.data)) {
      healthRecordsList.value = recordsRes.data
      totalRecords.value = recordsRes.data.length
    } else if (Array.isArray(recordsRes)) {
      healthRecordsList.value = recordsRes
      totalRecords.value = recordsRes.length
    }

    if (activitiesRes && activitiesRes.status === 'success' && Array.isArray(activitiesRes.data)) {
      recentActivities.value = activitiesRes.data.sort((a: any, b: any) => {
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      })
    }
  } catch (e) {
    console.warn('Error fetching dashboard stats:', e)
  } finally {
    isLoadingActivities.value = false
  }
}

let pollTimer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  fetchDashboardStats()
  // Auto-refresh every 30s to keep Live Data authentic
  pollTimer = setInterval(() => {
    fetchDashboardStats()
  }, 30000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})

const weeklyDaysInfo = computed(() => {
  const now = new Date()
  
  // Calculate rolling last 7 days ending on today (6 days ago through today)
  const days = []
  for (let i = 6; i >= 0; i--) {
    const d = new Date(now)
    d.setDate(now.getDate() - i)
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const isoDate = `${year}-${month}-${day}`
    const shortDay = d.toLocaleDateString('en-US', { weekday: 'short' }) // e.g. Mon, Tue...
    const shortDate = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) // e.g. Aug 24
    days.push({
      dateObj: d,
      isoDate,
      shortDay,
      shortDate
    })
  }
  return days
})

const chartData = computed(() => {
  const labels = weeklyDaysInfo.value.map(d => d.shortDay)
  
  const counts = weeklyDaysInfo.value.map(dayInfo => {
    return healthRecordsList.value.filter(rec => {
      const recDateStr = rec.date || rec.created_at
      if (!recDateStr) return false

      const parsedDate = new Date(recDateStr)
      if (isNaN(parsedDate.getTime())) return false

      const rYear = parsedDate.getFullYear()
      const rMonth = String(parsedDate.getMonth() + 1).padStart(2, '0')
      const rDay = String(parsedDate.getDate()).padStart(2, '0')
      const rIso = `${rYear}-${rMonth}-${rDay}`
      
      return rIso === dayInfo.isoDate
    }).length
  })

  return {
    labels,
    datasets: [
      {
        label: 'Health Records',
        backgroundColor: '#0d9488',
        hoverBackgroundColor: '#0f766e',
        borderRadius: 6,
        borderSkipped: 'bottom' as const,
        barPercentage: 0.55,
        data: counts
      }
    ]
  }
})

const chartOptions = computed(() => {
  const dataArray = (chartData.value.datasets[0]?.data as number[]) || []
  const maxVal = Math.max(...dataArray, 0)
  const yMax = maxVal < 5 ? 5 : Math.ceil((maxVal + 2) / 5) * 5

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleFont: { size: 12, weight: 'bold' as const, family: 'Inter' },
        bodyFont: { size: 12, family: 'Inter' },
        padding: 10,
        cornerRadius: 8,
        displayColors: false,
        callbacks: {
          title: (items: any[]) => {
            if (!items.length) return ''
            const idx = items[0]?.dataIndex
            const dayInfo = weeklyDaysInfo.value[idx]
            return dayInfo ? `${dayInfo.shortDay}, ${dayInfo.shortDate}` : items[0].label
          },
          label: (item: any) => {
            const count = item.raw || 0
            return [
              ` Consultations: ${count} ${count === 1 ? 'record' : 'records'}`,
              ` Status: ${count > 0 ? 'Logged' : 'No records'}`
            ]
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        max: yMax,
        ticks: {
          stepSize: yMax <= 5 ? 1 : (yMax <= 10 ? 2 : 5),
          precision: 0,
          color: '#64748b',
          font: {
            size: 12,
            family: 'Inter'
          },
          padding: 10
        },
        grid: {
          color: '#f1f5f9',
          drawTicks: false
        },
        border: {
          display: false
        }
      },
      x: {
        ticks: {
          color: '#64748b',
          font: {
            size: 12,
            family: 'Inter',
            weight: 500
          },
          padding: 10
        },
        grid: {
          display: false,
          drawTicks: false
        },
        border: {
          display: true,
          color: '#e2e8f0'
        }
      }
    }
  }
})
</script>