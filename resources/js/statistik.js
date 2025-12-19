// ============================================
// resources/js/statistik.js
// ============================================

document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ Statistik.js loaded");

    // ============================================
    // 1. GET CSRF TOKEN
    // ============================================
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || "";
    }

    // ============================================
    // 2. FETCH API HELPER (dengan error handling)
    // ============================================
    window.fetchStatistik = async function (url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
                ...options,
            });

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`
                );
            }

            const data = await response.json();
            console.log("✅ API Response:", data);
            return data;
        } catch (error) {
            console.error("❌ Fetch Error:", error);
            window.showToast("Gagal memuat data: " + error.message, "error");
            throw error;
        }
    };

    // 3. FETCH WORKSPACE DATA
    window.fetchWorkspaceData = async function (
        workspaceId,
        filter = "todo",
        periodStart = null,
        periodEnd = null
    ) {
        let url = `/api/statistik/workspace/${workspaceId}?filter=${filter}`;
        if (periodStart && periodEnd) {
            url += `&start=${periodStart}&end=${periodEnd}`;
        }
        return await window.fetchStatistik(url);
    };

    // ============================================
    // 4. FETCH MEMBER DATA
    // ============================================
    window.fetchMemberData = async function (
        workspaceId,
        memberId,
        filter = "todo",
        periodStart = null, // ✅ Tambah
        periodEnd = null // ✅ Tambah
    ) {
        let url = `/api/statistik/member/${memberId}?workspace_id=${workspaceId}&filter=${filter}`;
        if (periodStart && periodEnd) {
            url += `&start=${periodStart}&end=${periodEnd}`;
        }
        return await window.fetchStatistik(url);
    };

    // 5. FETCH TASKS BY FILTER
    window.fetchTasksByFilter = async function (
        workspaceId,
        filter,
        memberId = null,
        periodStart = null,
        periodEnd = null
    ) {
        let url = `/api/statistik/tasks?workspace_id=${workspaceId}&filter=${filter}`;
        if (memberId) {
            url += `&member_id=${memberId}`;
        }
        if (periodStart && periodEnd) {
            url += `&start=${periodStart}&end=${periodEnd}`;
        }
        return await window.fetchStatistik(url);
    };

    // ============================================
    // 6. FETCH PERIODE DATA
    // ============================================
    window.fetchPeriodeData = async function (
        workspaceId,
        periodStart,
        periodEnd,
        filter = "todo",
        memberId = null
    ) {
        let url = `/api/statistik/periode?workspace_id=${workspaceId}&start=${periodStart}&end=${periodEnd}&filter=${filter}`;
        if (memberId) {
            url += `&member_id=${memberId}`;
        }
        return await window.fetchStatistik(url);
    };

    // ============================================
    // 7. ANIMATE PROGRESS CIRCLE
    // ============================================
    window.animateProgressCircle = function (element, percentage) {
        if (!element) return;

        const path =
            element.querySelector(".progress-bar") ||
            element.querySelector("path[stroke-dasharray]");
        const text =
            element.querySelector(".progress-text") ||
            element.querySelector("text");

        if (!path) return;

        const value = Math.min(Math.max(percentage, 0), 100);
        const radius = 15.9155;
        const circumference = 2 * Math.PI * radius;

        // Animate dengan transition
        path.style.transition = "stroke-dashoffset 0.6s ease-out";
        path.style.strokeDasharray = `${circumference}`;
        path.style.strokeDashoffset = `${
            circumference - (value / 100) * circumference
        }`;

        if (text) {
            text.textContent = value + "%";
        }
    };

    // ============================================
    // 8. INIT ALL PROGRESS CIRCLES
    // ============================================
    window.initProgressCircles = function () {
        document
            .querySelectorAll(".progress-circle, [data-progress]")
            .forEach((circle) => {
                const progress =
                    parseInt(circle.getAttribute("data-progress")) || 0;
                window.animateProgressCircle(circle, progress);
            });
    };

    // ============================================
    // 9. FORMAT DATE (Indonesia)
    // ============================================
    window.formatDate = function (dateString, format = "short") {
        if (!dateString) return "-";

        const date = new Date(dateString);

        if (isNaN(date.getTime())) return "-";

        switch (format) {
            case "short":
                return date.toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "short",
                });

            case "long":
                return date.toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                });

            case "time":
                return date.toLocaleTimeString("id-ID", {
                    hour: "2-digit",
                    minute: "2-digit",
                });

            default:
                return date.toLocaleDateString("id-ID");
        }
    };

    // ============================================
    // 10. CHECK IF OVERDUE
    // ============================================
    window.isTaskOverdue = function (dueDate, status = null) {
        if (!dueDate) return false;
        if (status === "done" || status === "selesai") return false;
        return new Date(dueDate) < new Date();
    };

    // ============================================
    // 11. GET STATUS COLOR
    // ============================================
    window.getStatusColor = function (status) {
        const colors = {
            todo: "#2563eb",
            todo_list: "#2563eb",
            in_progress: "#6B7280",
            dikerjakan: "#6B7280",
            done: "#40c79a",
            selesai: "#40c79a",
            overdue: "#facc15",
            terlambat: "#facc15",
        };
        return colors[status?.toLowerCase()] || colors["todo"];
    };

    // ============================================
    // 12. GET STATUS LABEL
    // ============================================
    window.getStatusLabel = function (status) {
        const labels = {
            todo: "Todo List",
            todo_list: "Todo List",
            in_progress: "Dikerjakan",
            dikerjakan: "Dikerjakan",
            done: "Selesai",
            selesai: "Selesai",
            overdue: "Terlambat",
            terlambat: "Terlambat",
        };
        return labels[status?.toLowerCase()] || "Todo List";
    };

    // ============================================
    // 13. TOAST NOTIFICATION
    // ============================================
    window.showToast = function (message, type = "info") {
        // Remove existing toast
        const existing = document.getElementById("toast-notification");
        if (existing) existing.remove();

        const toast = document.createElement("div");
        toast.id = "toast-notification";
        toast.className =
            "fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 transform transition-all duration-300";

        const bgColors = {
            success: "bg-green-500",
            error: "bg-red-500",
            info: "bg-blue-500",
            warning: "bg-yellow-500",
        };

        toast.classList.add(bgColors[type] || bgColors["info"]);
        toast.textContent = message;
        toast.style.opacity = "0";
        toast.style.transform = "translateY(-20px)";

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.opacity = "1";
            toast.style.transform = "translateY(0)";
        }, 10);

        // Auto remove after 3s
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-20px)";
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // ============================================
    // 14. DEBOUNCE (untuk search input)
    // ============================================
    window.debounce = function (func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // ============================================
    // 15. LOADING SKELETON HELPER
    // ============================================
    window.showSkeleton = function (selector) {
        const element = document.querySelector(selector);
        if (element) {
            element.classList.add("skeleton-loading");
        }
    };

    window.hideSkeleton = function (selector) {
        const element = document.querySelector(selector);
        if (element) {
            element.classList.remove("skeleton-loading");
        }
    };

    // ============================================
    // 16. CALCULATE PERCENTAGE
    // ============================================
    window.calculatePercentage = function (completed, total) {
        if (total === 0) return 0;
        return Math.round((completed / total) * 100);
    };

    // ============================================
    // 17. FORMAT NUMBER (untuk statistik)
    // ============================================
    window.formatNumber = function (number) {
        return new Intl.NumberFormat("id-ID").format(number);
    };

    // ============================================
    // 18. ANIMATE NUMBER COUNTER
    // ============================================
    window.animateCounter = function (element, endValue, duration = 1000) {
        if (!element) return;

        const startValue = parseInt(element.textContent) || 0;
        const range = endValue - startValue;
        const increment = range / (duration / 16);
        let current = startValue;

        const timer = setInterval(() => {
            current += increment;
            if (
                (increment > 0 && current >= endValue) ||
                (increment < 0 && current <= endValue)
            ) {
                current = endValue;
                clearInterval(timer);
            }
            element.textContent = Math.round(current);
        }, 16);
    };

    // ============================================
    // 19. AUTO-INIT ON PAGE LOAD
    // ============================================
    // Auto animate progress circles yang sudah ada di halaman
    setTimeout(() => {
        window.initProgressCircles();
    }, 100);

    console.log("🚀 All statistik functions ready!");
});
