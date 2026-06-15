<?php
/**
 * Project: QazJumys
 * File: CategoryRepository.php
 * Author: Beck Sarbassov
 * Version: 1.0.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Reads the service categories used by the marketplace.
 * RU: Читает категории услуг, используемые маркетплейсом.
 */

declare(strict_types=1);

namespace QazJumys\Repositories;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * EN: Returns active categories in display order.
     * RU: Возвращает активные категории в порядке отображения.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT id, slug, name, description, accent_color FROM categories WHERE is_active = 1 ORDER BY sort_order, name');

        return $statement->fetchAll();
    }

    /**
     * EN: Provides local fallback categories before MySQL is configured.
     * RU: Предоставляет локальные категории до настройки MySQL.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fallback(): array
    {
        return [
            ['id' => 1, 'slug' => 'smm-content', 'name' => 'SMM және контент', 'description' => 'Әлеуметтік желі, мәтін, жоспар және бренд дауысы.', 'accent_color' => '#06b6d4'],
            ['id' => 2, 'slug' => 'target-performance', 'name' => 'Таргет / performance', 'description' => 'Жарнама кабинеттері, аудитория және нәтиже аналитикасы.', 'accent_color' => '#f43f5e'],
            ['id' => 3, 'slug' => 'website-development', 'name' => 'Сайт әзірлеу', 'description' => 'Landing page, корпоративтік сайт және интернет-дүкен.', 'accent_color' => '#2563eb'],
            ['id' => 4, 'slug' => 'seo-google-ads', 'name' => 'SEO / Google Ads', 'description' => 'Іздеу жүйесі, жарнама науқаны және конверсия.', 'accent_color' => '#22c55e'],
            ['id' => 5, 'slug' => 'mobile-reels', 'name' => 'Мобилограф / Reels', 'description' => 'Қысқа видео, түсірілім, сценарий және монтаж.', 'accent_color' => '#a855f7'],
            ['id' => 6, 'slug' => 'frontend-web', 'name' => 'Frontend / веб-әзірлеу', 'description' => 'HTML, CSS, JavaScript және интерфейс логикасы.', 'accent_color' => '#0ea5e9'],
            ['id' => 7, 'slug' => 'crm-automation', 'name' => 'CRM / автоматтандыру', 'description' => 'Процестер, интеграциялар және сату воронкасы.', 'accent_color' => '#14b8a6'],
            ['id' => 8, 'slug' => 'one-c-development', 'name' => '1C әзірлеу', 'description' => 'Есеп, интеграция, бизнес-логика және қолдау.', 'accent_color' => '#f59e0b'],
            ['id' => 9, 'slug' => 'ui-ux-graphic', 'name' => 'UI/UX және графдизайн', 'description' => 'Интерфейс, бренд визуалы және дизайн-жүйе.', 'accent_color' => '#ec4899'],
            ['id' => 10, 'slug' => 'video-motion', 'name' => 'Видеомонтаж / motion', 'description' => 'Ролик, анимация, титр және motion-графика.', 'accent_color' => '#6366f1'],
        ];
    }
}
