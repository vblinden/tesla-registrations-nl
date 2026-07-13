export const COLOR_MAP = {
    WIT: '#e8e8e8',
    ZWART: '#171717',
    GRIJS: '#9ca3af',
    BLAUW: '#3b82f6',
    ROOD: '#dc2626',
    GROEN: '#16a34a',
    GEEL: '#ca8a04',
    BRUIN: '#78350f',
    ORANJE: '#ea580c',
    PAARS: '#9333ea',
    BEIGE: '#d6c6a8',
    ZILVER: '#c0c0c0',
    'Niet geregistreerd': '#6b7280',
    ONBEKEND: '#6b7280',
};

export const MODEL_COLORS = {
    'MODEL Y': '#e82127',
    'MODEL 3': '#3b82f6',
    'MODEL S': '#171717',
    'MODEL X': '#7c3aed',
    CYBERTRUCK: '#9ca3af',
    ROADSTER: '#f59e0b',
};

export const TRACKED_MODELS = [
    {
        id: 'MODEL Y',
        label: 'Model Y',
        activeClass: 'bg-[#e82127] text-white',
        totalClass: 'text-accent-soft',
    },
    {
        id: 'MODEL 3',
        label: 'Model 3',
        activeClass: 'bg-[#3b82f6] text-white',
        totalClass: 'text-[#3b82f6]',
    },
    {
        id: 'MODEL S',
        label: 'Model S',
        activeClass: 'bg-zinc-800 text-white dark:bg-zinc-200 dark:text-zinc-900',
        totalClass: 'text-foreground',
    },
    {
        id: 'MODEL X',
        label: 'Model X',
        activeClass: 'bg-[#7c3aed] text-white',
        totalClass: 'text-[#7c3aed]',
    },
];

export function getColorHex(color) {
    return COLOR_MAP[color] ?? '#94a3b8';
}

export const VARIANT_COLORS = {
    'Long Range AWD': '#e82127',
    'Long Range RWD': '#f97316',
    'Standard RWD': '#3b82f6',
    'Standard Range RWD': '#60a5fa',
    AWD: '#7c3aed',
    RWD: '#14b8a6',
    Performance: '#171717',
    'Performance AWD': '#525252',
    Plaid: '#a855f7',
    Onbekend: '#94a3b8',
};

export function getVariantHex(variant) {
    return VARIANT_COLORS[variant] ?? '#94a3b8';
}

export function formatNumber(value) {
    return new Intl.NumberFormat('nl-NL').format(value);
}

export function formatPercent(value) {
    return new Intl.NumberFormat('nl-NL', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    }).format(value);
}

export function formatDateTime(isoString) {
    if (!isoString) {
        return 'Nog niet gesynchroniseerd';
    }

    return new Intl.DateTimeFormat('nl-NL', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(isoString));
}
