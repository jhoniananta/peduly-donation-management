<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionFeature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Log;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicPlan = Subscription::where('plan', 'free')->first();
     

        $feature = Feature::create([
            'name' => 'feature.assign',
            'description' => 'Memberikan kemampuan ke role',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'feature.unassign',
            'description' => 'Menghapus kemampuan dari role',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'role.index',
            'description' => 'Melihat semua role yang ada',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'role.create',
            'description' => 'Membuat role baru',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'role.edit',
            'description' => 'Mengedit role',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'role.delete',
            'description' => 'Menghapus role',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'role.assign',
            'description' => 'Assign role ke user',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
        
        $feature = Feature::create([
            'name' => 'role.unassign',
            'description' => 'UnAssign role dari user',
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'users.index',
            'description' => 'Melihat semua user'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'company.index',
            'description' => 'Melihat data company'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
        $feature = Feature::create([
            'name' => 'company.update',
            'description' => 'Mengubah data company'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
        $feature = Feature::create([
            'name' => 'users.create',
            'description' => 'Menambahkan data pengguna'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);

        $feature = Feature::create([
            'name' => 'users.edit',
            'description' => 'Mengubah data pengguna'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
      
        $feature = Feature::create([
            'name' => 'fundraising.create',
            'description' => 'Menambahkan data fundraising'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
        $feature = Feature::create([
            'name' => 'fundraising.edit',
            'description' => 'Mengubah data fundraising'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
        $feature = Feature::create([
            'name' => 'fundraising_news.create',
            'description' => 'Menambahkan data kabar berita fundraising'
        ]);
        SubscriptionFeature::create([
            'subscription_id' => $basicPlan->id,
            'feature_id' => $feature->id
        ]);
        
        $feature = Feature::create([
            'name' => 'company.verify',
            'description' => 'Memverifikasi company'
        ]);
    }
}
