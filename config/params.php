<?php

return [
    'adminEmail' => 'admin@example.com',
    // Ограничение на конвертируемый PDF файл
    'pdfFile' => [
        // Максимальный размер в байтах
        'maxSize' => 50 * 1024 * 1024, // 50Мб
        // Максимальное количество страниц
        'maxPage' => 20,
        // Время жизни загруженного файла, сек
        'ttl' => 60 *30, // 30 минут
    ],
];
