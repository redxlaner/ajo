<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'monthly_contribution_amount' => '50000',
            'bank_name'                   => 'Zenith Bank',
            'account_number'              => '1234567890',
            'account_name'                => 'Course Purse Cooperative',
            'reactivation_fee'            => '10000',
            'unit_price'                  => '100',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}