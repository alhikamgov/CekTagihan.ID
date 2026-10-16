<?php
declare(strict_types=1);

namespace App\Helpers;

class Toast
{
    public static function render(): string
    {
        $html = '<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>';
        $html .= '<script>
        function showToast(message, type = "success") {
            const colors = {
                success: "bg-brand-500",
                error: "bg-red-500",
                warning: "bg-amber-500",
                info: "bg-blue-500"
            };
            const icons = {
                success: "check-circle",
                error: "alert-circle",
                warning: "alert-triangle",
                info: "info"
            };
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className = `${colors[type]} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 text-sm font-medium transition-all duration-300 opacity-0 translate-x-4`;
            toast.innerHTML = `<i data-lucide="${icons[type]}" class="w-4 h-4 flex-shrink-0"></i><span>${message}</span>`;
            container.appendChild(toast);
            if (window.lucide) lucide.createIcons();
            setTimeout(() => {
                toast.classList.remove("opacity-0", "translate-x-4");
            }, 10);
            setTimeout(() => {
                toast.classList.add("opacity-0", "translate-x-4");
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
        </script>';
        return $html;
    }
}