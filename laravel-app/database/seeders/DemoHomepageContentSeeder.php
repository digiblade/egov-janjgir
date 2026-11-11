<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\HomepageUpdate;
use App\Models\Notice;
use Illuminate\Database\Seeder;

class DemoHomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            [
                'slug' => 'demo-public-consultation-ward-plan',
                'title_en' => '[DEMO DRAFT] Public consultation on the draft ward plan',
                'title_hi' => '[डेमो ड्राफ़्ट] प्रारूप वार्ड योजना पर जनपरामर्श',
                'description_en' => 'Sample content only. Add the approved date, venue, ward details and contact information before publication.',
                'description_hi' => 'यह केवल नमूना सामग्री है। प्रकाशित करने से पहले स्वीकृत तिथि, स्थान, वार्ड और संपर्क विवरण जोड़ें।',
                'notice_type' => 'public-consultation',
                'priority' => 'normal',
                'is_pinned' => false,
                'display_order' => 10,
            ],
            [
                'slug' => 'demo-citizen-service-hours',
                'title_en' => '[DEMO DRAFT] Citizen service hours and help desk information',
                'title_hi' => '[डेमो ड्राफ़्ट] नागरिक सेवा समय और सहायता केंद्र की जानकारी',
                'description_en' => 'Sample content only. Replace with verified office timings, service counter details and an official contact number.',
                'description_hi' => 'यह केवल नमूना सामग्री है। सत्यापित कार्यालय समय, सेवा काउंटर और आधिकारिक संपर्क नंबर दर्ज करें।',
                'notice_type' => 'general',
                'priority' => 'normal',
                'is_pinned' => false,
                'display_order' => 20,
            ],
            [
                'slug' => 'demo-public-health-information',
                'title_en' => '[DEMO DRAFT] Public health information and local service contacts',
                'title_hi' => '[डेमो ड्राफ़्ट] जनस्वास्थ्य जानकारी और स्थानीय सेवा संपर्क',
                'description_en' => 'Sample content only. Add information verified by the responsible department and current local service contacts.',
                'description_hi' => 'यह केवल नमूना सामग्री है। संबंधित विभाग से सत्यापित जानकारी और वर्तमान स्थानीय सेवा संपर्क जोड़ें।',
                'notice_type' => 'general',
                'priority' => 'normal',
                'is_pinned' => false,
                'display_order' => 30,
            ],
        ];

        foreach ($notices as $notice) {
            Notice::query()->firstOrCreate(
                ['slug' => $notice['slug']],
                $notice + ['status' => 'draft'],
            );
        }

        $announcements = [
            [
                'slug' => 'demo-citizen-service-camp',
                'title_en' => '[DEMO DRAFT] Citizen service camp announcement',
                'title_hi' => '[डेमो ड्राफ़्ट] नागरिक सेवा शिविर की घोषणा',
                'description_en' => 'Sample announcement only. Add the confirmed date, venue, available services and registration instructions before publication.',
                'description_hi' => 'यह केवल नमूना घोषणा है। प्रकाशित करने से पहले पुष्टि की गई तिथि, स्थान, उपलब्ध सेवाएँ और पंजीकरण निर्देश जोड़ें।',
                'priority' => 'normal',
                'display_order' => 10,
            ],
            [
                'slug' => 'demo-online-service-guide',
                'title_en' => '[DEMO DRAFT] Updated online service guide',
                'title_hi' => '[डेमो ड्राफ़्ट] अद्यतन ऑनलाइन सेवा मार्गदर्शिका',
                'description_en' => 'Sample announcement only. Replace this text with details of a verified service update and link to its official guide.',
                'description_hi' => 'यह केवल नमूना घोषणा है। सत्यापित सेवा अपडेट का विवरण और आधिकारिक मार्गदर्शिका का लिंक दें।',
                'priority' => 'normal',
                'display_order' => 20,
            ],
            [
                'slug' => 'demo-community-information-session',
                'title_en' => '[DEMO DRAFT] Community information session',
                'title_hi' => '[डेमो ड्राफ़्ट] सामुदायिक सूचना सत्र',
                'description_en' => 'Sample announcement only. Add the organizer, confirmed schedule, venue and accessibility information before publication.',
                'description_hi' => 'यह केवल नमूना घोषणा है। प्रकाशित करने से पहले आयोजक, पुष्टि किया गया समय, स्थान और सुगम्यता संबंधी जानकारी जोड़ें।',
                'priority' => 'normal',
                'display_order' => 30,
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::query()->firstOrCreate(
                ['slug' => $announcement['slug']],
                $announcement + ['status' => 'draft'],
            );
        }

        $updates = [
            [
                'title_en' => '[DEMO DRAFT] New citizen service information added',
                'title_hi' => '[डेमो ड्राफ़्ट] नागरिक सेवा की नई जानकारी जोड़ी गई',
                'summary_en' => 'Sample update. Replace with a short summary of a verified service change or new public resource.',
                'summary_hi' => 'नमूना अपडेट। सत्यापित सेवा परिवर्तन या नए सार्वजनिक संसाधन का संक्षिप्त विवरण दें।',
                'display_order' => 10,
            ],
            [
                'title_en' => '[DEMO DRAFT] Public consultation information prepared',
                'title_hi' => '[डेमो ड्राफ़्ट] जनपरामर्श की जानकारी तैयार',
                'summary_en' => 'Sample update. Add the approved consultation schedule and link to the related public notice.',
                'summary_hi' => 'नमूना अपडेट। स्वीकृत जनपरामर्श कार्यक्रम और संबंधित सार्वजनिक सूचना का लिंक जोड़ें।',
                'display_order' => 20,
            ],
            [
                'title_en' => '[DEMO DRAFT] Department contact information reviewed',
                'title_hi' => '[डेमो ड्राफ़्ट] विभागीय संपर्क जानकारी की समीक्षा',
                'summary_en' => 'Sample update. Replace with a verified summary and link to the updated department contact page.',
                'summary_hi' => 'नमूना अपडेट। सत्यापित संक्षिप्त विवरण और अद्यतन विभागीय संपर्क पृष्ठ का लिंक दें।',
                'display_order' => 30,
            ],
        ];

        foreach ($updates as $update) {
            HomepageUpdate::query()->firstOrCreate(
                ['title_en' => $update['title_en']],
                $update + ['status' => 'draft'],
            );
        }
    }
}
