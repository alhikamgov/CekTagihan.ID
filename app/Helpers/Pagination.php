<?php
declare(strict_types=1);

namespace App\Helpers;

class Pagination
{
    /**
     * Render pagination HTML.
     */
    public static function render(int $currentPage, int $lastPage, string $baseUrl, array $queryParams = []): string
    {
        if ($lastPage <= 1) {
            return '';
        }

        $buildUrl = function (int $page) use ($baseUrl, $queryParams): string {
            $params = array_merge($queryParams, ['page' => $page]);
            return $baseUrl . '?' . http_build_query($params);
        };

        $html = '<div class="flex items-center justify-between gap-2 mt-4 flex-wrap">';

        // Prev
        if ($currentPage > 1) {
            $html .= '<a href="' . htmlspecialchars($buildUrl($currentPage - 1)) . '" class="px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 transition">← Sebelumnya</a>';
        } else {
            $html .= '<span class="px-3 py-1.5 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-400 cursor-not-allowed">← Sebelumnya</span>';
        }

        // Info
        $html .= '<span class="text-sm text-gray-500">Halaman ' . $currentPage . ' dari ' . $lastPage . '</span>';

        // Next
        if ($currentPage < $lastPage) {
            $html .= '<a href="' . htmlspecialchars($buildUrl($currentPage + 1)) . '" class="px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-700 transition">Berikutnya →</a>';
        } else {
            $html .= '<span class="px-3 py-1.5 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-400 cursor-not-allowed">Berikutnya →</span>';
        }

        $html .= '</div>';
        return $html;
    }
}