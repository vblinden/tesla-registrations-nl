import { Link } from '@inertiajs/react';
import TeslaLogo from '../Components/TeslaLogo';
import ThemeSwitcher from '../Components/ThemeSwitcher';

export default function AppLayout({ children }) {
    return (
        <div className="min-h-screen bg-background text-foreground">
            <header className="border-b border-border bg-surface-muted/80 backdrop-blur-md">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#e82127] text-white">
                            <TeslaLogo className="h-7 w-auto" />
                        </div>
                        <div>
                            <p className="text-lg font-semibold tracking-tight">Tesla NL Registraties</p>
                            <p className="text-sm text-muted">RDW open data dashboard</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <ThemeSwitcher />
                        <a
                            href="https://opendata.rdw.nl/Voertuigen/Open-Data-RDW-Gekentekende_voertuigen/m9d7-ebf2/about_data"
                            target="_blank"
                            rel="noreferrer"
                            className="hidden text-sm text-muted transition hover:text-foreground sm:inline"
                        >
                            Bron: RDW Open Data
                        </a>
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">{children}</main>

            <footer className="border-t border-border py-6 text-center text-sm text-muted">
                Data via{' '}
                <Link href="/" className="text-foreground/70 hover:text-foreground">
                    RDW Gekentekende voertuigen
                </Link>
                {' · '}RDW data dagelijks, controle elke 15 min
            </footer>
        </div>
    );
}
