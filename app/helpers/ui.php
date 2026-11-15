<?php
declare(strict_types=1);

function formatRp(float|int|string $amount, bool $compact = false): string
{
    $val = (float)$amount;
    if ($compact && $val >= 1000) {
        $k = $val / 1000;
        $formatted = fmod($k, 1.0) === 0.0 ? number_format($k, 0, ',', '.') : number_format($k, 1, ',', '.');
        return 'Rp ' . $formatted . 'rb';
    }
    return 'Rp ' . number_format($val, 0, ',', '.');
}

function formatQty(float|int|string $qty): string
{
    $val = (float)$qty;
    return fmod($val, 1.0) === 0.0 ? number_format($val, 0, ',', '.') : rtrim(rtrim(number_format($val, 2, ',', '.'), '0'), ',');
}

function userInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
    }
    return $initials !== '' ? $initials : 'WF';
}

function categoryIconSvg(?string $categoryName): string
{
    $name = mb_strtolower(trim((string)$categoryName));
    if ($name === 'kopi') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/></svg>';
    }
    if ($name === 'minuman rasa') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1.5 13h-9L6 8Z"/><path d="M5 8V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2"/><path d="M12 4 15 2"/></svg>';
    }
    if ($name === 'minuman') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6v3l2 3v11a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V9l2-3V3Z"/><path d="M7 13h10"/></svg>';
    }
    if ($name === 'makanan') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9 9 0 0 0 9-9H3a9 9 0 0 0 9 9Z"/><path d="M7 21h10"/><path d="M19.5 12 22 6"/><path d="M16.25 3c.27.1.8.53.75 1.36-.06.83-.93 1.2-1 2.02-.05.78.34 1.24.73 1.62"/><path d="M11.25 3c.27.1.8.53.74 1.36-.05.83-.93 1.2-.98 2.02-.06.78.33 1.24.72 1.62"/></svg>';
    }
    if (str_contains($name, 'ringan')) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h12l-1 16H7L6 4Z"/><path d="M8 9h8"/><path d="M8 14h8"/></svg>';
    }
    if (str_contains($name, 'kacang')) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="9" cy="10" rx="4" ry="6" transform="rotate(-20 9 10)"/><ellipse cx="15" cy="14" rx="4" ry="5" transform="rotate(18 15 14)"/></svg>';
    }
    if ($name === 'rokok') {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="12" height="16" rx="2"/><path d="M6 9h12"/><path d="M9 14h6"/></svg>';
    }
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>';
}

