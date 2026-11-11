<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\GalleryCategory;
use App\Models\ReportCategory;
use App\Models\TeamCategory;
use App\Models\TrainingCategory;
use Illuminate\Database\Seeder;

class InitialCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            DocumentCategory::class => [
                ['slug' => 'sop-documents', 'name_en' => 'SOP Documents', 'name_hi' => 'मानक संचालन प्रक्रिया दस्तावेज़', 'description_en' => 'Standard operating procedures and process documents.', 'description_hi' => 'मानक संचालन प्रक्रियाएँ और प्रक्रिया संबंधी दस्तावेज़।'],
                ['slug' => 'guidelines', 'name_en' => 'Guidelines', 'name_hi' => 'दिशानिर्देश', 'description_en' => 'Guidance documents for public services and operations.', 'description_hi' => 'जनसेवाओं और संचालन के लिए मार्गदर्शन दस्तावेज़।'],
                ['slug' => 'circulars', 'name_en' => 'Circulars', 'name_hi' => 'परिपत्र', 'description_en' => 'Official circulars and departmental communications.', 'description_hi' => 'आधिकारिक परिपत्र और विभागीय संचार।'],
                ['slug' => 'government-orders', 'name_en' => 'Government Orders', 'name_hi' => 'शासनादेश', 'description_en' => 'Government orders and formal directives.', 'description_hi' => 'शासनादेश और औपचारिक निर्देश।'],
                ['slug' => 'application-forms', 'name_en' => 'Application Forms', 'name_hi' => 'आवेदन प्रपत्र', 'description_en' => 'Forms used to apply for public services.', 'description_hi' => 'जनसेवाओं के लिए आवेदन में उपयोग किए जाने वाले प्रपत्र।'],
                ['slug' => 'downloads', 'name_en' => 'Downloads', 'name_hi' => 'डाउनलोड', 'description_en' => 'Other public files available for download.', 'description_hi' => 'डाउनलोड के लिए उपलब्ध अन्य सार्वजनिक फ़ाइलें।'],
            ],
            TrainingCategory::class => [
                ['slug' => 'training-videos', 'name_en' => 'Training Videos', 'name_hi' => 'प्रशिक्षण वीडियो', 'description_en' => 'Video-based training and demonstrations.', 'description_hi' => 'वीडियो-आधारित प्रशिक्षण और प्रदर्शन।'],
                ['slug' => 'recorded-webinars', 'name_en' => 'Recorded Webinars', 'name_hi' => 'रिकॉर्ड किए गए वेबिनार', 'description_en' => 'Recordings of training and information webinars.', 'description_hi' => 'प्रशिक्षण और सूचना वेबिनार की रिकॉर्डिंग।'],
                ['slug' => 'ppt-presentations', 'name_en' => 'PPT Presentations', 'name_hi' => 'पीपीटी प्रस्तुतियाँ', 'description_en' => 'Presentation slides and related materials.', 'description_hi' => 'प्रस्तुति स्लाइड और संबंधित सामग्री।'],
                ['slug' => 'user-manuals', 'name_en' => 'User Manuals', 'name_hi' => 'उपयोगकर्ता पुस्तिकाएँ', 'description_en' => 'How-to guides and user manuals.', 'description_hi' => 'उपयोग निर्देश और उपयोगकर्ता पुस्तिकाएँ।'],
                ['slug' => 'faqs', 'name_en' => 'FAQs', 'name_hi' => 'अक्सर पूछे जाने वाले प्रश्न', 'description_en' => 'Frequently asked questions and answers.', 'description_hi' => 'अक्सर पूछे जाने वाले प्रश्न और उनके उत्तर।'],
                ['slug' => 'new-operator-induction', 'name_en' => 'New Operator Induction Materials', 'name_hi' => 'नए ऑपरेटरों के लिए परिचय सामग्री', 'description_en' => 'Orientation and onboarding resources for new operators.', 'description_hi' => 'नए ऑपरेटरों के परिचय और कार्यारंभ के लिए संसाधन।'],
            ],
            TeamCategory::class => [
                ['slug' => 'district-egovernance-team', 'name_en' => 'District e-Governance Team', 'name_hi' => 'जिला ई-गवर्नेंस टीम', 'description_en' => 'District-level e-governance team members.', 'description_hi' => 'जिला स्तर के ई-गवर्नेंस टीम सदस्य।'],
                ['slug' => 'block-coordinators', 'name_en' => 'Block Coordinators', 'name_hi' => 'ब्लॉक समन्वयक', 'description_en' => 'Coordinators serving at block level.', 'description_hi' => 'ब्लॉक स्तर पर कार्यरत समन्वयक।'],
                ['slug' => 'operators-list', 'name_en' => 'Operators List', 'name_hi' => 'ऑपरेटर सूची', 'description_en' => 'Publicly approved operator profiles.', 'description_hi' => 'सार्वजनिक प्रकाशन के लिए स्वीकृत ऑपरेटर प्रोफ़ाइल।'],
                ['slug' => 'roles-responsibilities', 'name_en' => 'Roles & Responsibilities', 'name_hi' => 'भूमिकाएँ एवं ज़िम्मेदारियाँ', 'description_en' => 'Team roles and responsibility information.', 'description_hi' => 'टीम की भूमिकाओं और ज़िम्मेदारियों की जानकारी।'],
            ],
            ReportCategory::class => [
                ['slug' => 'service-wise-reports', 'name_en' => 'Service-wise Reports', 'name_hi' => 'सेवा-वार रिपोर्ट', 'description_en' => 'Reports organized by public service.', 'description_hi' => 'जनसेवा के अनुसार व्यवस्थित रिपोर्ट।'],
                ['slug' => 'office-wise-reports', 'name_en' => 'Office-wise Reports', 'name_hi' => 'कार्यालय-वार रिपोर्ट', 'description_en' => 'Reports organized by office.', 'description_hi' => 'कार्यालय के अनुसार व्यवस्थित रिपोर्ट।'],
                ['slug' => 'csc-lsk-center-details', 'name_en' => 'CSC/LSK Center Details', 'name_hi' => 'सीएससी/एलएसके केंद्र विवरण', 'description_en' => 'Reports and information about CSC/LSK centers.', 'description_hi' => 'सीएससी/एलएसके केंद्रों की रिपोर्ट और जानकारी।'],
                ['slug' => 'inspection-reports', 'name_en' => 'Inspection Reports', 'name_hi' => 'निरीक्षण रिपोर्ट', 'description_en' => 'Inspection findings and related reports.', 'description_hi' => 'निरीक्षण निष्कर्ष और संबंधित रिपोर्ट।'],
                ['slug' => 'operator-details', 'name_en' => 'Operator Details', 'name_hi' => 'ऑपरेटर विवरण', 'description_en' => 'Reports related to operator information.', 'description_hi' => 'ऑपरेटर संबंधी जानकारी की रिपोर्ट।'],
                ['slug' => 'performance-ranking', 'name_en' => 'Performance Ranking', 'name_hi' => 'प्रदर्शन रैंकिंग', 'description_en' => 'Performance summaries and rankings.', 'description_hi' => 'प्रदर्शन सारांश और रैंकिंग।'],
            ],
            GalleryCategory::class => [
                ['slug' => 'training-photos', 'name_en' => 'Training Photos', 'name_hi' => 'प्रशिक्षण तस्वीरें', 'description_en' => 'Photographs from training sessions.', 'description_hi' => 'प्रशिक्षण सत्रों की तस्वीरें।'],
                ['slug' => 'workshop-photos', 'name_en' => 'Workshop Photos', 'name_hi' => 'कार्यशाला की तस्वीरें', 'description_en' => 'Photographs from workshops.', 'description_hi' => 'कार्यशालाओं की तस्वीरें।'],
                ['slug' => 'events-gallery', 'name_en' => 'Events Gallery', 'name_hi' => 'कार्यक्रम गैलरी', 'description_en' => 'Photographs from public and departmental events.', 'description_hi' => 'सार्वजनिक और विभागीय कार्यक्रमों की तस्वीरें।'],
                ['slug' => 'achievement-gallery', 'name_en' => 'Achievement Gallery', 'name_hi' => 'उपलब्धि गैलरी', 'description_en' => 'Approved photographs highlighting achievements.', 'description_hi' => 'उपलब्धियों को दर्शाने वाली स्वीकृत तस्वीरें।'],
            ],
        ];

        foreach ($categories as $model => $items) {
            foreach ($items as $index => $attributes) {
                $category = $model::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

                if (! $category->exists) {
                    $category->fill($attributes + [
                        'display_order' => ($index + 1) * 10,
                        'is_active' => true,
                    ])->save();
                } elseif ($category->trashed()) {
                    $category->restore();
                }
            }
        }
    }
}
