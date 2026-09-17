<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAttendanceAndPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'name' => 'John Employee',
            'email' => 'john@example.com',
            'role' => 'cashier',
            'salary' => 30000.00,
            'allowed_leaves' => 2,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_attendance_sheet(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('Daily Employee Attendance');
        $response->assertSee('John Employee');
    }

    public function test_admin_can_mark_attendance_for_employee(): void
    {
        $date = '2026-03-10';

        $response = $this->actingAs($this->admin)->post(route('admin.attendance.mark'), [
            'date' => $date,
            'attendances' => [
                $this->cashier->id => [
                    'status' => 'present',
                    'check_in' => '09:00',
                    'check_out' => '18:00',
                    'notes' => 'On time',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->cashier->id,
            'status' => 'present',
            'check_in' => '09:00',
            'check_out' => '18:00',
            'notes' => 'On time',
        ]);
    }

    public function test_monthly_payroll_calculation_with_excess_leaves(): void
    {
        // March has 31 days. Daily rate = 30000 / 31 = 967.74
        // Let's record 4 absents for John in March 2026. Allowed is 2.
        // Excess absences = 4 - 2 = 2 days.
        // Expected deduction = 2 * 967.74 = 1935.48
        // Net payable = 30000 - 1935.48 = 28064.52

        $dates = ['2026-03-01', '2026-03-02', '2026-03-03', '2026-03-04'];
        foreach ($dates as $d) {
            Attendance::create([
                'user_id' => $this->cashier->id,
                'date' => $d,
                'status' => 'absent',
            ]);
        }

        $payroll = $this->cashier->calculateMonthlyPayroll(2026, 3);

        $this->assertEquals(31, $payroll['total_days']);
        $this->assertEquals(30000.00, $payroll['base_salary']);
        $this->assertEquals(2, $payroll['allowed_leaves']);
        $this->assertEquals(4, $payroll['absent_count']);
        $this->assertEquals(4.0, $payroll['unpaid_absences']);
        $this->assertEquals(2.0, $payroll['excess_absences']);

        $expectedDailyRate = round(30000.00 / 31, 2);
        $expectedDeduction = round(2.0 * $expectedDailyRate, 2);
        $expectedNet = round(30000.00 - $expectedDeduction, 2);

        $this->assertEquals($expectedDailyRate, $payroll['daily_rate']);
        $this->assertEquals($expectedDeduction, $payroll['deduction_amount']);
        $this->assertEquals($expectedNet, $payroll['net_payable']);
    }

    public function test_monthly_payroll_no_deduction_within_allowed_threshold(): void
    {
        // 1 absent + 2 half days = 1 + (2 * 0.5) = 2 unpaid absence units.
        // Allowed threshold is 2 -> Excess is 0. Deduction is 0.
        Attendance::create([
            'user_id' => $this->cashier->id,
            'date' => '2026-03-05',
            'status' => 'absent',
        ]);
        Attendance::create([
            'user_id' => $this->cashier->id,
            'date' => '2026-03-06',
            'status' => 'half_day',
        ]);
        Attendance::create([
            'user_id' => $this->cashier->id,
            'date' => '2026-03-07',
            'status' => 'half_day',
        ]);

        $payroll = $this->cashier->calculateMonthlyPayroll(2026, 3);

        $this->assertEquals(2.0, $payroll['unpaid_absences']);
        $this->assertEquals(0.0, $payroll['excess_absences']);
        $this->assertEquals(0.0, $payroll['deduction_amount']);
        $this->assertEquals(30000.00, $payroll['net_payable']);
    }

    public function test_admin_can_disburse_salary_and_generate_slip(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.payroll.pay', $this->cashier->id), [
            'month_year' => '2026-03',
            'base_salary' => 30000.00,
            'deduction_amount' => 0.00,
            'bonus_amount' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-03-31',
            'notes' => 'March salary paid in cash with PKR 500 performance bonus',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('salary_payments', [
            'user_id' => $this->cashier->id,
            'month_year' => '2026-03',
            'base_salary' => 30000.00,
            'bonus_amount' => 500.00,
            'net_payable' => 30500.00,
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $payment = SalaryPayment::where('user_id', $this->cashier->id)->first();
        $slipResponse = $this->actingAs($this->admin)->get(route('admin.attendance.payroll.slip', $payment->id));
        $slipResponse->assertStatus(200);
        $slipResponse->assertSee('Salary Payment Voucher');
        $slipResponse->assertSee('John Employee');
        $slipResponse->assertSee('30,500');
    }
}
