<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * محصولات نمونه — بر اساس محصولات پروژه اولیه
     */
    public function run(): void
    {
        // کپی تصاویر نمونه به دیسک public در صورت نبود
        if (! Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $products = [
            [
                'title' => 'آچار شبکه حرفه‌ای',
                'slug' => 'network-crimper-pro',
                'category' => 'tools',
                'price' => 2000000,
                'stock' => 12,
                'image' => 'network-crimper-pro.webp',
                'description' => 'آچار شبکه حرفه‌ای مناسب کریمپ کردن کابل‌های شبکه با دقت بالا. بدنه فلزی مقاوم با دسته ارگونومیک ضدلغزش که خستگی دست را در کارهای طولانی کاهش می‌دهد. سازگار با کانکتورهای RJ45 و RJ11 و مناسب برای نصاب‌های شبکه و علاقه‌مندان به تجهیزات شبکه.',
            ],
            [
                'title' => 'مودم پرسرعت',
                'slug' => 'adsl-modem',
                'category' => 'modems-routers',
                'price' => 1000000,
                'stock' => 8,
                'image' => 'modem.webp',
                'description' => 'مودم پرسرعت با پشتیبانی از استانداردهای جدید شبکه، مناسب برای استفاده خانگی و اداری. دارای آنتن‌های قدرتمند، پورت‌های گیگابیت و تنظیمات آسان از طریق پنل کاربری. پوشش‌دهی عالی و ثبات اتصال در ساعات پیک مصرف.',
            ],
            [
                'title' => 'سوییچ شبکه',
                'slug' => 'network-switch',
                'category' => 'switches-hubs',
                'price' => 4000000,
                'stock' => 5,
                'image' => 'network-switch.webp',
                'description' => 'سوییچ شبکه مدیریتی با پورت‌های پرسرعت برای سازمان‌دهی زیرساخت شبکه. قابلیت مدیریت ترافیک، پشتیبانی از VLAN و بدنه فلزی استاندارد رک ۱۹ اینچ. انتخابی مطمئن برای دفاتر و شبکه‌های متوسط.',
            ],
            [
                'title' => 'کابل فیبر نوری',
                'slug' => 'fiber-optic-cable',
                'category' => 'cables-connectors',
                'price' => 500000,
                'stock' => 30,
                'image' => 'fiber-optic-cable.webp',
                'description' => 'کابل فیبر نوری با کیفیت ساخت بالا برای انتقال داده با بالاترین سرعت و تاخیر نزدیک به صفر. مقاوم در برابر تداخل الکترومغناطیسی و مناسب برای اتصالات بین‌ساختمانی و دیتاسنترها. دارای روکش محافظ ضدضربه.',
            ],
            [
                'title' => 'محافظ سوکت',
                'slug' => 'socket-protector',
                'category' => 'accessories',
                'price' => 600000,
                'stock' => 20,
                'image' => 'socket-protector.webp',
                'description' => 'محافظ سوکت هوشمند برای حفاظت از تجهیزات حساس شبکه در برابر نوسانات برق و ولتاژهای لحظه‌ای. مجهز به فیوز محافظ و نشانگر عملکرد. با استفاده از این محافظ، عمر مفید مودم، روتر و سایر تجهیزات دیجیتال به‌طور محسوسی افزایش می‌یابد.',
            ],
            [
                'title' => 'کابل شبکه Cat6',
                'slug' => 'cat6-cable',
                'category' => 'cables-connectors',
                'price' => 200000,
                'stock' => 50,
                'image' => 'cat6-cable.webp',
                'description' => 'کابل شبکه Cat6 با پهنای باند تا ۲۵۰ مگاهرتز و پشتیبانی از سرعت گیگابیت. هادی مسی خالص با روکش باکیفیت و مناسب برای کابل‌کشی داخلی و ساخت پچ‌کورد. تولید‌شده مطابق با استاندارد TIA/EIA.',
            ],
            [
                'title' => 'تستر شبکه',
                'slug' => 'network-tester',
                'category' => 'tools',
                'price' => 700000,
                'stock' => 15,
                'image' => 'network-tester.webp',
                'description' => 'تستر شبکه برای بررسی سلامت و ترتیب سیم‌های کابل‌های شبکه. نمایشگر LED وضعیت هر زوج سیم را به‌صورت واضح نشان می‌دهد. سبک، قابل حمل و ابزار ضروری هر نصاب شبکه.',
            ],
            [
                'title' => 'آچار شبکه کریمپ',
                'slug' => 'network-crimper',
                'category' => 'tools',
                'price' => 670000,
                'stock' => 18,
                'image' => 'network-crimper.webp',
                'description' => 'آچار کریمپ اقتصادی با کیفیت مطلوب برای کارهای روزمره شبکه. تیغه فولادی سخت‌کاری‌شده با عمر طولانی و سازگار با کانکتورهای RJ45 و RJ12 و RJ11. گزینه‌ای مقرون‌به‌صرفه برای شروع کار.',
            ],
        ];

        foreach ($products as $item) {
            $category = Category::where('slug', $item['category'])->first();
            unset($item['category']);

            $this->copyImage($item['image']);

            Product::updateOrCreate(
                ['slug' => $item['slug']],
                $item + ['category_id' => $category->id, 'is_active' => true]
            );
        }
    }

    /**
     * کپی تصویر نمونه از پوشه seeder به دیسک storage
     */
    private function copyImage(string $filename): void
    {
        $source = database_path('seeders/images/'.$filename);

        if (file_exists($source) && ! Storage::disk('public')->exists('products/'.$filename)) {
            Storage::disk('public')->put('products/'.$filename, file_get_contents($source));
        }
    }
}
