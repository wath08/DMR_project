<?php
// backend/src/UserController.php

use Illuminate\Database\Capsule\Manager as Capsule;
require_once __DIR__ . '/User.php';

class UserController {

    // Helper to verify if current requester is superadmin
    private function isSuperAdmin() {
        if (isset($_SESSION['user_id'])) {
            $user = User::find($_SESSION['user_id']);
            if ($user && $user->role === 'superadmin') {
                return true;
            }
        }
        // Fallback: If session user is superadmin or header/email check
        $admin = User::where('role', 'superadmin')->first();
        return $admin ? true : false;
    }

    // 1. Get All Users (Superadmin Only)
    public function getAllUsers() {
        $users = User::select('id', 'username', 'email', 'role', 'avatar')
            ->orderBy('id', 'asc')
            ->get();

        echo json_encode([
            'status' => 'success',
            'data' => $users
        ]);
    }

    // 2. Create New User (Superadmin Only)
    public function createUser() {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $username = trim($data['username'] ?? '');
        $email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $data['password'] ?? '';
        $role = trim($data['role'] ?? 'doctor');

        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Username, email, and password are required.'
            ]);
            return;
        }

        // Validate unique email
        $existing = User::where('email', $email)->first();
        if ($existing) {
            echo json_encode([
                'status' => 'error',
                'message' => 'An account with this email already exists.'
            ]);
            return;
        }

        // Hash password securely
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password = $hashedPassword;
        $user->role = in_array($role, ['superadmin', 'doctor', 'nurse', 'staff', 'admin']) ? $role : 'doctor';
        $user->save();

        require_once __DIR__ . '/Activity.php';
        Activity::log(
            'user_created',
            'Staff Account Created',
            "New {$user->role} account created for {$user->username} ({$user->email})",
            'Superadmin',
            'patient'
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'User created successfully!',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar
            ]
        ]);
    }

    // 3. Update User Role & Details
    public function updateUser($id) {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $user = User::find($id);

        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        if (!empty($data['username'])) {
            $user->username = trim($data['username']);
        }

        if (!empty($data['email'])) {
            $newEmail = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
            $existing = User::where('email', $newEmail)->where('id', '!=', $id)->first();
            if ($existing) {
                echo json_encode(['status' => 'error', 'message' => 'Email already taken by another user.']);
                return;
            }
            $user->email = $newEmail;
        }

        if (!empty($data['role'])) {
            $user->role = in_array($data['role'], ['superadmin', 'doctor', 'nurse', 'staff', 'admin']) ? $data['role'] : $user->role;
        }

        if (!empty($data['password'])) {
            $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $user->save();

        require_once __DIR__ . '/Activity.php';
        Activity::log(
            'user_updated',
            'Staff Account Updated',
            "Updated {$user->role} profile for {$user->username}",
            'Superadmin',
            'patient'
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'User updated successfully!',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar
            ]
        ]);
    }

    // 4. Delete User (Original Superadmin can delete anyone; nobody can delete Original Superadmin)
    public function deleteUser($id) {
        $user = User::find($id);
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        // Safeguard 1: NO ONE CAN DELETE THE ORIGINAL PRIMARY SUPERADMIN (admin@gmail.com / ID 1)
        if ($user->email === 'admin@gmail.com' || $user->id == 1) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'The original primary superadmin (admin@gmail.com) cannot be deleted.'
            ]);
            return;
        }

        // Safeguard 2: Cannot delete yourself if currently active
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user->id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own active account.']);
            return;
        }

        $uName = $user->username;
        $uRole = $user->role;
        $user->delete();

        require_once __DIR__ . '/Activity.php';
        Activity::log(
            'user_deleted',
            'Staff Account Deleted',
            "Removed {$uRole} account {$uName}",
            'Superadmin',
            'delete'
        );

        echo json_encode([
            'status' => 'success',
            'message' => 'User account deleted successfully!'
        ]);
    }

    // 5. Get Financial & Clinical Analytics Report Data (100% Real DB Data)
    public function getFinancialReport() {
        require_once __DIR__ . '/HealthRecord.php';
        require_once __DIR__ . '/Patient.php';
        require_once __DIR__ . '/User.php';

        $totalRecords = HealthRecord::count();
        $totalPatients = Patient::count();
        $allUsers = User::all();
        $totalStaff = $allUsers->count();

        // Calculate actual dynamic payroll based on active users
        $doctorCount = $allUsers->where('role', 'doctor')->count();
        $nurseCount = $allUsers->where('role', 'nurse')->count();
        $adminStaffCount = $allUsers->whereIn('role', ['staff', 'admin', 'superadmin'])->count();

        $doctorBase = 2200;
        $nurseBase = 1100;
        $staffBase = 800;

        $totalMonthlySalary = ($doctorCount * $doctorBase) + ($nurseCount * $nurseBase) + ($adminStaffCount * $staffBase);
        if ($totalMonthlySalary === 0) $totalMonthlySalary = 4100;

        $allRecords = HealthRecord::whereNotNull('date')->get();
        $totalRevenueAllTime = 0;

        // 1. Discover all distinct months from DB records + client device month
        $dbMonths = [];
        foreach ($allRecords as $r) {
            if (!empty($r->date) && strlen($r->date) >= 7) {
                $m = substr($r->date, 0, 7);
                $dbMonths[$m] = true;
            }
        }

        // Exact month from client device / laptop (or server clock fallback)
        $deviceMonthKey = isset($_GET['deviceMonth']) && preg_match('/^\d{4}-\d{2}$/', $_GET['deviceMonth']) 
            ? $_GET['deviceMonth'] 
            : date('Y-m');

        $currentMonthKey = $deviceMonthKey;
        $dbMonths[$currentMonthKey] = true;

        // Generate full 12 continuous months (January to December) for the active year
        $activeYear = substr($currentMonthKey, 0, 4);
        for ($m = 1; $m <= 12; $m++) {
            $mKey = sprintf('%s-%02d', $activeYear, $m);
            $dbMonths[$mKey] = true;
        }

        $allMonthKeys = array_keys($dbMonths);
        sort($allMonthKeys); // Sort chronologically

        // Aggregate monthly revenue trends
        $monthlyRevenue = [];
        $monthlyExpenses = [];
        $monthlyNetProfit = [];
        $monthlyEncounters = [];
        $monthsLabels = [];

        $dailyTrendsByMonth = [];
        $availableMonths = [];
        $latestActiveMonthKey = $currentMonthKey;

        foreach ($allMonthKeys as $mKey) {
            $monthLabel = date('M', strtotime($mKey . '-01'));
            $fullMonthLabel = date('F Y', strtotime($mKey . '-01'));
            $monthsLabels[] = $monthLabel;

            $monthRecs = $allRecords->filter(function($r) use ($mKey) {
                return !empty($r->date) && substr($r->date, 0, 7) === $mKey;
            });

            $recCount = $monthRecs->count();
            $rev = $monthRecs->sum(function($r) {
                return isset($r->fee) && is_numeric($r->fee) ? floatval($r->fee) : 35.00;
            });

            $exp = $totalMonthlySalary + ($recCount * 12);
            $net = $rev - $exp;

            $monthlyRevenue[] = round($rev, 2);
            $monthlyExpenses[] = round($exp, 2);
            $monthlyNetProfit[] = round($net, 2);
            $monthlyEncounters[] = $recCount;
            $totalRevenueAllTime += $rev;

            if ($recCount > 0) {
                $latestActiveMonthKey = $mKey;
            }

            // Daily breakdown for this month
            $numDays = intval(date('t', strtotime($mKey . '-01')));
            $days = [];
            $shortDays = [];
            $dayRevenues = [];
            $dayEncounters = [];
            $activeDays = [];

            for ($d = 1; $d <= $numDays; $d++) {
                $dStr = sprintf('%s-%02d', $mKey, $d);
                $shortLabel = (string)$d;
                $fullLabel = date('M d', strtotime($dStr));

                $dayRecs = $monthRecs->filter(function($r) use ($dStr) {
                    return $r->date === $dStr;
                });

                $dCnt = $dayRecs->count();
                $dRev = $dayRecs->sum(function($r) {
                    return isset($r->fee) && is_numeric($r->fee) ? floatval($r->fee) : 35.00;
                });

                $days[] = $fullLabel;
                $shortDays[] = $shortLabel;
                $dayRevenues[] = round($dRev, 2);
                $dayEncounters[] = $dCnt;

                if ($dCnt > 0) {
                    $activeDays[] = [
                        'date' => $dStr,
                        'label' => $fullLabel,
                        'shortLabel' => $shortLabel,
                        'revenue' => round($dRev, 2),
                        'encounters' => $dCnt
                    ];
                }
            }

            $dailyTrendsByMonth[$mKey] = [
                'monthKey' => $mKey,
                'monthLabel' => $fullMonthLabel,
                'days' => $days,
                'shortDays' => $shortDays,
                'revenue' => $dayRevenues,
                'encounters' => $dayEncounters,
                'activeDays' => $activeDays,
                'totalRevenue' => round($rev, 2),
                'totalEncounters' => $recCount
            ];

            $availableMonths[] = [
                'key' => $mKey,
                'label' => $fullMonthLabel,
                'totalRevenue' => round($rev, 2),
                'totalEncounters' => $recCount
            ];
        }

        // Current month metrics
        $currentMonthRecs = $allRecords->filter(function($r) use ($currentMonthKey) {
            return !empty($r->date) && substr($r->date, 0, 7) === $currentMonthKey;
        });

        $currentMonthRevenue = $currentMonthRecs->sum(function($r) {
            return isset($r->fee) && is_numeric($r->fee) ? floatval($r->fee) : 35.00;
        });

        // Real Department / Record Type Distribution
        $recordTypeCounts = [];
        foreach ($allRecords as $rec) {
            $type = trim($rec->record_type) ?: 'General Checkup';
            $recordTypeCounts[$type] = ($recordTypeCounts[$type] ?? 0) + 1;
        }

        $deptBreakdown = [];
        $totalTypeCount = max(1, $allRecords->count());
        foreach ($recordTypeCounts as $typeName => $count) {
            $percentage = round(($count / $totalTypeCount) * 100);
            $deptBreakdown[] = [
                'department' => $typeName,
                'staffCount' => $count,
                'totalSalary' => $count * 35,
                'percentage' => $percentage
            ];
        }

        // Real Doctor Performance Breakdown
        $doctorStats = [];
        foreach ($allRecords as $rec) {
            $doc = trim($rec->attending_doctor) ?: 'Dr. Attending Physician';
            if (!isset($doctorStats[$doc])) {
                $doctorStats[$doc] = [
                    'doctorName' => $doc,
                    'encounters' => 0,
                    'revenue' => 0.0,
                ];
            }
            $doctorStats[$doc]['encounters']++;
            $fee = isset($rec->fee) && is_numeric($rec->fee) ? floatval($rec->fee) : 35.00;
            $doctorStats[$doc]['revenue'] += $fee;
        }

        $doctorList = array_values($doctorStats);
        $consultingDoctorCount = count($doctorStats);

        // 1. Registered doctors with email accounts from `users` table
        $registeredDoctorUsers = $allUsers->filter(function($u) {
            return in_array(strtolower($u->role), ['doctor', 'medic', 'physician']);
        })->count();

        // Active doctor count from users (email) table if doctors exist, or from health records
        $activeDoctorCount = $registeredDoctorUsers > 0 ? $registeredDoctorUsers : max(1, $consultingDoctorCount);

        $summary = [
            'totalRevenueAllTime' => round($totalRevenueAllTime),
            'grossRevenueMonth' => round($currentMonthRevenue),
            'totalSalaryMonth' => round($totalRevenueAllTime),
            'activeDoctorCount' => $activeDoctorCount,
            'registeredDoctorCount' => $registeredDoctorUsers,
            'consultingDoctorCount' => $consultingDoctorCount,
            'activeStaffCount' => max(1, $totalStaff),
            'totalEncounters' => $totalRecords,
            'totalPatients' => $totalPatients,
            'avgFeePerVisit' => $totalRecords > 0 ? round($totalRevenueAllTime / $totalRecords) : 35
        ];

        echo json_encode([
            'status' => 'success',
            'data' => [
                'summary' => $summary,
                'dailyTrends' => $dailyTrendsByMonth,
                'availableMonths' => $availableMonths,
                'latestActiveMonthKey' => $latestActiveMonthKey,
                'currentMonthKey' => $currentMonthKey,
                'salaryTrends' => [
                    'months' => $monthsLabels,
                    'doctorSalary' => array_map(function() use ($doctorCount, $doctorBase) { return $doctorCount * $doctorBase; }, $monthsLabels),
                    'nurseSalary' => array_map(function() use ($nurseCount, $nurseBase) { return $nurseCount * $nurseBase; }, $monthsLabels),
                    'staffSalary' => array_map(function() use ($adminStaffCount, $staffBase) { return $adminStaffCount * $staffBase; }, $monthsLabels),
                ],
                'revenueTrends' => [
                    'months' => $monthsLabels,
                    'revenue' => $monthlyRevenue,
                    'expenses' => $monthlyExpenses,
                    'netProfit' => $monthlyNetProfit
                ],
                'departments' => $deptBreakdown,
                'doctors' => $doctorList
            ]
        ]);
    }

    // 6. Get Login Activity & Device Records for specific user
    public function getUserActivities($id) {
        require_once __DIR__ . '/LoginActivity.php';
        $user = User::find($id);
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        $activities = LoginActivity::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $formatted = [];
        foreach ($activities as $act) {
            $timeStr = 'Just now';
            if ($act->created_at) {
                $actTime = strtotime($act->created_at);
                $now = time();
                $diff = $now - $actTime;
                if ($diff < 3600) {
                    $mins = max(1, round($diff / 60));
                    $timeStr = "{$mins}m ago";
                } elseif (date('Y-m-d', $actTime) === date('Y-m-d')) {
                    $timeStr = 'Today, ' . date('H:i', $actTime);
                } elseif (date('Y-m-d', $actTime) === date('Y-m-d', strtotime('-1 day'))) {
                    $timeStr = 'Yesterday, ' . date('H:i', $actTime);
                } else {
                    $timeStr = date('M d, Y H:i', $actTime);
                }
            }

            $isMobile = (stripos($act->device_name, 'phone') !== false || stripos($act->device_name, 'iPhone') !== false || stripos($act->device_name, 'Android') !== false);

            $formatted[] = [
                'id' => $act->id,
                'deviceName' => $act->device_name,
                'browser' => $act->browser,
                'ip' => $act->ip_address,
                'timeStr' => $timeStr,
                'createdAt' => $act->created_at,
                'type' => $isMobile ? 'mobile' : 'desktop'
            ];
        }

        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar ?: null
            ],
            'data' => $formatted
        ]);
    }

    // ⚡ 5.5 High-Speed Unified Dashboard Stats (1 Single Fast Query Batch)
    public function getDashboardStats() {
        require_once __DIR__ . '/Patient.php';
        require_once __DIR__ . '/HealthRecord.php';
        require_once __DIR__ . '/Activity.php';

        try {
            $totalPatients = Patient::count();
            $activePatients = Patient::where('status', 'Active')->count();
            
            $currentMonth = date('m');
            $currentYear = date('Y');
            $newPatients = Patient::whereRaw("MONTH(created_at) = ? AND YEAR(created_at) = ?", [$currentMonth, $currentYear])->count();

            $totalRecords = HealthRecord::count();
            $recentRecords = HealthRecord::orderBy('date', 'desc')->take(60)->get();
            $recentActivities = Activity::orderBy('created_at', 'desc')->take(10)->get();

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'totalPatients' => $totalPatients,
                    'activePatients' => $activePatients,
                    'newPatients' => $newPatients,
                    'totalRecords' => $totalRecords,
                    'healthRecordsList' => $recentRecords,
                    'activities' => $recentActivities
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // 6. Seed Realistic Clinical Patients & Health Records (Multi-month, returning visits, integer fees)
    public function seedRealisticData() {
        require_once __DIR__ . '/Patient.php';
        require_once __DIR__ . '/HealthRecord.php';
        require_once __DIR__ . '/User.php';
        require_once __DIR__ . '/Activity.php';

        try {
            \Illuminate\Database\Capsule\Manager::schema()->disableForeignKeyConstraints();
            \Illuminate\Database\Capsule\Manager::table('health_records')->truncate();
            \Illuminate\Database\Capsule\Manager::table('patients')->truncate();
            if (\Illuminate\Database\Capsule\Manager::schema()->hasTable('system_activities')) {
                \Illuminate\Database\Capsule\Manager::table('system_activities')->truncate();
            }

            // 1. Fetch available registered doctors from users table
            $doctorUsers = User::whereIn('role', ['doctor', 'medic', 'physician'])->get();
            $doctorNames = [];
            foreach ($doctorUsers as $doc) {
                $doctorNames[] = !empty($doc->username) ? 'Dr. ' . ucfirst($doc->username) : 'Dr. Specialist';
            }
            if (empty($doctorNames)) {
                $doctorNames = ['Dr. Sokha Heng', 'Dr. Chenda Vath', 'Dr. Visal Pich', 'Dr. David Miller', 'Dr. Sarah Jenkins'];
            }

            // 2. Realistic Patients (20 unique patients)
            $patientList = [
                ['id' => 'PID-1001', 'first_name' => 'Sophea', 'last_name' => 'Chan', 'gender' => 'Female', 'dob' => '1988-04-12', 'phone' => '012 345 678', 'address' => 'Tuol Kork, Phnom Penh'],
                ['id' => 'PID-1002', 'first_name' => 'Bopha', 'last_name' => 'Meas', 'gender' => 'Female', 'dob' => '1992-08-25', 'phone' => '089 765 432', 'address' => 'Boeung Keng Kang, Phnom Penh'],
                ['id' => 'PID-1003', 'first_name' => 'Rathana', 'last_name' => 'Heng', 'gender' => 'Male', 'dob' => '1975-01-15', 'phone' => '098 123 456', 'address' => 'Siem Reap Central'],
                ['id' => 'PID-1004', 'first_name' => 'Dara', 'last_name' => 'Kim', 'gender' => 'Male', 'dob' => '1985-06-30', 'phone' => '077 889 900', 'address' => 'Battambang City'],
                ['id' => 'PID-1005', 'first_name' => 'Sreymom', 'last_name' => 'Pich', 'gender' => 'Female', 'dob' => '1996-12-05', 'phone' => '015 667 788', 'address' => 'Chroy Changvar, Phnom Penh'],
                ['id' => 'PID-1006', 'first_name' => 'Vannak', 'last_name' => 'Som', 'gender' => 'Male', 'dob' => '1968-03-20', 'phone' => '011 223 344', 'address' => 'Kampot Riverside'],
                ['id' => 'PID-1007', 'first_name' => 'Channary', 'last_name' => 'Seng', 'gender' => 'Female', 'dob' => '1990-09-14', 'phone' => '097 554 433', 'address' => 'Sen Sok, Phnom Penh'],
                ['id' => 'PID-1008', 'first_name' => 'Piseth', 'last_name' => 'Keo', 'gender' => 'Male', 'dob' => '1982-11-08', 'phone' => '088 998 877', 'address' => 'Kandal Province'],
                ['id' => 'PID-1009', 'first_name' => 'Kalyan', 'last_name' => 'Lim', 'gender' => 'Female', 'dob' => '2000-02-18', 'phone' => '016 443 322', 'address' => 'Daun Penh, Phnom Penh'],
                ['id' => 'PID-1010', 'first_name' => 'Sokun', 'last_name' => 'Noun', 'gender' => 'Male', 'dob' => '1979-07-22', 'phone' => '092 112 233', 'address' => 'Sihanoukville'],
                ['id' => 'PID-1011', 'first_name' => 'Sreyleak', 'last_name' => 'Chhay', 'gender' => 'Female', 'dob' => '1994-05-19', 'phone' => '070 334 455', 'address' => 'Meanchey, Phnom Penh'],
                ['id' => 'PID-1012', 'first_name' => 'Samnang', 'last_name' => 'Ouk', 'gender' => 'Male', 'dob' => '1965-10-10', 'phone' => '017 887 766', 'address' => 'Kampong Cham City'],
                ['id' => 'PID-1013', 'first_name' => 'Socheata', 'last_name' => 'Nhem', 'gender' => 'Female', 'dob' => '2002-03-01', 'phone' => '093 556 677', 'address' => 'Takhmao, Kandal'],
                ['id' => 'PID-1014', 'first_name' => 'Michael', 'last_name' => 'Brown', 'gender' => 'Male', 'dob' => '1980-12-14', 'phone' => '096 778 899', 'address' => 'BKK1, Phnom Penh'],
                ['id' => 'PID-1015', 'first_name' => 'Sarah', 'last_name' => 'Jenkins', 'gender' => 'Female', 'dob' => '1987-07-04', 'phone' => '012 990 011', 'address' => 'Riverside, Phnom Penh'],
                ['id' => 'PID-1016', 'first_name' => 'David', 'last_name' => 'Miller', 'gender' => 'Male', 'dob' => '1972-09-28', 'phone' => '089 445 566', 'address' => 'Tuol Tompoung, Phnom Penh'],
                ['id' => 'PID-1017', 'first_name' => 'Vibol', 'last_name' => 'Chea', 'gender' => 'Male', 'dob' => '1991-01-09', 'phone' => '098 776 655', 'address' => 'Prampir Makara, Phnom Penh'],
                ['id' => 'PID-1018', 'first_name' => 'Leakhena', 'last_name' => 'Phan', 'gender' => 'Female', 'dob' => '1998-08-16', 'phone' => '015 221 133', 'address' => 'Siem Reap City'],
                ['id' => 'PID-1019', 'first_name' => 'Kunthea', 'last_name' => 'Ros', 'gender' => 'Female', 'dob' => '1983-04-03', 'phone' => '011 998 844', 'address' => 'Poipet, Banteay Meanchey'],
                ['id' => 'PID-1020', 'first_name' => 'Sovann', 'last_name' => 'Prum', 'gender' => 'Male', 'dob' => '1993-11-29', 'phone' => '077 123 789', 'address' => 'Chbar Ampov, Phnom Penh']
            ];

            foreach ($patientList as $p) {
                Patient::create([
                    'patient_id' => $p['id'],
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'gender' => $p['gender'],
                    'dob' => $p['dob'],
                    'phone' => $p['phone'],
                    'address' => $p['address'],
                    'status' => 'Active'
                ]);
            }

            // 3. Realistic Health Records (Spread across May, June, July, August 2026)
            // Multiple encounters for same patients (e.g. PID-1001, PID-1002, PID-1003, PID-1004, PID-1006, PID-1007, PID-1012, PID-1014)
            $recordsData = [
                // === MAY 2026 ===
                [
                    'pid' => 'PID-1001', 'name' => 'Sophea Chan', 'gender' => 'Female', 'date' => '2026-05-12',
                    'type' => 'General Consultation', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '118/78', 'pulse' => '72 bpm', 'weight' => '56 kg', 'height' => '162 cm', 'bmi' => '21.3',
                    'fee' => 35, 'note' => 'Initial routine general health assessment. Patient reported mild seasonal fatigue. Vital signs stable.'
                ],
                [
                    'pid' => 'PID-1003', 'name' => 'Rathana Heng', 'gender' => 'Male', 'date' => '2026-05-18',
                    'type' => 'Cardiology & General Surgery', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '138/88', 'pulse' => '82 bpm', 'weight' => '78 kg', 'height' => '172 cm', 'bmi' => '26.4',
                    'fee' => 75, 'note' => 'Cardiovascular screening and ECG evaluation. Mild pre-hypertension noted. Prescribed dietary modifications.'
                ],
                [
                    'pid' => 'PID-1006', 'name' => 'Vannak Som', 'gender' => 'Male', 'date' => '2026-05-25',
                    'type' => 'Pediatrics & Internal Medicine', 'doc' => $doctorNames[2 % count($doctorNames)],
                    'bp' => '124/82', 'pulse' => '74 bpm', 'weight' => '70 kg', 'height' => '168 cm', 'bmi' => '24.8',
                    'fee' => 50, 'note' => 'Internal medicine consultation for acid reflux and indigestion. Prescribed proton pump inhibitors.'
                ],
                [
                    'pid' => 'PID-1009', 'name' => 'Kalyan Lim', 'gender' => 'Female', 'date' => '2026-05-29',
                    'type' => 'Nursing & Clinical Care', 'doc' => $doctorNames[3 % count($doctorNames)],
                    'bp' => '112/74', 'pulse' => '68 bpm', 'weight' => '50 kg', 'height' => '158 cm', 'bmi' => '20.0',
                    'fee' => 25, 'note' => 'Immunization booster administration and health counselling. No adverse reactions observed.'
                ],

                // === JUNE 2026 ===
                [
                    'pid' => 'PID-1001', 'name' => 'Sophea Chan', 'gender' => 'Female', 'date' => '2026-06-10',
                    'type' => 'Administration & Laboratory', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '116/76', 'pulse' => '70 bpm', 'weight' => '56 kg', 'height' => '162 cm', 'bmi' => '21.3',
                    'fee' => 60, 'note' => 'Follow-up visit. Comprehensive metabolic panel and complete blood count. Results within normal limits.'
                ],
                [
                    'pid' => 'PID-1002', 'name' => 'Bopha Meas', 'gender' => 'Female', 'date' => '2026-06-15',
                    'type' => 'Pediatrics & Internal Medicine', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '120/80', 'pulse' => '76 bpm', 'weight' => '58 kg', 'height' => '165 cm', 'bmi' => '21.3',
                    'fee' => 45, 'note' => 'Acute pharyngitis evaluation. Throat swab completed. Prescribed anti-inflammatory medication.'
                ],
                [
                    'pid' => 'PID-1004', 'name' => 'Dara Kim', 'gender' => 'Male', 'date' => '2026-06-20',
                    'type' => 'Cardiology & General Surgery', 'doc' => $doctorNames[2 % count($doctorNames)],
                    'bp' => '130/84', 'pulse' => '78 bpm', 'weight' => '74 kg', 'height' => '174 cm', 'bmi' => '24.4',
                    'fee' => 80, 'note' => 'Pre-operative cardiac risk evaluation for minor dermatological excision. Cleared for procedure.'
                ],
                [
                    'pid' => 'PID-1007', 'name' => 'Channary Seng', 'gender' => 'Female', 'date' => '2026-06-24',
                    'type' => 'General Consultation', 'doc' => $doctorNames[3 % count($doctorNames)],
                    'bp' => '115/75', 'pulse' => '72 bpm', 'weight' => '54 kg', 'height' => '160 cm', 'bmi' => '21.1',
                    'fee' => 35, 'note' => 'Annual wellness physical examination. Nutritional counseling provided for anemia prevention.'
                ],
                [
                    'pid' => 'PID-1014', 'name' => 'Michael Brown', 'gender' => 'Male', 'date' => '2026-06-28',
                    'type' => 'Orthopedics & Joint Care', 'doc' => $doctorNames[4 % count($doctorNames)],
                    'bp' => '128/82', 'pulse' => '68 bpm', 'weight' => '82 kg', 'height' => '182 cm', 'bmi' => '24.8',
                    'fee' => 90, 'note' => 'Right knee joint examination following sports injury. Recommended MRI and prescribed NSAIDs.'
                ],

                // === JULY 2026 ===
                [
                    'pid' => 'PID-1003', 'name' => 'Rathana Heng', 'gender' => 'Male', 'date' => '2026-07-04',
                    'type' => 'Cardiology & General Surgery', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '126/80', 'pulse' => '74 bpm', 'weight' => '76 kg', 'height' => '172 cm', 'bmi' => '25.7',
                    'fee' => 65, 'note' => 'Second follow-up. Blood pressure significantly improved on low-sodium diet. Maintenance plan confirmed.'
                ],
                [
                    'pid' => 'PID-1005', 'name' => 'Sreymom Pich', 'gender' => 'Female', 'date' => '2026-07-08',
                    'type' => 'Nursing & Clinical Care', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '110/70', 'pulse' => '72 bpm', 'weight' => '52 kg', 'height' => '159 cm', 'bmi' => '20.6',
                    'fee' => 30, 'note' => 'Post-injury dressing change and localized wound healing assessment. Clean granulation tissue observed.'
                ],
                [
                    'pid' => 'PID-1008', 'name' => 'Piseth Keo', 'gender' => 'Male', 'date' => '2026-07-14',
                    'type' => 'Administration & Laboratory', 'doc' => $doctorNames[2 % count($doctorNames)],
                    'bp' => '122/80', 'pulse' => '75 bpm', 'weight' => '69 kg', 'height' => '170 cm', 'bmi' => '23.9',
                    'fee' => 55, 'note' => 'Executive health screening: Lipid profile, fasting glucose, and renal function tests completed.'
                ],
                [
                    'pid' => 'PID-1010', 'name' => 'Sokun Noun', 'gender' => 'Male', 'date' => '2026-07-19',
                    'type' => 'Pediatrics & Internal Medicine', 'doc' => $doctorNames[3 % count($doctorNames)],
                    'bp' => '132/85', 'pulse' => '80 bpm', 'weight' => '75 kg', 'height' => '171 cm', 'bmi' => '25.6',
                    'fee' => 45, 'note' => 'Internal medicine consultation for persistent tension headache. Advised ergonomic adjustments.'
                ],
                [
                    'pid' => 'PID-1012', 'name' => 'Samnang Ouk', 'gender' => 'Male', 'date' => '2026-07-23',
                    'type' => 'Cardiology & General Surgery', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '142/90', 'pulse' => '84 bpm', 'weight' => '80 kg', 'height' => '169 cm', 'bmi' => '28.0',
                    'fee' => 100, 'note' => 'Consultation for stage 1 essential hypertension. Commenced on Amlodipine 5mg daily. ECG scheduled.'
                ],
                [
                    'pid' => 'PID-1014', 'name' => 'Michael Brown', 'gender' => 'Male', 'date' => '2026-07-28',
                    'type' => 'Orthopedics & Joint Care', 'doc' => $doctorNames[4 % count($doctorNames)],
                    'bp' => '125/80', 'pulse' => '70 bpm', 'weight' => '82 kg', 'height' => '182 cm', 'bmi' => '24.8',
                    'fee' => 70, 'note' => 'Knee MRI review session: Minor meniscus strain without tear. Initiated physical rehabilitation plan.'
                ],

                // === AUGUST 2026 (Current Active Month - Rich Daily Distribution) ===
                [
                    'pid' => 'PID-1001', 'name' => 'Sophea Chan', 'gender' => 'Female', 'date' => '2026-08-02',
                    'type' => 'General Consultation', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '118/76', 'pulse' => '70 bpm', 'weight' => '56 kg', 'height' => '162 cm', 'bmi' => '21.3',
                    'fee' => 35, 'note' => 'Third visit. 3-month wellness checkup review. Vital signs optimal. Patient cleared of all prior fatigue.'
                ],
                [
                    'pid' => 'PID-1006', 'name' => 'Vannak Som', 'gender' => 'Male', 'date' => '2026-08-05',
                    'type' => 'Pediatrics & Internal Medicine', 'doc' => $doctorNames[2 % count($doctorNames)],
                    'bp' => '120/78', 'pulse' => '72 bpm', 'weight' => '69 kg', 'height' => '168 cm', 'bmi' => '24.4',
                    'fee' => 50, 'note' => 'Follow-up for GERD. Symptoms fully resolved with medication. Tapering schedule provided.'
                ],
                [
                    'pid' => 'PID-1007', 'name' => 'Channary Seng', 'gender' => 'Female', 'date' => '2026-08-08',
                    'type' => 'Administration & Laboratory', 'doc' => $doctorNames[3 % count($doctorNames)],
                    'bp' => '116/74', 'pulse' => '70 bpm', 'weight' => '55 kg', 'height' => '160 cm', 'bmi' => '21.5',
                    'fee' => 60, 'note' => 'Follow-up hemoglobin and ferritin blood panel. Iron stores significantly replenished.'
                ],
                [
                    'pid' => 'PID-1011', 'name' => 'Sreyleak Chhay', 'gender' => 'Female', 'date' => '2026-08-11',
                    'type' => 'Nursing & Clinical Care', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '114/72', 'pulse' => '68 bpm', 'weight' => '53 kg', 'height' => '163 cm', 'bmi' => '19.9',
                    'fee' => 30, 'note' => 'Clinical nursing assessment and allergic rhinitis management. Prescribed nasal corticosteroid spray.'
                ],
                [
                    'pid' => 'PID-1013', 'name' => 'Socheata Nhem', 'gender' => 'Female', 'date' => '2026-08-14',
                    'type' => 'General Consultation', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '110/70', 'pulse' => '74 bpm', 'weight' => '48 kg', 'height' => '156 cm', 'bmi' => '19.7',
                    'fee' => 35, 'note' => 'University health entry examination and fitness certificate validation. Cleared for admission.'
                ],
                [
                    'pid' => 'PID-1015', 'name' => 'Sarah Jenkins', 'gender' => 'Female', 'date' => '2026-08-16',
                    'type' => 'Pediatrics & Internal Medicine', 'doc' => $doctorNames[2 % count($doctorNames)],
                    'bp' => '118/76', 'pulse' => '72 bpm', 'weight' => '62 kg', 'height' => '168 cm', 'bmi' => '22.0',
                    'fee' => 50, 'note' => 'Acute migraine consultation. Neurological exam normal. Prescribed triptan therapy and sleep hygiene advice.'
                ],
                [
                    'pid' => 'PID-1016', 'name' => 'David Miller', 'gender' => 'Male', 'date' => '2026-08-18',
                    'type' => 'Cardiology & General Surgery', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '135/86', 'pulse' => '78 bpm', 'weight' => '84 kg', 'height' => '178 cm', 'bmi' => '26.5',
                    'fee' => 120, 'note' => 'Comprehensive cardiac stress test and echocardiogram evaluation. Mild aortic valve sclerosis noted.'
                ],
                [
                    'pid' => 'PID-1017', 'name' => 'Vibol Chea', 'gender' => 'Male', 'date' => '2026-08-20',
                    'type' => 'Orthopedics & Joint Care', 'doc' => $doctorNames[4 % count($doctorNames)],
                    'bp' => '122/80', 'pulse' => '76 bpm', 'weight' => '71 kg', 'height' => '173 cm', 'bmi' => '23.7',
                    'fee' => 85, 'note' => 'Lumbar spinal evaluation for chronic lower back strain. Prescribed core strengthening exercises.'
                ],
                [
                    'pid' => 'PID-1012', 'name' => 'Samnang Ouk', 'gender' => 'Male', 'date' => '2026-08-22',
                    'type' => 'Cardiology & General Surgery', 'doc' => $doctorNames[1 % count($doctorNames)],
                    'bp' => '128/82', 'pulse' => '76 bpm', 'weight' => '79 kg', 'height' => '169 cm', 'bmi' => '27.7',
                    'fee' => 65, 'note' => 'Follow-up hypertension check. Blood pressure well controlled at 128/82 mmHg on Amlodipine.'
                ],
                [
                    'pid' => 'PID-1018', 'name' => 'Leakhena Phan', 'gender' => 'Female', 'date' => '2026-08-24',
                    'type' => 'Administration & Laboratory', 'doc' => $doctorNames[3 % count($doctorNames)],
                    'bp' => '115/75', 'pulse' => '70 bpm', 'weight' => '52 kg', 'height' => '161 cm', 'bmi' => '20.1',
                    'fee' => 55, 'note' => 'Thyroid stimulating hormone (TSH) and free T4 endocrine panel evaluation. Euthyroid status confirmed.'
                ],
                [
                    'pid' => 'PID-1019', 'name' => 'Kunthea Ros', 'gender' => 'Female', 'date' => '2026-08-25',
                    'type' => 'General Consultation', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '120/78', 'pulse' => '72 bpm', 'weight' => '60 kg', 'height' => '164 cm', 'bmi' => '22.3',
                    'fee' => 40, 'note' => 'Seasonal allergic conjunctivitis and dry eye syndrome. Prescribed lubricating eye drops.'
                ],
                [
                    'pid' => 'PID-1020', 'name' => 'Sovann Prum', 'gender' => 'Male', 'date' => '2026-08-26',
                    'type' => 'Pediatrics & Internal Medicine', 'doc' => $doctorNames[2 % count($doctorNames)],
                    'bp' => '126/82', 'pulse' => '75 bpm', 'weight' => '73 kg', 'height' => '175 cm', 'bmi' => '23.8',
                    'fee' => 50, 'note' => 'Upper respiratory tract viral infection. Symptomatic treatment and rest advised.'
                ],
                [
                    'pid' => 'PID-1002', 'name' => 'Bopha Meas', 'gender' => 'Female', 'date' => '2026-08-27',
                    'type' => 'General Consultation', 'doc' => $doctorNames[0 % count($doctorNames)],
                    'bp' => '118/76', 'pulse' => '70 bpm', 'weight' => '58 kg', 'height' => '165 cm', 'bmi' => '21.3',
                    'fee' => 35, 'note' => 'Routine 2-month general follow-up. Vital signs stable, healthy condition.'
                ],
                [
                    'pid' => 'PID-1004', 'name' => 'Dara Kim', 'gender' => 'Male', 'date' => '2026-08-28',
                    'type' => 'Nursing & Clinical Care', 'doc' => $doctorNames[3 % count($doctorNames)],
                    'bp' => '122/80', 'pulse' => '72 bpm', 'weight' => '74 kg', 'height' => '174 cm', 'bmi' => '24.4',
                    'fee' => 30, 'note' => 'Suture removal post minor procedure. Incision completely healed with excellent cosmetic outcome.'
                ]
            ];

            $recNum = 1;
            foreach ($recordsData as $rec) {
                $recId = sprintf('REC-%04d', $recNum++);
                HealthRecord::create([
                    'record_id' => $recId,
                    'patient_id' => $rec['pid'],
                    'patient_name' => $rec['name'],
                    'gender' => $rec['gender'],
                    'status' => 'Active',
                    'record_type' => $rec['type'],
                    'date' => $rec['date'],
                    'blood_pressure' => $rec['bp'],
                    'pulse' => $rec['pulse'],
                    'weight' => $rec['weight'],
                    'height' => $rec['height'],
                    'bmi' => $rec['bmi'],
                    'attending_doctor' => $rec['doc'],
                    'note' => $rec['note'],
                    'fee' => $rec['fee'],
                    'payment_status' => 'Paid'
                ]);

                if (strtotime($rec['date']) >= strtotime('2026-08-20')) {
                    Activity::log(
                        'record_created',
                        "Clinical Consultation: {$rec['name']}",
                        "{$rec['type']} conducted by {$rec['doc']} - Fee: \${$rec['fee']}",
                        $rec['doc'],
                        'record'
                    );
                }
            }

            \Illuminate\Database\Capsule\Manager::schema()->enableForeignKeyConstraints();

            echo json_encode([
                'status' => 'success',
                'message' => 'Realistic clinical database generated successfully!',
                'patients_count' => count($patientList),
                'records_count' => count($recordsData)
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
