<?php

namespace Database\Seeders;

use App\Models\QuickLink;
use Illuminate\Database\Seeder;

class QuickLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'url' => '/documents', 'title_en' => 'Public documents', 'title_hi' => 'सार्वजनिक दस्तावेज़',
                'description_en' => 'Forms, notices and records', 'description_hi' => 'फ़ॉर्म, सूचनाएँ और अभिलेख', 'icon' => 'file-text',
            ],
            [
                'url' => '/training', 'title_en' => 'Training & learning', 'title_hi' => 'प्रशिक्षण एवं अध्ययन',
                'description_en' => 'Learning materials and resources', 'description_hi' => 'अध्ययन सामग्री और संसाधन', 'icon' => 'book-open',
            ],
            [
                'url' => '/reports', 'title_en' => 'Public reports', 'title_hi' => 'सार्वजनिक रिपोर्ट',
                'description_en' => 'Reports and publications', 'description_hi' => 'रिपोर्ट और प्रकाशन', 'icon' => 'chart',
            ],
            [
                'url' => '/contact', 'title_en' => 'Contact the district office', 'title_hi' => 'जिला कार्यालय से संपर्क करें',
                'description_en' => 'Office contact information', 'description_hi' => 'कार्यालय की संपर्क जानकारी', 'icon' => 'arrow-up-right',
            ],
        ];

        foreach ($links as $order => $link) {
            QuickLink::query()->firstOrCreate(
                ['url' => $link['url']],
                $link + [
                    'is_external' => false,
                    'opens_in_new_tab' => false,
                    'display_order' => ($order + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }
}
