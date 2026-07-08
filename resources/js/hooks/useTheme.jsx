import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

const STORAGE_KEY = 'theme';

const ThemeContext = createContext(null);

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function getStoredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);

    if (stored === 'light' || stored === 'dark' || stored === 'system') {
        return stored;
    }

    return 'system';
}

function resolveTheme(theme) {
    return theme === 'system' ? getSystemTheme() : theme;
}

function applyTheme(resolvedTheme) {
    document.documentElement.classList.toggle('dark', resolvedTheme === 'dark');
}

export function ThemeProvider({ children }) {
    const [theme, setThemeState] = useState(getStoredTheme);
    const [systemTheme, setSystemTheme] = useState(getSystemTheme);

    const resolvedTheme = useMemo(
        () => (theme === 'system' ? systemTheme : theme),
        [theme, systemTheme],
    );

    useEffect(() => {
        applyTheme(resolvedTheme);
        localStorage.setItem(STORAGE_KEY, theme);
    }, [theme, resolvedTheme]);

    useEffect(() => {
        const media = window.matchMedia('(prefers-color-scheme: dark)');

        const handleChange = (event) => {
            setSystemTheme(event.matches ? 'dark' : 'light');
        };

        media.addEventListener('change', handleChange);

        return () => media.removeEventListener('change', handleChange);
    }, []);

    const setTheme = useCallback((nextTheme) => {
        setThemeState(nextTheme);
    }, []);

    const value = useMemo(
        () => ({ theme, resolvedTheme, setTheme }),
        [theme, resolvedTheme, setTheme],
    );

    return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
}

export function useTheme() {
    const context = useContext(ThemeContext);

    if (!context) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }

    return context;
}
