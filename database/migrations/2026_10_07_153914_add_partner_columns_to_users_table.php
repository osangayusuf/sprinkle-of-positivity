<?php

use App\Enums\GroupMembershipRole;
use App\Enums\GroupMembershipStatus;
use App\Enums\PartnerStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('partner_status')->nullable()->after('onboarding_completed_at');
            $table->timestamp('partner_decided_at')->nullable()->after('partner_status');
        });

        // Group managers must be accountability partners, so anyone already
        // managing a group is converted rather than left without a manager.
        $roleId = DB::table('roles')->where('name', 'partner')->value('id')
            ?? DB::table('roles')->insertGetId(['name' => 'partner', 'created_at' => now(), 'updated_at' => now()]);

        $managerIds = DB::table('group_user')
            ->where('role', GroupMembershipRole::Manager->value)
            ->where('status', GroupMembershipStatus::Approved->value)
            ->pluck('user_id')
            ->unique();

        foreach ($managerIds as $userId) {
            DB::table('users')->where('id', $userId)->update([
                'partner_status' => PartnerStatus::Approved->value,
                'partner_decided_at' => now(),
            ]);

            DB::table('role_user')->insertOrIgnore(['role_id' => $roleId, 'user_id' => $userId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['partner_status', 'partner_decided_at']);
        });
    }
};
