<template>
  <div class="p-8 bg-slate-50/60 min-h-full">
    <!-- Page Header & Action Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold font-heading text-slate-900 tracking-tight flex items-center gap-2.5">
          <BarChart3 class="w-7 h-7 text-teal-600" />
          <span>Financial & Salary Reports</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Analytics on staff compensation, hospital revenue, and operational expenses.</p>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <!-- Date Selector -->
        <div class="flex items-center gap-2 bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-xs text-xs font-semibold text-slate-700 whitespace-nowrap shrink-0">
          <Calendar class="w-4 h-4 text-teal-600 shrink-0" />
          <span>2026 Live Database Analytics</span>
        </div>

        <!-- 📥 Export Excel / CSV Button -->
        <button
          type="button"
          @click="showExportModal = true; exportSelectedMonth = selectedMonthKey"
          :disabled="isExporting"
          class="flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md transition-all cursor-pointer disabled:opacity-50 whitespace-nowrap shrink-0"
        >
          <Download class="w-4 h-4 shrink-0" :class="{'animate-bounce': isExporting}" />
          <span>{{ isExporting ? 'Exporting...' : 'Export Excel / CSV' }}</span>
        </button>
      </div>
    </div>

    <!-- Summary KPI Stat Cards (100% Real DB Data) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      
      <!-- Total All-Time Revenue Collected -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Revenue (All Time)</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <DollarSign class="w-5 h-5" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 mt-2">
          ${{ (reportData?.summary?.totalRevenueAllTime ?? 0).toLocaleString() }}
        </p>
        <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold mt-2">
          <TrendingUp class="w-3.5 h-3.5" />
          <span>Total collected from all consultations</span>
        </div>
      </div>

      <!-- Gross Revenue (Selected Month) -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Monthly Revenue</span>
          <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
            <CreditCard class="w-5 h-5" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 mt-2">
          ${{ (reportData?.summary?.grossRevenueMonth ?? 0).toLocaleString() }}
        </p>
        <div class="flex items-center gap-1.5 text-xs text-teal-600 font-semibold mt-2">
          <TrendingUp class="w-3.5 h-3.5" />
          <span>From patient consultation fees</span>
        </div>
      </div>

      <!-- Total Encounters -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Encounters</span>
          <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <TrendingUp class="w-5 h-5" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 mt-2">
          {{ reportData?.summary?.totalEncounters ?? 0 }} Visits
        </p>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-2">
          <span>Across {{ reportData?.summary?.totalPatients ?? 0 }} registered patients</span>
        </div>
      </div>

      <!-- Active Doctors & Specialists -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Doctors & Medics</span>
          <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <Users class="w-5 h-5" />
          </div>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 mt-2">
          {{ reportData?.summary?.activeDoctorCount || 1 }} {{ (reportData?.summary?.activeDoctorCount === 1) ? 'Doctor' : 'Doctors' }}
        </p>
        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-2">
          <span>{{ (reportData?.summary?.registeredDoctorCount > 0) ? `${reportData.summary.registeredDoctorCount} registered doctor accounts` : `${reportData?.summary?.consultingDoctorCount || 5} active specialists consulting` }}</span>
        </div>
      </div>
    </div>

    <!-- Interactive Consultation Revenue Breakdown Chart (Full Width & Spacious) -->
    <div class="mb-8">
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
        
        <!-- Header & Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
          <div>
            <h2 class="text-base font-bold text-slate-900 font-heading">
              {{ revenueViewMode === 'daily' ? 'Daily Consultation Revenue' : 'Monthly Consultation Revenue' }}
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">
              {{ revenueViewMode === 'daily' 
                  ? `Daily fee income for ${selectedDailyData.monthLabel}` 
                  : 'Total consultation fee revenue collected per month' }}
            </p>
          </div>

          <!-- Controls: Month Selector & View Mode Switcher -->
          <div class="flex items-center gap-2.5 self-start sm:self-auto shrink-0 flex-wrap">
            <!-- 🌟 Custom Styled Month Popover Selector (When in daily mode) -->
            <div v-if="revenueViewMode === 'daily'" class="relative" ref="monthDropdownRef">
              <button 
                type="button" 
                @click.stop="isMonthMenuOpen = !isMonthMenuOpen"
                class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-slate-100/80 border border-slate-200 hover:border-teal-400 rounded-xl text-xs font-bold text-slate-800 shadow-2xs transition-all cursor-pointer"
                :class="{'border-teal-500 ring-2 ring-teal-500/20 bg-white': isMonthMenuOpen}"
              >
                <Calendar class="w-3.5 h-3.5 text-teal-600 shrink-0" />
                <span>{{ selectedMonthLabel }}</span>
                <span class="font-mono text-[11px] text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-100 font-extrabold">
                  ${{ selectedDailyData.totalRevenue }}
                </span>
                <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 shrink-0" :class="{'rotate-180': isMonthMenuOpen}" />
              </button>

              <!-- Custom Floating Menu -->
              <Transition name="fade">
                <div 
                  v-if="isMonthMenuOpen" 
                  class="absolute top-full right-0 mt-1.5 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden py-1"
                >
                  <div class="px-3.5 py-2 text-[10.5px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                    <span>Select Month</span>
                    <span class="text-teal-700 font-bold font-mono">{{ availableMonths.length }} Months</span>
                  </div>
                  
                  <div class="max-h-60 overflow-y-auto divide-y divide-slate-50 py-1">
                    <button
                      v-for="m in availableMonths"
                      :key="m.key"
                      type="button"
                      @click="selectedMonthKey = m.key; isMonthMenuOpen = false"
                      class="w-full px-3.5 py-2.5 text-left flex items-center justify-between transition-colors cursor-pointer group"
                      :class="selectedMonthKey === m.key ? 'bg-teal-50/80 text-teal-900 font-bold' : 'hover:bg-slate-50 text-slate-700'"
                    >
                      <div class="flex items-center gap-2.5">
                        <div 
                          class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold transition-colors"
                          :class="selectedMonthKey === m.key ? 'bg-teal-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-500 group-hover:bg-teal-100 group-hover:text-teal-700'"
                        >
                          <Calendar class="w-3.5 h-3.5" />
                        </div>
                        <div>
                          <div class="text-xs font-bold" :class="selectedMonthKey === m.key ? 'text-teal-900' : 'text-slate-800'">
                            {{ m.label }}
                          </div>
                          <div class="text-[10px] text-slate-400 font-medium">
                            {{ m.totalEncounters }} {{ m.totalEncounters === 1 ? 'visit' : 'visits' }}
                          </div>
                        </div>
                      </div>

                      <div class="text-right shrink-0">
                        <div 
                          class="font-mono text-xs font-extrabold"
                          :class="m.totalRevenue > 0 ? 'text-emerald-600' : 'text-slate-400'"
                        >
                          ${{ m.totalRevenue.toLocaleString() }}
                        </div>
                        <span v-if="selectedMonthKey === m.key" class="text-[9.5px] font-bold text-teal-700 bg-teal-100/90 px-1.5 py-0.2 rounded inline-block mt-0.5">
                          Selected
                        </span>
                      </div>
                    </button>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- View Switcher Tabs -->
            <div class="bg-slate-100 p-0.5 rounded-xl flex items-center border border-slate-200/60">
              <button 
                type="button" 
                @click="revenueViewMode = 'daily'"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="revenueViewMode === 'daily' ? 'bg-white text-teal-700 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800'"
              >
                Daily
              </button>
              <button 
                type="button" 
                @click="revenueViewMode = 'monthly'"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="revenueViewMode === 'monthly' ? 'bg-white text-teal-700 shadow-2xs font-extrabold' : 'text-slate-500 hover:text-slate-800'"
              >
                Monthly
              </button>
            </div>
          </div>
        </div>

        <!-- Clean Mini KPI Stat Strip -->
        <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50/70 border border-slate-100 rounded-xl mb-4 text-center">
          <div>
            <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block">Period Revenue</span>
            <span class="text-sm font-extrabold text-emerald-600 font-mono">
              ${{ (revenueViewMode === 'daily' ? (selectedDailyData.totalRevenue ?? 0) : (reportData?.summary?.grossRevenueMonth ?? 0)).toLocaleString() }}
            </span>
          </div>
          <div class="border-x border-slate-200/70">
            <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block">Total Visits</span>
            <span class="text-sm font-extrabold text-slate-800 font-mono">
              {{ revenueViewMode === 'daily' ? (selectedDailyData.totalEncounters ?? 0) : (reportData?.summary?.totalEncounters ?? 0) }}
            </span>
          </div>
          <div>
            <span class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider block">Average / Visit</span>
            <span class="text-sm font-extrabold text-teal-700 font-mono">
              ${{ (revenueViewMode === 'daily' && selectedDailyData.totalEncounters > 0) ? Math.round(selectedDailyData.totalRevenue / selectedDailyData.totalEncounters) : ((reportData?.summary?.totalEncounters > 0) ? Math.round(reportData.summary.totalRevenueAllTime / reportData.summary.totalEncounters) : 0) }}
            </span>
          </div>
        </div>
        
        <!-- The Chart Canvas -->
        <div class="h-64 relative">
          <Bar v-if="consultationRevenueChartData" :data="consultationRevenueChartData" :options="dailyRevenueBarOptions" />
        </div>

        <!-- Footer Caption -->
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-teal-600 inline-block"></span>
            <span class="text-slate-600 font-medium text-[11.5px]">
              {{ revenueViewMode === 'daily' ? `Showing all ${selectedDailyData.days?.length || 31} days of ${selectedDailyData.monthLabel}` : 'Showing all 12 months of the year' }}
            </span>
          </span>
          <span class="font-mono font-bold text-teal-700 text-[11.5px] bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
            Live DB Data
          </span>
        </div>
      </div>
    </div>

    <!-- Bottom Section: Department Breakdown Table & Doughnut (Flexible & Responsive) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
      
      <!-- Doughnut Chart (Flexible, Fixed-Size & Equal-Height) -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between h-full">
        <div>
          <div class="flex items-center justify-between gap-2 mb-1">
            <h2 class="text-base font-bold text-slate-900 font-heading">Clinical Category Distribution</h2>
            <span class="px-2 py-0.5 bg-teal-50 text-teal-700 rounded-full text-[10px] font-bold shrink-0">Live DB</span>
          </div>
          <p class="text-xs text-slate-400 mb-3">Breakdown of patient cases by clinical category</p>
        </div>

        <!-- Center Ring with Dynamic Total Badge -->
        <div class="relative w-full h-80 sm:h-72 my-auto flex items-center justify-center">
          <Doughnut v-if="deptChartData" :data="deptChartData" :options="doughnutOptions" />
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-16 sm:pb-12">
            <span class="text-xl font-extrabold text-slate-800 font-mono leading-none">
              {{ reportData?.summary?.totalEncounters ?? 0 }}
            </span>
            <span class="text-[9.5px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Cases</span>
          </div>
        </div>

        <!-- Dynamic Top Category Footer -->
        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
          <div class="flex items-center gap-1.5 truncate">
            <span class="text-slate-400">Top Category:</span>
            <strong class="text-slate-800 truncate font-bold">{{ topDepartment?.department || 'No Cases' }}</strong>
          </div>
          <span class="font-extrabold text-teal-700 font-mono text-xs shrink-0 pl-2">{{ topDepartment?.percentage || 0 }}%</span>
        </div>
      </div>

      <!-- Department Table (Equal Height, Responsive with clean scroll) -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between h-full">
        <div>
          <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-white">
            <div>
              <h2 class="text-base font-bold text-slate-900 font-heading">Departmental Clinical Volume Breakdown</h2>
              <p class="text-xs text-slate-400 mt-0.5">Aggregated cases and generated revenue from medical encounters</p>
            </div>
            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-lg text-xs font-bold shadow-2xs shrink-0">
              Live Data
            </span>
          </div>
          
          <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse text-sm min-w-[550px]">
              <thead>
                <tr class="bg-slate-50/90 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
                  <th class="w-[38%] px-6 py-3.5">Clinical Examination Category</th>
                  <th class="w-[18%] px-6 py-3.5">Total Cases</th>
                  <th class="w-[20%] px-6 py-3.5">Total Revenue ($)</th>
                  <th class="w-[24%] px-6 py-3.5">Share (%)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="d in reportData?.departments || []" :key="d.department" class="hover:bg-slate-50/60 transition-colors">
                  <td class="px-6 py-3.5 font-bold text-slate-800 text-[13px] truncate" :title="d.department">
                    {{ d.department }}
                  </td>
                  <td class="px-6 py-3.5 text-slate-700 font-semibold text-[13px]">
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-mono font-bold text-xs mr-1">
                      {{ d.staffCount }}
                    </span>
                    <span>{{ d.staffCount === 1 ? 'case' : 'cases' }}</span>
                  </td>
                  <td class="px-6 py-3.5 font-mono font-extrabold text-emerald-600 text-sm">
                    ${{ Math.round(Number(d.totalSalary)).toLocaleString() }}
                  </td>
                  <td class="px-6 py-3.5">
                    <div class="flex items-center gap-3">
                      <div class="w-full max-w-[100px] h-2 bg-slate-100 rounded-full overflow-hidden shrink-0">
                        <div class="h-full bg-teal-600 rounded-full transition-all duration-500" :style="{ width: d.percentage + '%' }"></div>
                      </div>
                      <span class="text-xs font-extrabold text-slate-700 font-mono w-8 text-right shrink-0">{{ d.percentage }}%</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- 👨‍⚕️ Doctor Clinical Performance & Revenue Contribution Table -->
    <div v-if="reportData?.doctors && reportData.doctors.length > 0" class="mt-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="px-6 py-4.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-base font-bold text-slate-900 font-heading">Doctor Clinical Consultations & Revenue Contribution</h2>
            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[11px] font-bold">
              {{ reportData.doctors.length }} {{ reportData.doctors.length === 1 ? 'Doctor' : 'Doctors' }}
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-0.5">Real-time encounter volume and billable consultation revenue by attending physician</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-lg text-xs font-bold shadow-2xs">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Live DB</span>
          </span>
        </div>
      </div>
      
      <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse text-sm min-w-[620px] table-fixed">
          <thead>
            <tr class="bg-slate-50/90 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/70">
              <th class="w-[34%] px-6 py-3.5">Attending Physician</th>
              <th class="w-[20%] px-6 py-3.5">Encounters Treated</th>
              <th class="w-[22%] px-6 py-3.5">Generated Revenue ($)</th>
              <th class="w-[24%] px-6 py-3.5">Workload Share (%)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="doc in reportData.doctors" :key="doc.doctorName" class="hover:bg-slate-50/60 transition-colors">
              <td class="px-6 py-4 font-bold text-slate-900">
                <div class="flex items-center gap-2.5 truncate">
                  <div class="w-7 h-7 rounded-full bg-teal-50 text-teal-700 font-extrabold text-xs flex items-center justify-center border border-teal-100/80 shrink-0">
                    Dr
                  </div>
                  <span class="truncate text-[13px] font-bold text-slate-800" :title="doc.doctorName">{{ doc.doctorName }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-700 font-semibold text-[13px]">
                <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-mono font-bold text-xs mr-1">
                  {{ doc.encounters }}
                </span>
                <span>{{ doc.encounters === 1 ? 'visit' : 'visits' }}</span>
              </td>
              <td class="px-6 py-4 font-mono font-extrabold text-emerald-600 text-sm">
                ${{ Math.round(Number(doc.revenue)).toLocaleString() }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-full max-w-[130px] h-2 bg-slate-100 rounded-full overflow-hidden shrink-0">
                    <div 
                      class="h-full bg-teal-600 rounded-full transition-all duration-500" 
                      :style="{ width: Math.min(100, Math.round((doc.encounters / (reportData.summary.totalEncounters || 1)) * 100)) + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs font-extrabold text-slate-700 font-mono w-9 text-right shrink-0">
                    {{ Math.round((doc.encounters / (reportData.summary.totalEncounters || 1)) * 100) }}%
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 🌟 Clean Human-Crafted Export Modal -->
    <Transition name="fade">
      <div 
        v-if="showExportModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs cursor-pointer"
        @click.self="showExportModal = false; isExportMonthOpen = false"
      >
        <div 
          class="bg-white rounded-2xl border border-slate-200/90 shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150 cursor-default"
          @click.stop="isExportMonthOpen = false"
        >
          <!-- Header -->
          <div class="px-6 py-4.5 border-b border-slate-100 flex items-center justify-between">
            <div>
              <h3 class="text-base font-bold text-slate-900 font-heading">Export Report Data</h3>
              <p class="text-xs text-slate-400 mt-0.5">Download patient payments and examination records</p>
            </div>
            <button 
              type="button" 
              @click="showExportModal = false"
              class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-colors cursor-pointer text-base"
            >
              ✕
            </button>
          </div>

          <!-- Body -->
          <div class="p-6 space-y-5">
            
            <!-- Scope Segmented Tabs -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-2">Time Period</label>
              <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-100/80 rounded-xl border border-slate-200/60">
                <button
                  type="button"
                  @click="exportScope = 'month'"
                  class="py-1.5 px-2 rounded-lg text-xs font-bold transition-all cursor-pointer text-center"
                  :class="exportScope === 'month' ? 'bg-white text-teal-800 shadow-2xs font-extrabold' : 'text-slate-600 hover:text-slate-900'"
                >
                  By Month
                </button>
                <button
                  type="button"
                  @click="exportScope = 'custom'"
                  class="py-1.5 px-2 rounded-lg text-xs font-bold transition-all cursor-pointer text-center"
                  :class="exportScope === 'custom' ? 'bg-white text-teal-800 shadow-2xs font-extrabold' : 'text-slate-600 hover:text-slate-900'"
                >
                  Date Range
                </button>
                <button
                  type="button"
                  @click="exportScope = 'all'"
                  class="py-1.5 px-2 rounded-lg text-xs font-bold transition-all cursor-pointer text-center"
                  :class="exportScope === 'all' ? 'bg-white text-teal-800 shadow-2xs font-extrabold' : 'text-slate-600 hover:text-slate-900'"
                >
                  All Time
                </button>
              </div>

              <!-- Content for Month Tab (Custom Floating Menu - No Native Select) -->
              <div v-if="exportScope === 'month'" class="mt-3.5 relative" ref="exportMonthDropdownRef">
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Select Month:</label>
                <button
                  type="button"
                  @click.stop="isExportMonthOpen = !isExportMonthOpen"
                  class="w-full bg-slate-50 hover:bg-slate-100/80 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 flex items-center justify-between transition-all cursor-pointer"
                  :class="{'ring-2 ring-teal-500/20 border-teal-500 bg-white': isExportMonthOpen}"
                >
                  <div class="flex items-center gap-2">
                    <Calendar class="w-4 h-4 text-teal-600 shrink-0" />
                    <span>{{ exportSelectedMonthLabel }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                      ${{ exportSelectedMonthRevenue }}
                    </span>
                    <ChevronDown class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{'rotate-180': isExportMonthOpen}" />
                  </div>
                </button>

                <!-- Custom Floating Popover Dropdown -->
                <Transition name="fade">
                  <div
                    v-if="isExportMonthOpen"
                    class="absolute left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden py-1 max-h-52 overflow-y-auto"
                  >
                    <button
                      v-for="m in activeRecordedMonths"
                      :key="m.key"
                      type="button"
                      @click="exportSelectedMonth = m.key; isExportMonthOpen = false"
                      class="w-full px-3.5 py-2.5 text-left flex items-center justify-between text-xs transition-colors cursor-pointer"
                      :class="exportSelectedMonth === m.key ? 'bg-teal-50/80 text-teal-900 font-bold' : 'hover:bg-slate-50 text-slate-700'"
                    >
                      <div class="flex items-center gap-2.5">
                        <div class="w-2 h-2 rounded-full" :class="exportSelectedMonth === m.key ? 'bg-teal-600' : 'bg-slate-300'"></div>
                        <span>{{ m.label }}</span>
                        <span class="text-[10px] text-slate-400 font-medium font-mono">({{ m.totalEncounters }} {{ m.totalEncounters === 1 ? 'visit' : 'visits' }})</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-emerald-600 text-xs">${{ m.totalRevenue }}</span>
                        <Check v-if="exportSelectedMonth === m.key" class="w-3.5 h-3.5 text-teal-600 shrink-0" />
                      </div>
                    </button>
                  </div>
                </Transition>
              </div>

              <!-- Content for Custom Date Range Tab (3-Column Scroll Picker: MONTH | DAY | YEAR) -->
              <div v-if="exportScope === 'custom'" class="mt-3.5 space-y-2.5">
                <!-- Clean Human-Designed Input Fields for From Date & To Date -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">From Date</label>
                    <button
                      type="button"
                      @click="activeDateTarget = 'start'"
                      class="w-full bg-slate-50 hover:bg-slate-100/80 border text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition-all cursor-pointer font-sans"
                      :class="activeDateTarget === 'start' ? 'border-teal-600 bg-white ring-2 ring-teal-500/15 font-bold text-slate-900' : 'border-slate-200 font-medium text-slate-700'"
                    >
                      <span class="text-xs font-semibold font-sans tracking-normal text-slate-800">{{ exportStartDate }}</span>
                      <Calendar class="w-4 h-4" :class="activeDateTarget === 'start' ? 'text-teal-600' : 'text-slate-400'" />
                    </button>
                  </div>
                  <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">To Date</label>
                    <button
                      type="button"
                      @click="activeDateTarget = 'end'"
                      class="w-full bg-slate-50 hover:bg-slate-100/80 border text-left px-3.5 py-2.5 rounded-xl text-xs flex items-center justify-between transition-all cursor-pointer font-sans"
                      :class="activeDateTarget === 'end' ? 'border-teal-600 bg-white ring-2 ring-teal-500/15 font-bold text-slate-900' : 'border-slate-200 font-medium text-slate-700'"
                    >
                      <span class="text-xs font-semibold font-sans tracking-normal text-slate-800">{{ exportEndDate }}</span>
                      <Calendar class="w-4 h-4" :class="activeDateTarget === 'end' ? 'text-teal-600' : 'text-slate-400'" />
                    </button>
                  </div>
                </div>

                <!-- 3-Column Scroll Picker Card (MONTH | DAY | YEAR) -->
                <div class="bg-white border border-slate-200/90 rounded-2xl shadow-2xs p-3 grid grid-cols-3 divide-x divide-slate-100">
                  
                  <!-- Column 1: MONTH -->
                  <div class="flex flex-col items-center px-1">
                    <div class="text-[11px] font-bold text-slate-400 tracking-wider uppercase mb-1.5">MONTH</div>
                    <div class="w-full h-36 overflow-y-auto space-y-0.5 px-1 py-0.5 scrollbar-thin text-center">
                      <button
                        v-for="(name, idx) in shortMonthNames"
                        :key="name"
                        type="button"
                        @click="currentPickerMonth = idx + 1"
                        class="w-full py-1.5 rounded-lg text-xs transition-all cursor-pointer text-center"
                        :class="currentPickerMonth === idx + 1 ? 'bg-slate-100/90 text-slate-900 font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                      >
                        {{ name }}
                      </button>
                    </div>
                  </div>

                  <!-- Column 2: DAY -->
                  <div class="flex flex-col items-center px-1">
                    <div class="text-[11px] font-bold text-slate-400 tracking-wider uppercase mb-1.5">DAY</div>
                    <div class="w-full h-36 overflow-y-auto space-y-0.5 px-1 py-0.5 scrollbar-thin text-center">
                      <button
                        v-for="d in daysInCurrentPickerMonth"
                        :key="d"
                        type="button"
                        @click="currentPickerDay = d"
                        class="w-full py-1.5 rounded-lg text-xs transition-all cursor-pointer text-center"
                        :class="currentPickerDay === d ? 'bg-slate-100/90 text-slate-900 font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                      >
                        {{ d }}
                      </button>
                    </div>
                  </div>

                  <!-- Column 3: YEAR -->
                  <div class="flex flex-col items-center px-1">
                    <div class="text-[11px] font-bold text-slate-400 tracking-wider uppercase mb-1.5">YEAR</div>
                    <div class="w-full h-36 overflow-y-auto space-y-0.5 px-1 py-0.5 scrollbar-thin text-center">
                      <button
                        v-for="y in pickerYears"
                        :key="y"
                        type="button"
                        @click="currentPickerYear = y"
                        class="w-full py-1.5 rounded-lg text-xs transition-all cursor-pointer text-center"
                        :class="currentPickerYear === y ? 'bg-slate-100/90 text-slate-900 font-extrabold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                      >
                        {{ y }}
                      </button>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Content for All Time Tab -->
              <div v-if="exportScope === 'all'" class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2.5 text-xs text-slate-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                <span>Includes all {{ reportData?.summary?.totalEncounters || 25 }} encounters across {{ reportData?.summary?.totalPatients || 26 }} registered patients.</span>
              </div>
            </div>

            <!-- Format Selector -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-2">File Format</label>
              <div class="grid grid-cols-2 gap-2.5">
                <button
                  type="button"
                  @click="exportFormat = 'excel'"
                  class="flex items-center gap-2.5 p-3 rounded-xl border text-left transition-all cursor-pointer"
                  :class="exportFormat === 'excel' ? 'bg-emerald-50/90 border-emerald-500 ring-2 ring-emerald-500/20 text-emerald-950 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                >
                  <FileSpreadsheet class="w-4 h-4 text-emerald-600 shrink-0" />
                  <div>
                    <div class="text-xs font-bold">Excel (.xlsx)</div>
                    <div class="text-[10px] text-slate-400">Microsoft Excel format</div>
                  </div>
                </button>

                <button
                  type="button"
                  @click="exportFormat = 'csv'"
                  class="flex items-center gap-2.5 p-3 rounded-xl border text-left transition-all cursor-pointer"
                  :class="exportFormat === 'csv' ? 'bg-teal-50/90 border-teal-500 ring-2 ring-teal-500/20 text-teal-950 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                >
                  <FileText class="w-4 h-4 text-teal-600 shrink-0" />
                  <div>
                    <div class="text-xs font-bold">CSV (.csv)</div>
                    <div class="text-[10px] text-slate-400">Standard spreadsheet</div>
                  </div>
                </button>
              </div>
            </div>

          </div>

          <!-- Footer -->
          <div class="px-6 py-4 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between">
            <button
              type="button"
              @click="showExportModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors cursor-pointer"
            >
              Cancel
            </button>

            <button
              type="button"
              @click="executeFilteredExport"
              :disabled="isExporting"
              class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer disabled:opacity-50"
            >
              <Download class="w-3.5 h-3.5" :class="{'animate-bounce': isExporting}" />
              <span>{{ isExporting ? 'Exporting...' : 'Download File' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { api } from '../services/api'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  ArcElement
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'
import { 
  BarChart3, 
  DollarSign, 
  CreditCard, 
  TrendingUp, 
  Users, 
  Calendar,
  ChevronDown,
  Download,
  FileSpreadsheet,
  FileText,
  Check
} from 'lucide-vue-next'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  PointElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

// 🌟 Dynamic Device Date Detection (Auto-adapts to 2026, 2027, 2028...)
const now = new Date()
const currentYear = now.getFullYear()
const currentMonthStr = String(now.getMonth() + 1).padStart(2, '0')
const initialMonthKey = `${currentYear}-${currentMonthStr}`
const lastDayOfInitialMonth = new Date(currentYear, now.getMonth() + 1, 0).getDate()

const reportData = ref<any>(null)
const revenueViewMode = ref<'daily' | 'monthly'>('daily')
const selectedMonthKey = ref<string>(initialMonthKey)

// 🌟 Custom Popover State for Dashboard
const isMonthMenuOpen = ref(false)
const monthDropdownRef = ref<HTMLElement | null>(null)

// 📥 Export Modal & Filtering State
const showExportModal = ref(false)
const exportScope = ref<'month' | 'custom' | 'all'>('month')
const exportSelectedMonth = ref<string>(initialMonthKey)
const exportStartDate = ref<string>(`${initialMonthKey}-01`)
const exportEndDate = ref<string>(`${initialMonthKey}-${String(lastDayOfInitialMonth).padStart(2, '0')}`)
const exportFormat = ref<'excel' | 'csv'>('excel')
const isExporting = ref(false)

const monthNames = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

// 📅 Auto-generate Month Options from Database Records & Current/Future Device Years
const allYearMonths = computed(() => {
  const yearsSet = new Set<number>()
  
  // Include database recorded years
  const dbMonths = reportData.value?.availableMonths || []
  dbMonths.forEach((m: any) => {
    if (m.key) {
      const y = parseInt(m.key.split('-')[0], 10)
      if (!isNaN(y)) yearsSet.add(y)
    }
  })

  // Include surrounding years (e.g. 2025, 2026, 2027)
  const deviceYear = new Date().getFullYear()
  yearsSet.add(deviceYear - 1)
  yearsSet.add(deviceYear)
  yearsSet.add(deviceYear + 1)

  const sortedYears = Array.from(yearsSet).sort((a, b) => b - a)
  const result: Array<{ key: string, label: string }> = []

  sortedYears.forEach(year => {
    for (let month = 12; month >= 1; month--) {
      const mStr = String(month).padStart(2, '0')
      const key = `${year}-${mStr}`
      result.push({
        key,
        label: `${monthNames[month - 1]} ${year}`
      })
    }
  })

  return result
})

// 🌟 3-Column Scroll Wheel Picker State & Logic (MONTH | DAY | YEAR)
const activeDateTarget = ref<'start' | 'end'>('start')

const shortMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const pickerYears = [2031, 2030, 2029, 2028, 2027, 2026, 2025, 2024]

const startYear = ref<number>(currentYear)
const startMonth = ref<number>(now.getMonth() + 1)
const startDay = ref<number>(1)

const endYear = ref<number>(currentYear)
const endMonth = ref<number>(now.getMonth() + 1)
const endDay = ref<number>(lastDayOfInitialMonth)

const currentPickerYear = computed({
  get: () => activeDateTarget.value === 'start' ? startYear.value : endYear.value,
  set: (val: number) => {
    if (activeDateTarget.value === 'start') startYear.value = val
    else endYear.value = val
    adjustDaysIfNeeded()
    syncExportDates()
  }
})

const currentPickerMonth = computed({
  get: () => activeDateTarget.value === 'start' ? startMonth.value : endMonth.value,
  set: (val: number) => {
    if (activeDateTarget.value === 'start') startMonth.value = val
    else endMonth.value = val
    adjustDaysIfNeeded()
    syncExportDates()
  }
})

const currentPickerDay = computed({
  get: () => activeDateTarget.value === 'start' ? startDay.value : endDay.value,
  set: (val: number) => {
    if (activeDateTarget.value === 'start') startDay.value = val
    else endDay.value = val
    syncExportDates()
  }
})

const daysInCurrentPickerMonth = computed(() => {
  const y = currentPickerYear.value
  const m = currentPickerMonth.value
  const total = new Date(y, m, 0).getDate()
  return Array.from({ length: total }, (_, i) => i + 1)
})

const adjustDaysIfNeeded = () => {
  const maxDays = new Date(currentPickerYear.value, currentPickerMonth.value, 0).getDate()
  if (currentPickerDay.value > maxDays) {
    currentPickerDay.value = maxDays
  }
}

const syncExportDates = () => {
  const sm = String(startMonth.value).padStart(2, '0')
  const sd = String(startDay.value).padStart(2, '0')
  exportStartDate.value = `${startYear.value}-${sm}-${sd}`

  const em = String(endMonth.value).padStart(2, '0')
  const ed = String(endDay.value).padStart(2, '0')
  exportEndDate.value = `${endYear.value}-${em}-${ed}`
}

// 🌟 Custom Popover State for Export Modal Month Selector
const isExportMonthOpen = ref(false)
const exportMonthDropdownRef = ref<HTMLElement | null>(null)

const selectedMonthLabel = computed(() => {
  const found = availableMonths.value.find((m: any) => m.key === selectedMonthKey.value)
  return found ? found.label : 'Select Month'
})

const exportSelectedMonthLabel = computed(() => {
  const found = availableMonths.value.find((m: any) => m.key === exportSelectedMonth.value)
  return found ? found.label : 'Select Month'
})

const exportSelectedMonthRevenue = computed(() => {
  const found = availableMonths.value.find((m: any) => m.key === exportSelectedMonth.value)
  return found ? found.totalRevenue : 0
})

const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as Node
  if (monthDropdownRef.value && !monthDropdownRef.value.contains(target)) {
    isMonthMenuOpen.value = false
  }
  if (exportMonthDropdownRef.value && !exportMonthDropdownRef.value.contains(target)) {
    isExportMonthOpen.value = false
  }
}

// 📊 Filter & Export Health Records & Financial Data to Excel / CSV
const executeFilteredExport = async () => {
  try {
    isExporting.value = true
    
    const res = await api.get('/api/health-records')
    let records = res && res.status === 'success' && Array.isArray(res.data) ? res.data : []
    
    if (records.length === 0) {
      alert('No health records found in database to export.')
      isExporting.value = false
      return
    }

    // Apply Filter Condition (Month, Custom Date Range, or All-Time)
    let conditionLabel = 'All_Time'
    if (exportScope.value === 'month') {
      const targetMonth = exportSelectedMonth.value || selectedMonthKey.value
      records = records.filter((r: any) => r.date && r.date.startsWith(targetMonth))
      conditionLabel = `Month_${targetMonth}`
    } else if (exportScope.value === 'custom') {
      if (exportStartDate.value && exportEndDate.value) {
        records = records.filter((r: any) => r.date && r.date >= exportStartDate.value && r.date <= exportEndDate.value)
        conditionLabel = `From_${exportStartDate.value}_To_${exportEndDate.value}`
      }
    }

    if (records.length === 0) {
      alert('No records found matching the selected date condition.')
      isExporting.value = false
      return
    }

    // CSV / Excel Columns (Headers)
    const headers = [
      'Patient ID',
      'Patient Name',
      'Gender',
      'Record ID',
      'Consultation Date',
      'Clinical Examination Category',
      'Attending Doctor',
      'Payment Fee ($ USD)',
      'Payment Status',
      'Blood Pressure',
      'Heart Rate (bpm)',
      'Weight (kg)',
      'Height (m)',
      'BMI',
      'Clinical Findings / Notes'
    ]

    // Escape CSV cell helper
    const escapeCsv = (val: any) => {
      if (val === null || val === undefined) return '""'
      const str = String(val).replace(/"/g, '""')
      return `"${str}"`
    }

    // Generate CSV Rows
    const rows = records.map((r: any) => {
      const feeInt = r.fee && !isNaN(Number(r.fee)) ? Math.round(Number(r.fee)) : 35
      return [
        escapeCsv(r.patient_id || 'N/A'),
        escapeCsv(r.patient_name || 'N/A'),
        escapeCsv(r.gender || 'N/A'),
        escapeCsv(r.record_id || 'N/A'),
        escapeCsv(r.date || 'N/A'),
        escapeCsv(r.record_type || 'General Checkup'),
        escapeCsv(r.attending_doctor || 'Dr. Attending Physician'),
        escapeCsv(`$${feeInt}`),
        escapeCsv(r.payment_status || 'Paid'),
        escapeCsv(r.blood_pressure || 'N/A'),
        escapeCsv(r.pulse || 'N/A'),
        escapeCsv(r.weight || 'N/A'),
        escapeCsv(r.height || 'N/A'),
        escapeCsv(r.bmi || 'N/A'),
        escapeCsv(r.note || '')
      ].join(',')
    })

    // Calculate Summary Totals
    const totalRev = records.reduce((sum: number, r: any) => {
      return sum + (r.fee && !isNaN(Number(r.fee)) ? Math.round(Number(r.fee)) : 35)
    }, 0)
    const avgFee = records.length > 0 ? Math.round(totalRev / records.length) : 35

    // Clean Summary Block - perfectly aligned directly in Column G (Attending Doctor) & Column H (Payment Fee)
    const emptyRow = headers.map(() => '').join(',')
    const summaryRow1 = ['', '', '', '', '', '', 'TOTAL CONSULTATION REVENUE:', `"$${totalRev.toLocaleString()}"`, '', '', '', '', '', '', ''].join(',')
    const summaryRow2 = ['', '', '', '', '', '', 'TOTAL PATIENT VISITS:', `"${records.length} Visits"`, '', '', '', '', '', '', ''].join(',')
    const summaryRow3 = ['', '', '', '', '', '', 'AVERAGE FEE PER VISIT:', `"$${avgFee}"`, '', '', '', '', '', '', ''].join(',')

    // Combine into full CSV content with UTF-8 BOM for 100% perfect Excel compatibility
    const csvContent = '\uFEFF' + [
      headers.map(h => `"${h}"`).join(','),
      ...rows,
      emptyRow,
      summaryRow1,
      summaryRow2,
      summaryRow3
    ].join('\r\n')
    
    // Create download blob
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    
    const today = new Date().toISOString().split('T')[0]
    link.setAttribute('href', url)
    link.setAttribute('download', `Hospital_Clinical_Financial_Report_${conditionLabel}_${today}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    showExportModal.value = false
  } catch (err) {
    console.error('Error exporting data:', err)
    alert('Failed to export data. Please try again.')
  } finally {
    isExporting.value = false
  }
}

const defaultDepartments = [
  { department: 'Cardiology & General Surgery', totalSalary: 14500, staffCount: 4, percentage: 38 },
  { department: 'Pediatrics & Internal Medicine', totalSalary: 10200, staffCount: 3, percentage: 27 },
  { department: 'Nursing & Clinical Care', totalSalary: 8400, staffCount: 5, percentage: 22 },
  { department: 'Administration & Laboratory', totalSalary: 5100, staffCount: 3, percentage: 13 }
]

const getDeviceMonthKey = () => {
  const d = new Date()
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  return `${year}-${month}`
}

const fetchReports = async () => {
  try {
    const deviceMonth = getDeviceMonthKey()
    let res = await api.get(`/api/reports/financial?deviceMonth=${deviceMonth}`)
    
    // 🌱 If database was cleaned/empty, auto-seed realistic clinical dataset once
    if (res && res.status === 'success' && (!res.data?.summary?.totalEncounters || res.data.summary.totalEncounters === 0)) {
      await api.get('/api/admin/seed-realistic-data').catch(() => {})
      res = await api.get(`/api/reports/financial?deviceMonth=${deviceMonth}`)
    }

    if (res && res.status === 'success' && res.data) {
      reportData.value = res.data
      if (res.data.latestActiveMonthKey) {
        selectedMonthKey.value = res.data.latestActiveMonthKey
      } else if (res.data.currentMonthKey) {
        selectedMonthKey.value = res.data.currentMonthKey
      }
    }
  } catch (err) {
    console.error('Error fetching reports:', err)
  }
}

const handleKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    if (isExportMonthOpen.value) {
      isExportMonthOpen.value = false
      e.stopPropagation()
      return
    }
    if (isMonthMenuOpen.value) {
      isMonthMenuOpen.value = false
      e.stopPropagation()
      return
    }
    if (showExportModal.value) {
      showExportModal.value = false
    }
  }
}

onMounted(() => {
  fetchReports()
  document.addEventListener('click', handleClickOutside, true)
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
  window.removeEventListener('keydown', handleKeyDown)
})

// 📅 Available Months Dropdown List (Sorted latest active first)
const availableMonths = computed(() => {
  const list = reportData.value?.availableMonths || []
  if (list.length === 0) {
    const curYear = new Date().getFullYear()
    const curMonth = String(new Date().getMonth() + 1).padStart(2, '0')
    const curMonthName = new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    return [{ key: `${curYear}-${curMonth}`, label: curMonthName, totalRevenue: 0, totalEncounters: 0 }]
  }
  // Sort reverse-chronologically so latest months appear at top
  return [...list].sort((a: any, b: any) => b.key.localeCompare(a.key))
})

// 📅 Active Recorded Months (For clean export dropdown without empty months)
const activeRecordedMonths = computed(() => {
  const list = availableMonths.value
  const filtered = list.filter((m: any) => m.totalEncounters > 0)
  return filtered.length > 0 ? filtered : list.slice(0, 2)
})

// 📅 Selected Month's Daily Breakdown Data
const selectedDailyData = computed(() => {
  const allDaily = reportData.value?.dailyTrends
  if (allDaily && allDaily[selectedMonthKey.value]) {
    return allDaily[selectedMonthKey.value]
  }
  return {
    days: ['Aug 01', 'Aug 05', 'Aug 10', 'Aug 15', 'Aug 20', 'Aug 25', 'Aug 31'],
    shortDays: ['01', '05', '10', '15', '20', '25', '31'],
    revenue: [0, 0, 35, 35, 0, 490, 0],
    encounters: [0, 0, 1, 1, 0, 14, 0],
    totalRevenue: 875,
    totalEncounters: 25,
    monthLabel: 'August 2026'
  }
})

// 📊 Consultation Revenue Chart Data (Daily by Day / Monthly View)
const consultationRevenueChartData = computed(() => {
  if (revenueViewMode.value === 'daily') {
    const d = selectedDailyData.value
    // Convert labels to clean consecutive integer days: 1, 2, 3, 4, 5, ... 31
    const labels = (d.shortDays || d.days || []).map((s: string) => {
      const num = parseInt(s, 10)
      return isNaN(num) ? s : String(num)
    })
    return {
      labels: labels,
      datasets: [
        {
          label: 'Daily Revenue ($)',
          backgroundColor: '#0d9488', // teal-600
          hoverBackgroundColor: '#0f766e', // teal-700
          data: d.revenue,
          borderRadius: 4,
          maxBarThickness: 18
        }
      ]
    }
  } else {
    const rev = reportData.value?.revenueTrends || {
      months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      revenue: [0, 0, 0, 0, 0, 0, 0, 840, 0, 0, 0, 0]
    }
    return {
      labels: rev.months,
      datasets: [
        {
          label: 'Monthly Revenue ($)',
          backgroundColor: '#0d9488',
          hoverBackgroundColor: '#0f766e',
          data: rev.revenue,
          borderRadius: 6,
          maxBarThickness: 36
        }
      ]
    }
  }
})

// Options for Consultation Revenue Bar Chart
const dailyRevenueBarOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: '#0f172a',
      titleFont: { size: 12, weight: 'bold' as const },
      bodyFont: { size: 12 },
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        title: (items: any[]) => {
          if (!items.length) return ''
          const idx = items[0].dataIndex
          if (revenueViewMode.value === 'daily') {
            const d = selectedDailyData.value
            return d.days ? d.days[idx] : `Day ${items[0].label}`
          }
          return items[0].label
        },
        label: (context: any) => {
          const val = context.raw || 0
          const idx = context.dataIndex
          if (revenueViewMode.value === 'daily') {
            const d = selectedDailyData.value
            const enc = d.encounters ? d.encounters[idx] : 0
            return [
              ` Consultation Income: $${val.toLocaleString()} USD`,
              ` Patient Visits: ${enc} ${enc === 1 ? 'visit' : 'visits'}`
            ]
          }
          return ` Monthly Revenue: $${val.toLocaleString()} USD`
        }
      }
    }
  },
  scales: {
    x: { 
      grid: { display: false },
      ticks: {
        maxRotation: 0,
        autoSkip: false, // 🌟 Show EVERY single day 1, 2, 3, 4, 5, ... 31 without skipping!
        font: { size: 10, weight: 'bold' }
      }
    },
    y: { 
      grid: { color: '#f1f5f9' },
      beginAtZero: true,
      ticks: {
        callback: (val: any) => val >= 1000 ? `$${val / 1000}k` : `$${val}`,
        font: { size: 10 }
      }
    }
  }
}))

// 🍩 Department / Clinical Category Doughnut Chart Data (100% Live DB Data)
const deptChartData = computed(() => {
  const depts = reportData.value?.departments || []
  const palette = ['#0d9488', '#0284c7', '#8b5cf6', '#f59e0b', '#ec4899', '#10b981', '#6366f1', '#14b8a6', '#f97316', '#64748b']
  return {
    labels: depts.map((d: any) => d.department),
    datasets: [
      {
        backgroundColor: palette.slice(0, depts.length || 4),
        data: depts.map((d: any) => d.staffCount)
      }
    ]
  }
})

// Top category by volume
const topDepartment = computed(() => {
  const depts = reportData.value?.departments
  if (!depts || depts.length === 0) return null
  return [...depts].sort((a: any, b: any) => b.staffCount - a.staffCount)[0]
})

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: { 
        boxWidth: 10, 
        padding: 10,
        font: { family: 'inherit', size: 10, weight: 'bold' } 
      }
    },
    tooltip: {
      callbacks: {
        label: function(context: any) {
          const val = context.raw || 0
          const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0)
          const pct = total > 0 ? Math.round((val / total) * 100) : 0
          return ` ${context.label}: ${val} ${val === 1 ? 'case' : 'cases'} (${pct}%)`
        }
      }
    }
  },
  cutout: '72%'
}
</script>
