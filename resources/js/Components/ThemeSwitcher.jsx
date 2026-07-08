import { useTheme } from '../hooks/useTheme';

const options = [
    { value: 'light', label: 'Licht', icon: SunIcon },
    { value: 'dark', label: 'Donker', icon: MoonIcon },
    { value: 'system', label: 'Systeem', icon: SystemIcon },
];

export default function ThemeSwitcher() {
    const { theme, setTheme } = useTheme();

    return (
        <div
            className="flex items-center rounded-full border border-border bg-surface p-1"
            role="group"
            aria-label="Thema"
        >
            {options.map(({ value, label, icon: Icon }) => (
                <button
                    key={value}
                    type="button"
                    onClick={() => setTheme(value)}
                    aria-label={label}
                    aria-pressed={theme === value}
                    title={label}
                    className={`rounded-full p-2 transition ${
                        theme === value
                            ? 'bg-foreground text-background'
                            : 'text-muted hover:text-foreground'
                    }`}
                >
                    <Icon className="h-4 w-4" />
                </button>
            ))}
        </div>
    );
}

function SunIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
            <circle cx="12" cy="12" r="4" />
            <path strokeLinecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
        </svg>
    );
}

function MoonIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"
            />
        </svg>
    );
}

function SystemIcon({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75">
            <rect x="3" y="4" width="18" height="12" rx="2" />
            <path strokeLinecap="round" d="M8 20h8M12 16v4" />
        </svg>
    );
}