function productIllustrationSvg(string $name, ?string $categoryName = ''): string
{
    $n = mb_strtolower($name);
    $c = mb_strtolower((string)$categoryName);

    // 1. Indomie Telur
    if (str_contains($n, 'indomie') || $c === 'makanan') {
        return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="60" cy="83" rx="36" ry="6" fill="#E5E7EB"/>
            <path d="M25 48H95C92 68 79 80 60 80C41 80 28 68 25 48Z" fill="#2563eb"/>
            <ellipse cx="60" cy="48" rx="35" ry="9" fill="#FEF3C7" stroke="#EA580C" stroke-width="2"/>
            <path d="M36 46C42 36 52 37 57 46C62 36 72 37 77 46C80 41 84 43 85 48" stroke="#F59E0B" stroke-width="3.8" stroke-linecap="round"/>
            <ellipse cx="48" cy="44" rx="8.5" ry="6" fill="#FFFFFF" stroke="#F59E0B" stroke-width="1.2"/>
            <circle cx="48" cy="44" r="3.2" fill="#F97316"/>
            <circle cx="72" cy="45" r="3" fill="#22C55E"/>
            <path d="M74 18L90 36M80 15L94 32" stroke="#78350F" stroke-width="2.6" stroke-linecap="round"/>
        </svg>';
    }

    // 2. Rokok
    if ($c === 'rokok' || str_contains($n, 'mild') || str_contains($n, 'filter') || str_contains($n, 'samsu') || str_contains($n, 'sam soe') || str_contains($n, 'magnum') || str_contains($n, 'super') || str_contains($n, 'signature') || str_contains($n, 'apel') || str_contains($n, 'prima')) {
        $packColor = str_contains($n, 'mild') || str_contains($n, 'prima') ? '#FFFFFF' : (str_contains($n, 'dji') || str_contains($n, 'super') || str_contains($n, 'magnum') ? '#1F2937' : '#DC2626');
        $accentColor = str_contains($n, 'mild') ? '#DC2626' : (str_contains($n, 'apel') ? '#16A34A' : '#F59E0B');
        $strokeColor = $packColor === '#FFFFFF' ? '#CBD5E1' : '#111827';
        return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="60" cy="84" rx="30" ry="5.5" fill="#E5E7EB"/>
            <rect x="40" y="18" width="40" height="62" rx="6" fill="' . $packColor . '" stroke="' . $strokeColor . '" stroke-width="2"/>
            <path d="M40 34H80" stroke="' . $accentColor . '" stroke-width="3"/>
            <rect x="48" y="42" width="24" height="20" rx="4" fill="' . $accentColor . '" fill-opacity="0.9"/>
            <path d="M54 52H66" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round"/>
            <rect x="47" y="12" width="7" height="10" rx="2" fill="#FDE68A" stroke="#D97706" stroke-width="1"/>
            <rect x="57" y="10" width="7" height="12" rx="2" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1"/>
        </svg>';
    }

    // 3. Kopi (Hot coffee cup)
    if ($c === 'kopi' || str_contains($n, 'kopi') || str_contains($n, 'kapal') || str_contains($n, 'good day')) {
        $isIced = str_contains($n, 'freezz');
        if ($isIced) {
            return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <ellipse cx="60" cy="85" rx="28" ry="5" fill="#E5E7EB"/>
                <path d="M64 11L59 29" stroke="#2563eb" stroke-width="4" stroke-linecap="round"/>
                <path d="M40 30H80L74 82H46L40 30Z" fill="#78350F"/>
                <path d="M42 36H78L76 52H44L42 36Z" fill="#D97706"/>
                <ellipse cx="60" cy="30" rx="20" ry="5" fill="#FFFBEB" stroke="#D97706" stroke-width="1.5"/>
            </svg>';
        }
        return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="58" cy="84" rx="36" ry="6.5" fill="#E5E7EB"/>
            <ellipse cx="58" cy="80" rx="31" ry="6" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1.5"/>
            <path d="M79 44H86C91 44 95 48 95 53C95 58 91 62 86 62H79" stroke="#2563eb" stroke-width="5" stroke-linecap="round"/>
            <path d="M32 36H81V62C81 73 72 80 61 80H52C41 80 32 73 32 62V36Z" fill="#2563eb"/>
            <ellipse cx="56.5" cy="36" rx="24.5" ry="7" fill="#FFF7ED"/>
            <ellipse cx="56.5" cy="36" rx="20" ry="5" fill="#451A03"/>
            <path d="M46 21C46 17 50 15 49 11M57 19C57 15 61 13 60 9M68 21C68 17 72 15 71 11" stroke="#9CA3AF" stroke-width="2.2" stroke-linecap="round"/>
        </svg>';
    }

    // 4. Minuman Rasa (Nutrisari, Marimas, Chocolatos sachet, Susu Jahe, Drink Beng-Beng, AHM)
    if ($c === 'minuman rasa') {
        $cupFill = str_contains($n, 'macha') || str_contains($n, 'matcha')
            ? '#22C55E'
            : (str_contains($n, 'anggur') ? '#9333EA'
            : (str_contains($n, 'coklat') || str_contains($n, 'beng') ? '#6D341A'
            : (str_contains($n, 'coconut') || str_contains($n, 'jahe') ? '#FEF3C7' : '#F97316')));
        return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="60" cy="85" rx="28" ry="5" fill="#E5E7EB"/>
            <path d="M66 11L60 30" stroke="#2563eb" stroke-width="4" stroke-linecap="round"/>
            <path d="M40 30H80L74 82H46L40 30Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <path d="M42.5 40H77.5L74 79H46L42.5 40Z" fill="' . $cupFill . '" fill-opacity="0.88"/>
            <rect x="49" y="46" width="10" height="10" rx="2.5" transform="rotate(-10 49 46)" fill="#FFFFFF" fill-opacity="0.65"/>
            <rect x="61" y="50" width="9" height="9" rx="2.5" transform="rotate(14 61 50)" fill="#FFFFFF" fill-opacity="0.58"/>
        </svg>';
    }

    // 5. Minuman Botol / Kaleng / Gelas (Aqua, Teh Pucuk, Pocari, Sprite, Coca-Cola, Fanta, Floridina, Le Minerale)
    if ($c === 'minuman') {
        if (str_contains($n, 'aqua') || str_contains($n, 'minerale') || str_contains($n, 'pocari') || str_contains($n, 'mizone')) {
            $bodyColor = str_contains($n, 'aqua') || str_contains($n, 'minerale') ? '#BAE6FD' : '#38BDF8';
            $capColor = str_contains($n, 'pocari') || str_contains($n, 'mizone') ? '#0284C7' : '#0EA5E9';
            return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <ellipse cx="60" cy="85" rx="26" ry="5" fill="#E5E7EB"/>
                <rect x="52" y="10" width="16" height="8" rx="3" fill="' . $capColor . '"/>
                <path d="M51 18H69L76 31V78C76 80.5 74 82 71.5 82H48.5C46 82 44 80.5 44 78V31L51 18Z" fill="' . $bodyColor . '" stroke="#0284C7" stroke-width="1.8"/>
                <rect x="44" y="42" width="32" height="18" fill="#FFFFFF" fill-opacity="0.9"/>
                <path d="M50 51H70" stroke="' . $capColor . '" stroke-width="3" stroke-linecap="round"/>
            </svg>';
        }
        $bottleTint = str_contains($n, 'coca') ? '#991B1B'
            : (str_contains($n, 'sprite') || str_contains($n, 'nipis') ? '#16A34A'
            : (str_contains($n, 'fanta') || str_contains($n, 'floridina') ? '#EA580C'
            : (str_contains($n, 'pucuk') ? '#B45309' : '#2563eb')));
        return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="60" cy="85" rx="26" ry="5" fill="#E5E7EB"/>
            <rect x="52" y="10" width="16" height="8" rx="3" fill="#2563eb"/>
            <path d="M51 18H69L76 31V78C76 80.5 74 82 71.5 82H48.5C46 82 44 80.5 44 78V31L51 18Z" fill="' . $bottleTint . '"/>
            <rect x="44" y="41" width="32" height="18" fill="#FFF7ED"/>
            <circle cx="60" cy="50" r="5" fill="' . $bottleTint . '"/>
        </svg>';
    }

    // 6. Kacang-Kacangan
    if ($c === 'kacang-kacangan' || str_contains($n, 'kacang') || str_contains($n, 'tictac')) {
        return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <ellipse cx="60" cy="84" rx="32" ry="5.5" fill="#E5E7EB"/>
            <rect x="37" y="18" width="46" height="62" rx="10" fill="#FDE68A" stroke="#D97706" stroke-width="2"/>
            <rect x="43" y="32" width="34" height="26" rx="6" fill="#FFFFFF"/>
            <ellipse cx="55" cy="45" rx="6" ry="9" transform="rotate(-20 55 45)" fill="#D97706"/>
            <ellipse cx="66" cy="46" rx="5.5" ry="8" transform="rotate(20 66 46)" fill="#B45309"/>
        </svg>';
    }

    // 7. Makanan Ringan (Malkis, Better, Beng-Beng, Chocolatos, Roti Aoka, Kerupuk)
    return '<svg class="ws-prod-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <ellipse cx="60" cy="84" rx="32" ry="5.5" fill="#E5E7EB"/>
        <rect x="33" y="24" width="54" height="52" rx="10" fill="#2563eb"/>
        <rect x="39" y="34" width="42" height="28" rx="6" fill="#FFF7ED"/>
        <circle cx="52" cy="48" r="7" fill="#F59E0B"/>
        <circle cx="68" cy="48" r="7" fill="#78350F"/>
    </svg>';
}

function getHeaderNotifications(): array
{
    if (!isLoggedIn()) {
        return [];
    }
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $products = StockModel::products();
    $cached = array_values(array_filter(
        $products,
        static fn(array $p): bool => (int)$p['is_active'] === 1 && (float)$p['stock'] <= (float)$p['min_stock']
    ));
    return $cached;
}
