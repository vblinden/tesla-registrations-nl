import { useState } from 'react';
import { ColorStackedChart, MarketComparisonChart, ModelLineChart } from '../Components/RegistrationCharts';
import AppLayout from '../Layouts/AppLayout';
import { formatDateTime, formatNumber, formatPercent, TRACKED_MODELS } from '../lib/chartUtils';

function StatCard({ label, value, accent = false, suffix = '' }) {
    return (
        <div
            className={`rounded-2xl border p-5 ${
                accent
                    ? 'border-[#e82127]/30 bg-gradient-to-br from-[#e82127]/10 to-surface dark:from-[#e82127]/20'
                    : 'border-border bg-surface'
            }`}
        >
            <p className="text-sm text-muted">{label}</p>
            <p className={`mt-2 text-3xl font-bold tracking-tight ${accent ? 'text-accent-soft' : 'text-foreground'}`}>
                {typeof value === 'number' ? formatNumber(value) : value}
                {suffix}
            </p>
        </div>
    );
}

function ModelFilterButtons({ active, onChange, includeAll = false }) {
    const options = includeAll
        ? [{ id: 'all', label: "Alle Tesla's", activeClass: 'bg-foreground text-background' }, ...TRACKED_MODELS]
        : TRACKED_MODELS;

    return (
        <div className="flex flex-wrap gap-2">
            {options.map((model) => (
                <button
                    key={model.id}
                    type="button"
                    onClick={() => onChange(model.id)}
                    className={`rounded-full px-4 py-2 text-sm font-medium transition ${
                        active === model.id
                            ? model.activeClass
                            : 'bg-foreground/5 text-muted hover:bg-foreground/10 hover:text-foreground'
                    }`}
                >
                    {model.label}
                </button>
            ))}
        </div>
    );
}

export default function Dashboard({
    dailyAll,
    dailyByModelDetail,
    dailyByModel,
    dailyMarket,
    colors,
    models,
    summary,
    lastSyncedAt,
    rdwDataUpdatedAt,
    period,
}) {
    const [activeView, setActiveView] = useState('MODEL Y');
    const [activeTableModel, setActiveTableModel] = useState('MODEL Y');

    const chartViews = {
        all: {
            data: dailyAll,
            title: 'Alle Tesla registraties',
            subtitle: 'Alle Tesla-modellen gecombineerd, uitgesplitst naar kleur',
        },
        ...Object.fromEntries(
            TRACKED_MODELS.map((model) => [
                model.id,
                {
                    data: dailyByModelDetail[model.id] ?? [],
                    title: `${model.label} registraties`,
                    subtitle: 'Nieuwe kentekens per dag, uitgesplitst naar kleur',
                },
            ]),
        ),
    };

    const { data: chartData, title: chartTitle, subtitle: chartSubtitle } = chartViews[activeView];
    const tableData = dailyByModelDetail[activeTableModel] ?? [];
    const tableModel = TRACKED_MODELS.find((model) => model.id === activeTableModel);
    const tableColors = colors.filter((color) => tableData.some((day) => day[color] > 0));

    return (
        <AppLayout>

            <div className="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Tesla registraties Nederland
                    </h1>
                    <p className="mt-2 max-w-2xl text-muted">
                        Overzicht van nieuwe kentekenregistraties op basis van RDW open data. De RDW publiceert
                        nieuwe data ongeveer één keer per dag (rond 06:00 uur); dit dashboard controleert elke 15
                        minuten op updates.
                    </p>
                </div>
                <div className="rounded-xl border border-border bg-surface px-4 py-3 text-sm text-muted">
                    <p>Laatst gesynchroniseerd</p>
                    <p className="font-medium text-foreground">{formatDateTime(lastSyncedAt)}</p>
                    <p className="mt-2">RDW data bijgewerkt</p>
                    <p className="font-medium text-foreground">{formatDateTime(rdwDataUpdatedAt)}</p>
                    <p className="mt-1 text-xs text-muted/70">
                        Periode: {period.from} t/m {period.to}
                    </p>
                </div>
            </div>

            <div className="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <StatCard label="Tesla (14 dagen)" value={summary.total} accent />
                <StatCard label="NL personenauto's (14 dagen)" value={summary.totalNl} />
                <StatCard
                    label="Tesla marktaandeel (14 dagen)"
                    value={formatPercent(summary.marketShare)}
                    suffix="%"
                />
            </div>

            <div className="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Model Y (14 dagen)" value={summary.modelY} accent />
                <StatCard label="Model 3 (14 dagen)" value={summary.model3} />
                <StatCard label="Model S (14 dagen)" value={summary.modelS} />
                <StatCard label="Model X (14 dagen)" value={summary.modelX} />
            </div>

            <div className="mb-6">
                <ModelFilterButtons active={activeView} onChange={setActiveView} includeAll />
            </div>

            <div className="grid gap-6 xl:grid-cols-2">
                <ColorStackedChart
                    data={chartData}
                    colors={colors}
                    title={chartTitle}
                    subtitle={chartSubtitle}
                />
                <ModelLineChart dailyByModel={dailyByModel} models={models} />
            </div>

            <div className="mt-6">
                <MarketComparisonChart data={dailyMarket} />
            </div>

            <div className="mt-6 rounded-2xl border border-border bg-surface p-6">
                <div className="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h2 className="text-lg font-semibold text-foreground">Registraties per dag</h2>
                    <ModelFilterButtons active={activeTableModel} onChange={setActiveTableModel} />
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full text-left text-sm">
                        <thead>
                            <tr className="border-b border-border text-muted">
                                <th className="pb-3 pr-4 font-medium">Datum</th>
                                <th className="pb-3 pr-4 font-medium">Totaal</th>
                                {tableColors.map((color) => (
                                    <th key={color} className="pb-3 pr-4 font-medium">
                                        {color}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {[...tableData].reverse().map((day) => (
                                <tr key={day.date} className="border-b border-border/50 text-foreground/80">
                                    <td className="py-3 pr-4">{day.label}</td>
                                    <td className={`py-3 pr-4 font-semibold ${tableModel?.totalClass ?? 'text-foreground'}`}>
                                        {day.total}
                                    </td>
                                    {tableColors.map((color) => (
                                        <td key={color} className="py-3 pr-4">
                                            {day[color] || '—'}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
