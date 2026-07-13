import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { useChartTheme } from '../lib/chartTheme';
import { getColorHex, getVariantHex, MODEL_COLORS } from '../lib/chartUtils';

function ChartTooltip({ active, payload, label, chartTheme }) {
    if (!active || !payload?.length) {
        return null;
    }

    const total = payload.reduce((sum, entry) => sum + (entry.value || 0), 0);

    return (
        <div
            className="rounded-xl border px-4 py-3 shadow-2xl"
            style={{
                borderColor: chartTheme.tooltip.border,
                backgroundColor: chartTheme.tooltip.background,
            }}
        >
            <p className="mb-2 text-sm font-medium" style={{ color: chartTheme.tooltip.label }}>
                {label}
            </p>
            <p className="mb-2 text-lg font-semibold" style={{ color: chartTheme.tooltip.text }}>
                {total} registraties
            </p>
            <div className="space-y-1">
                {payload
                    .filter((entry) => entry.value > 0)
                    .sort((a, b) => b.value - a.value)
                    .map((entry) => (
                        <div key={entry.dataKey} className="flex items-center justify-between gap-4 text-sm">
                            <div className="flex items-center gap-2">
                                <span
                                    className="h-2.5 w-2.5 rounded-full"
                                    style={{ backgroundColor: entry.color }}
                                />
                                <span style={{ color: chartTheme.tooltip.muted }}>{entry.dataKey}</span>
                            </div>
                            <span className="font-medium" style={{ color: chartTheme.tooltip.text }}>
                                {entry.value}
                            </span>
                        </div>
                    ))}
            </div>
        </div>
    );
}

export function ColorStackedChart({ data, colors, title, subtitle, colorFor = getColorHex }) {
    const chartTheme = useChartTheme();
    const activeColors = colors.filter((color) => data.some((day) => day[color] > 0));

    return (
        <div className="rounded-2xl border border-border bg-surface p-6">
            <div className="mb-6">
                <h2 className="text-xl font-semibold text-foreground">{title}</h2>
                {subtitle && <p className="mt-1 text-sm text-muted">{subtitle}</p>}
            </div>
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={data} margin={{ top: 8, right: 8, left: -16, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke={chartTheme.grid} vertical={false} />
                        <XAxis
                            dataKey="label"
                            tick={{ fill: chartTheme.tick, fontSize: 12 }}
                            axisLine={false}
                            tickLine={false}
                        />
                        <YAxis
                            tick={{ fill: chartTheme.tick, fontSize: 12 }}
                            axisLine={false}
                            tickLine={false}
                            allowDecimals={false}
                        />
                        <Tooltip
                            content={<ChartTooltip chartTheme={chartTheme} />}
                            cursor={{ fill: chartTheme.cursor }}
                        />
                        <Legend
                            wrapperStyle={{ paddingTop: 16 }}
                            formatter={(value) => <span className="text-muted">{value}</span>}
                        />
                        {activeColors.map((color) => (
                            <Bar
                                key={color}
                                dataKey={color}
                                stackId="colors"
                                fill={colorFor(color)}
                                radius={color === activeColors[activeColors.length - 1] ? [4, 4, 0, 0] : [0, 0, 0, 0]}
                            />
                        ))}
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}

export function VariantStackedChart(props) {
    return <ColorStackedChart {...props} colorFor={getVariantHex} />;
}

export function ModelLineChart({ dailyByModel, models }) {
    const chartTheme = useChartTheme();
    const data = Object.values(dailyByModel[models[0]] ?? []).map((_, index) => {
        const row = { label: dailyByModel[models[0]]?.[index]?.label };

        models.forEach((model) => {
            row[model] = dailyByModel[model]?.[index]?.total ?? 0;
        });

        return row;
    });

    return (
        <div className="rounded-2xl border border-border bg-surface p-6">
            <div className="mb-6">
                <h2 className="text-xl font-semibold text-foreground">Registraties per model</h2>
                <p className="mt-1 text-sm text-muted">Dagelijkse trend per Tesla-model</p>
            </div>
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart data={data} margin={{ top: 8, right: 8, left: -16, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke={chartTheme.grid} vertical={false} />
                        <XAxis
                            dataKey="label"
                            tick={{ fill: chartTheme.tick, fontSize: 12 }}
                            axisLine={false}
                            tickLine={false}
                        />
                        <YAxis
                            tick={{ fill: chartTheme.tick, fontSize: 12 }}
                            axisLine={false}
                            tickLine={false}
                            allowDecimals={false}
                        />
                        <Tooltip content={<ChartTooltip chartTheme={chartTheme} />} />
                        <Legend formatter={(value) => <span className="text-muted">{value}</span>} />
                        {models.map((model) => (
                            <Line
                                key={model}
                                type="monotone"
                                dataKey={model}
                                stroke={MODEL_COLORS[model] ?? '#94a3b8'}
                                strokeWidth={model === 'MODEL Y' ? 3 : 2}
                                dot={false}
                                activeDot={{ r: 5 }}
                            />
                        ))}
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}

