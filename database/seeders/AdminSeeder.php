<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────
        if (DB::table('admins')->count() === 0) {
            DB::table('admins')->insert([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'status' => 'enable',
                'admin_type' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Demo User ──────────────────────────
        if (DB::table('users')->where('email', 'user@example.com')->doesntExist()) {
            DB::table('users')->insert([
                'name' => 'Demo User',
                'email' => 'user@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'enable',
                'is_banned' => 'no',
                'is_seller' => '0',
                'instructor_experience' => 0,
                'instructor_joining_request' => 'not_yet',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Demo Agency ────────────────────────
        if (DB::table('users')->where('email', 'agency@example.com')->doesntExist()) {
            DB::table('users')->insert([
                'name' => 'Demo Agency',
                'email' => 'agency@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'enable',
                'is_banned' => 'no',
                'is_seller' => '1',
                'instructor_experience' => 0,
                'instructor_joining_request' => 'approved',
                'agency_name' => 'Demo Travel Agency',
                'agency_slug' => 'demo-travel-agency',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
