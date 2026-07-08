import { useTheme } from '../hooks/useTheme';

export function getChartTheme(resolvedTheme) {
    const isDark = resolvedTheme === 'dark';

    return {
        grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
        tick: isDark ? 'rgba(255,255,255,0.5)' : 'rgba(0,0,0,0.45)',
        cursor: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)',
        tooltip: {
            border: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)',
            background: isDark ? '#1a1a1a' : '#ffffff',
            label: isDark ? 'rgba(255,255,255,0.8)' : 'rgba(0,0,0,0.7)',
            text: isDark ? '#ffffff' : '#18181b',
            muted: isDark ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.55)',
        },
    };
}

export function useChartTheme() {
    const { resolvedTheme } = useTheme();

    return getChartTheme(resolvedTheme);
}
